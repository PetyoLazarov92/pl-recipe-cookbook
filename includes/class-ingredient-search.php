<?php
/**
 * Recipe Search by Ingredients Class
 *
 * Handles frontend search functionality.
 *
 * @package PLRecipeCookbook
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PL_Recipe_Ingredient_Search Class
 */
class PL_Recipe_Ingredient_Search {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_pl_search_recipes', array( $this, 'ajax_search_recipes' ) );
		add_action( 'wp_ajax_nopriv_pl_search_recipes', array( $this, 'ajax_search_recipes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_search_scripts' ) );
	}

	/**
	 * Enqueue search scripts
	 *
	 * @return void
	 */
	public function enqueue_search_scripts() {
		// Only load on recipe search page.
		if ( ! is_page_template( 'page-recipe-search.php' ) && ! is_page( 'nameri-recepta' ) ) {
			return;
		}

		wp_enqueue_style(
			'pl-recipe-search',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/recipe-search.css',
			array(),
			'1.0.0'
		);

		wp_enqueue_script(
			'pl-recipe-search',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/recipe-search.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'pl-recipe-search',
			'plRecipeSearch',
			array(
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'pl_recipe_search' ),
				'i18n'         => array(
					'noSelection'     => __( 'No ingredients selected yet', 'pl-recipe-cookbook' ),
					'searching'       => __( 'Searching for recipes...', 'pl-recipe-cookbook' ),
					'errorLoading'    => __( 'Error loading recipes', 'pl-recipe-cookbook' ),
					'selectToSearch'  => __( 'Select ingredients from the left to find recipes', 'pl-recipe-cookbook' ),
					'noRecipesFound'  => __( 'No recipes found with the selected ingredients', 'pl-recipe-cookbook' ),
					'loading'         => __( 'Loading...', 'pl-recipe-cookbook' ),
					'found'           => __( 'found', 'pl-recipe-cookbook' ),
				),
			)
		);
	}

	/**
	 * AJAX search recipes by ingredients
	 *
	 * @return void
	 */
	public function ajax_search_recipes() {
		check_ajax_referer( 'pl_recipe_search', 'nonce' );

		// Ensure translations are loaded in AJAX context.
		// This is critical for multisite where locale may not be properly set during AJAX.
		$locale = determine_locale();
		unload_textdomain( 'pl-recipe-cookbook' );
		
		// Try loading from WP_LANG_DIR first (system translations).
		$loaded = load_textdomain( 'pl-recipe-cookbook', WP_LANG_DIR . '/plugins/pl-recipe-cookbook-' . $locale . '.mo' );
		
		// If not loaded, try plugin's own languages directory.
		if ( ! $loaded ) {
			load_plugin_textdomain( 'pl-recipe-cookbook', false, dirname( plugin_basename( dirname( __FILE__ ) ) ) . '/languages' );
		}

		$ingredient_ids = isset( $_POST['ingredient_ids'] ) ? array_map( 'absint', $_POST['ingredient_ids'] ) : array();
		$logic          = isset( $_POST['logic'] ) && 'OR' === $_POST['logic'] ? 'OR' : 'AND';
		$cursor         = isset( $_POST['cursor'] ) ? absint( $_POST['cursor'] ) : null;
		$limit          = 12;

		if ( empty( $ingredient_ids ) ) {
			wp_send_json_success(
				array(
					'html'        => '<p class="no-results">' . esc_html__( 'Please select at least one ingredient.', 'pl-recipe-cookbook' ) . '</p>',
					'next_cursor' => null,
					'has_more'    => false,
					'total_found' => 0,
				)
			);
		}

		$results = $this->search_recipes( $ingredient_ids, $logic, $cursor, $limit );

		// Generate HTML.
		ob_start();
		if ( ! empty( $results['recipes'] ) ) {
			foreach ( $results['recipes'] as $recipe ) {
				$this->render_recipe_card( $recipe );
			}
		}
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'        => $html,
				'next_cursor' => $results['next_cursor'],
				'has_more'    => $results['has_more'],
				'total_found' => $results['total_found'],
			)
		);
	}

	/**
	 * Search recipes by ingredients
	 *
	 * @param array  $ingredient_ids Array of ingredient IDs.
	 * @param string $logic          'AND' or 'OR'.
	 * @param int    $cursor         Last recipe ID from previous page.
	 * @param int    $limit          Results per page.
	 * @return array
	 */
	private function search_recipes( $ingredient_ids, $logic = 'AND', $cursor = null, $limit = 12 ) {
		global $wpdb;

		$ingredient_ids   = array_map( 'absint', $ingredient_ids );
		$ingredient_count = count( $ingredient_ids );
		$placeholders     = implode( ',', $ingredient_ids );

		$cursor_where = '';
		if ( $cursor ) {
			$cursor_where = $wpdb->prepare( 'AND recipe_id < %d', $cursor );
		}

		if ( 'AND' === $logic ) {
			// Recipes with ALL selected ingredients.
			$sql = "
				SELECT recipe_id, COUNT(*) as match_count
				FROM {$wpdb->prefix}pl_recipe_ingredients
				WHERE ingredient_id IN ($placeholders)
				$cursor_where
				GROUP BY recipe_id
				HAVING match_count = $ingredient_count
				ORDER BY recipe_id DESC
				LIMIT " . ( $limit + 1 );
		} else {
			// Recipes with AT LEAST ONE selected ingredient.
			$sql = "
				SELECT recipe_id, COUNT(*) as match_count
				FROM {$wpdb->prefix}pl_recipe_ingredients
				WHERE ingredient_id IN ($placeholders)
				$cursor_where
				GROUP BY recipe_id
				ORDER BY match_count DESC, recipe_id DESC
				LIMIT " . ( $limit + 1 );
		}

		$results = $wpdb->get_results( $sql );

		// Check if there are more results.
		$has_more = ( count( $results ) > $limit );
		if ( $has_more ) {
			array_pop( $results );
		}

		$next_cursor = null;
		if ( $has_more && ! empty( $results ) ) {
			$last_result = end( $results );
			$next_cursor = $last_result->recipe_id;
		}

		// Get full recipe data.
		$recipe_ids = wp_list_pluck( $results, 'recipe_id' );
		$recipes    = array();

		if ( ! empty( $recipe_ids ) ) {
			$recipes_query = new WP_Query(
				array(
					'post_type'      => 'pl_recipe',
					'post__in'       => $recipe_ids,
					'orderby'        => 'post__in',
					'posts_per_page' => $limit,
					'post_status'    => 'publish',
				)
			);

			$recipes = $recipes_query->posts;
		}

		return array(
			'recipes'     => $recipes,
			'next_cursor' => $next_cursor,
			'has_more'    => $has_more,
			'total_found' => count( $results ),
		);
	}

	/**
	 * Render recipe card
	 *
	 * @param WP_Post $recipe Recipe post object.
	 * @return void
	 */
	private function render_recipe_card( $recipe ) {
		$prep_time  = get_post_meta( $recipe->ID, '_pl_recipe_prep_time', true );
		$cook_time  = get_post_meta( $recipe->ID, '_pl_recipe_cook_time', true );
		$servings   = get_post_meta( $recipe->ID, '_pl_recipe_servings', true );
		$difficulty = get_post_meta( $recipe->ID, '_pl_recipe_difficulty', true );
		$thumbnail  = get_the_post_thumbnail_url( $recipe->ID, 'medium_large' );

		if ( ! $thumbnail ) {
			$thumbnail = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/recipe-placeholder.svg';
		}

		$difficulty_labels = array(
			'easy'   => __( 'Easy', 'pl-recipe-cookbook' ),
			'medium' => __( 'Medium', 'pl-recipe-cookbook' ),
			'hard'   => __( 'Hard', 'pl-recipe-cookbook' ),
		);
		$difficulty_label = isset( $difficulty_labels[ $difficulty ] ) ? $difficulty_labels[ $difficulty ] : ucfirst( $difficulty );

		?>
		<article class="recipe-card">
			<a href="<?php echo esc_url( get_permalink( $recipe->ID ) ); ?>" class="recipe-card-link">
				<div class="recipe-card-image">
					<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( get_the_title( $recipe->ID ) ); ?>">
					<?php if ( $difficulty ) : ?>
						<span class="recipe-badge recipe-badge-<?php echo esc_attr( $difficulty ); ?>">
							<?php echo esc_html( $difficulty_label ); ?>
						</span>
					<?php endif; ?>
				</div>

				<div class="recipe-card-content">
					<h2 class="recipe-card-title"><?php echo esc_html( get_the_title( $recipe->ID ) ); ?></h2>

					<?php if ( $recipe->post_excerpt ) : ?>
						<div class="recipe-card-excerpt">
							<?php echo wp_trim_words( $recipe->post_excerpt, 20, '...' ); ?>
						</div>
					<?php endif; ?>

					<div class="recipe-card-meta">
						<?php if ( $prep_time || $cook_time ) : ?>
							<span class="meta-time">
								⏰ <?php echo esc_html( ( $prep_time + $cook_time ) ); ?> <?php echo esc_html( __( 'min', 'pl-recipe-cookbook' ) ); ?>
							</span>
						<?php endif; ?>

						<?php if ( $servings ) : ?>
							<span class="meta-servings">
								🍽️ <?php echo esc_html( $servings ); ?> <?php echo esc_html( __( 'servings', 'pl-recipe-cookbook' ) ); ?>
							</span>
						<?php endif; ?>
					</div>
				</div>
			</a>
		</article>
		<?php
	}

	/**
	 * Get all categories with ingredients
	 *
	 * @return array
	 */
	public static function get_categories_with_ingredients() {
		global $wpdb;

		$categories = $wpdb->get_results(
			"SELECT c.*, COUNT(i.id) as ingredient_count
			FROM {$wpdb->prefix}pl_ingredient_categories c
			LEFT JOIN {$wpdb->prefix}pl_ingredients i ON c.id = i.category_id
			GROUP BY c.id
			HAVING ingredient_count > 0
			ORDER BY c.display_order ASC, c.name ASC"
		);

		foreach ( $categories as $category ) {
			$category->ingredients = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, name FROM {$wpdb->prefix}pl_ingredients 
					WHERE category_id = %d 
					ORDER BY name ASC",
					$category->id
				)
			);
		}

		return $categories;
	}
}

// Initialize.
new PL_Recipe_Ingredient_Search();
