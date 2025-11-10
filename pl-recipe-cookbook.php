<?php
/**
 * Plugin Name: PL Recipe Cookbook
 * Plugin URI: https://plazarov.com
 * Description: A custom post type for managing cooking recipes with beautiful templates and styling.
 * Version: 1.0.0
 * Author: Plazarov
 * Author URI: https://plazarov.com
 * Text Domain: pl-recipe-cookbook
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package PLRecipeCookbook
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Recipe Manager Class
 */
class PL_Recipe_Manager {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Instance of this class
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Get instance of this class
	 *
	 * @return PL_Recipe_Manager
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		// Load required files.
		$this->load_dependencies();

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_recipe_post_type' ) );
		add_action( 'init', array( $this, 'register_recipe_taxonomies' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_recipe_meta_boxes' ) );
		add_action( 'save_post_pl_recipe', array( $this, 'save_recipe_meta' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_filter( 'template_include', array( $this, 'recipe_template_loader' ), 99 );
		add_filter( 'sanitize_title', array( $this, 'transliterate_recipe_slug' ), 9, 3 );
		add_filter( 'wp_unique_post_slug', array( $this, 'transliterate_unique_slug' ), 10, 6 );
		add_action( 'wp_insert_post_data', array( $this, 'force_transliterate_slug' ), 10, 2 );
		add_action( 'wp_ajax_pl_load_more_terms', array( $this, 'ajax_load_more_terms' ) );
		add_action( 'wp_ajax_nopriv_pl_load_more_terms', array( $this, 'ajax_load_more_terms' ) );
	}

	/**
	 * Load plugin dependencies
	 *
	 * @return void
	 */
	private function load_dependencies() {
		// Database management.
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-database.php';

		// Ingredient search functionality.
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-ingredient-search.php';

		// Recipe display helpers (shortcodes and functions).
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-recipe-display-helpers.php';

		// Admin classes.
		if ( is_admin() ) {
			require_once plugin_dir_path( __FILE__ ) . 'admin/class-ingredients-admin.php';
			require_once plugin_dir_path( __FILE__ ) . 'admin/class-recipe-ingredients-meta.php';
		}
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'pl-recipe-cookbook',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	}

	/**
	 * Register Recipe Custom Post Type
	 */
	public function register_recipe_post_type() {
		$labels = array(
			'name'                  => _x( 'Recipes', 'Post type general name', 'pl-recipe-cookbook' ),
			'singular_name'         => _x( 'Recipe', 'Post type singular name', 'pl-recipe-cookbook' ),
			'menu_name'             => _x( 'Recipes', 'Admin Menu text', 'pl-recipe-cookbook' ),
			'name_admin_bar'        => _x( 'Recipe', 'Add New on Toolbar', 'pl-recipe-cookbook' ),
			'add_new'               => __( 'Add New', 'pl-recipe-cookbook' ),
			'add_new_item'          => __( 'Add New Recipe', 'pl-recipe-cookbook' ),
			'new_item'              => __( 'New Recipe', 'pl-recipe-cookbook' ),
			'edit_item'             => __( 'Edit Recipe', 'pl-recipe-cookbook' ),
			'view_item'             => __( 'View Recipe', 'pl-recipe-cookbook' ),
			'all_items'             => __( 'All Recipes', 'pl-recipe-cookbook' ),
			'search_items'          => __( 'Search Recipes', 'pl-recipe-cookbook' ),
			'parent_item_colon'     => __( 'Parent Recipes:', 'pl-recipe-cookbook' ),
			'not_found'             => __( 'No recipes found.', 'pl-recipe-cookbook' ),
			'not_found_in_trash'    => __( 'No recipes found in Trash.', 'pl-recipe-cookbook' ),
			'featured_image'        => _x( 'Recipe Image', 'Overrides the "Featured Image" phrase', 'pl-recipe-cookbook' ),
			'set_featured_image'    => _x( 'Set recipe image', 'Overrides the "Set featured image" phrase', 'pl-recipe-cookbook' ),
			'remove_featured_image' => _x( 'Remove recipe image', 'Overrides the "Remove featured image" phrase', 'pl-recipe-cookbook' ),
			'use_featured_image'    => _x( 'Use as recipe image', 'Overrides the "Use as featured image" phrase', 'pl-recipe-cookbook' ),
			'archives'              => _x( 'Recipe archives', 'The post type archive label used in nav menus', 'pl-recipe-cookbook' ),
			'insert_into_item'      => _x( 'Insert into recipe', 'Overrides the "Insert into post" phrase', 'pl-recipe-cookbook' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this recipe', 'Overrides the "Uploaded to this post" phrase', 'pl-recipe-cookbook' ),
			'filter_items_list'     => _x( 'Filter recipes list', 'Screen reader text for the filter links', 'pl-recipe-cookbook' ),
			'items_list_navigation' => _x( 'Recipes list navigation', 'Screen reader text for the pagination', 'pl-recipe-cookbook' ),
			'items_list'            => _x( 'Recipes list', 'Screen reader text for the items list', 'pl-recipe-cookbook' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'pl-recipe' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-food',
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
			'show_in_rest'       => true,
		);

		register_post_type( 'pl_recipe', $args );
	}

	/**
	 * Register Recipe Taxonomies
	 */
	public function register_recipe_taxonomies() {
		// Recipe Categories.
		$category_labels = array(
			'name'              => _x( 'Recipe Categories', 'taxonomy general name', 'pl-recipe-cookbook' ),
			'singular_name'     => _x( 'Recipe Category', 'taxonomy singular name', 'pl-recipe-cookbook' ),
			'search_items'      => __( 'Search Recipe Categories', 'pl-recipe-cookbook' ),
			'all_items'         => __( 'All Recipe Categories', 'pl-recipe-cookbook' ),
			'parent_item'       => __( 'Parent Recipe Category', 'pl-recipe-cookbook' ),
			'parent_item_colon' => __( 'Parent Recipe Category:', 'pl-recipe-cookbook' ),
			'edit_item'         => __( 'Edit Recipe Category', 'pl-recipe-cookbook' ),
			'update_item'       => __( 'Update Recipe Category', 'pl-recipe-cookbook' ),
			'add_new_item'      => __( 'Add New Recipe Category', 'pl-recipe-cookbook' ),
			'new_item_name'     => __( 'New Recipe Category Name', 'pl-recipe-cookbook' ),
			'menu_name'         => __( 'Categories', 'pl-recipe-cookbook' ),
		);

		register_taxonomy(
			'pl_recipe_cat',
			array( 'pl_recipe' ),
			array(
				'hierarchical'      => true,
				'labels'            => $category_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'pl-recipe-category' ),
				'show_in_rest'      => true,
			)
		);

		// Recipe Tags.
		$tag_labels = array(
			'name'                       => _x( 'Recipe Tags', 'taxonomy general name', 'pl-recipe-cookbook' ),
			'singular_name'              => _x( 'Recipe Tag', 'taxonomy singular name', 'pl-recipe-cookbook' ),
			'search_items'               => __( 'Search Recipe Tags', 'pl-recipe-cookbook' ),
			'popular_items'              => __( 'Popular Recipe Tags', 'pl-recipe-cookbook' ),
			'all_items'                  => __( 'All Recipe Tags', 'pl-recipe-cookbook' ),
			'edit_item'                  => __( 'Edit Recipe Tag', 'pl-recipe-cookbook' ),
			'update_item'                => __( 'Update Recipe Tag', 'pl-recipe-cookbook' ),
			'add_new_item'               => __( 'Add New Recipe Tag', 'pl-recipe-cookbook' ),
			'new_item_name'              => __( 'New Recipe Tag Name', 'pl-recipe-cookbook' ),
			'separate_items_with_commas' => __( 'Separate recipe tags with commas', 'pl-recipe-cookbook' ),
			'add_or_remove_items'        => __( 'Add or remove recipe tags', 'pl-recipe-cookbook' ),
			'choose_from_most_used'      => __( 'Choose from the most used recipe tags', 'pl-recipe-cookbook' ),
			'menu_name'                  => __( 'Tags', 'pl-recipe-cookbook' ),
		);

		register_taxonomy(
			'pl_recipe_tag',
			array( 'pl_recipe' ),
			array(
				'hierarchical'      => false,
				'labels'            => $tag_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'pl-recipe-tag' ),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Add Recipe Meta Boxes
	 */
	public function add_recipe_meta_boxes() {
		add_meta_box(
			'pl_recipe_details',
			__( 'Recipe Details', 'pl-recipe-cookbook' ),
			array( $this, 'render_recipe_details_meta_box' ),
			'pl_recipe',
			'normal',
			'high'
		);
	}

	/**
	 * Render Recipe Details Meta Box
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_recipe_details_meta_box( $post ) {
		wp_nonce_field( 'pl_recipe_details_nonce', 'pl_recipe_details_nonce' );

		$prep_time    = get_post_meta( $post->ID, '_pl_recipe_prep_time', true );
		$cook_time    = get_post_meta( $post->ID, '_pl_recipe_cook_time', true );
		$servings     = get_post_meta( $post->ID, '_pl_recipe_servings', true );
		$difficulty   = get_post_meta( $post->ID, '_pl_recipe_difficulty', true );
		$ingredients  = get_post_meta( $post->ID, '_pl_recipe_ingredients', true );
		$instructions = get_post_meta( $post->ID, '_pl_recipe_instructions', true );
		?>
		<div class="recipe-meta-box">
			<p>
				<label for="pl_recipe_prep_time"><strong><?php esc_html_e( 'Preparation Time (minutes):', 'pl-recipe-cookbook' ); ?></strong></label><br>
				<input type="number" id="pl_recipe_prep_time" name="pl_recipe_prep_time" value="<?php echo esc_attr( $prep_time ); ?>" class="widefat" min="0">
			</p>
			<p>
				<label for="pl_recipe_cook_time"><strong><?php esc_html_e( 'Cooking Time (minutes):', 'pl-recipe-cookbook' ); ?></strong></label><br>
				<input type="number" id="pl_recipe_cook_time" name="pl_recipe_cook_time" value="<?php echo esc_attr( $cook_time ); ?>" class="widefat" min="0">
			</p>
			<p>
				<label for="pl_recipe_servings"><strong><?php esc_html_e( 'Servings:', 'pl-recipe-cookbook' ); ?></strong></label><br>
				<input type="number" id="pl_recipe_servings" name="pl_recipe_servings" value="<?php echo esc_attr( $servings ); ?>" class="widefat" min="1">
			</p>
			<p>
				<label for="pl_recipe_difficulty"><strong><?php esc_html_e( 'Difficulty:', 'pl-recipe-cookbook' ); ?></strong></label><br>
				<select id="pl_recipe_difficulty" name="pl_recipe_difficulty" class="widefat">
					<option value=""><?php esc_html_e( 'Select Difficulty', 'pl-recipe-cookbook' ); ?></option>
					<option value="easy" <?php selected( $difficulty, 'easy' ); ?>><?php esc_html_e( 'Easy', 'pl-recipe-cookbook' ); ?></option>
					<option value="medium" <?php selected( $difficulty, 'medium' ); ?>><?php esc_html_e( 'Medium', 'pl-recipe-cookbook' ); ?></option>
					<option value="hard" <?php selected( $difficulty, 'hard' ); ?>><?php esc_html_e( 'Hard', 'pl-recipe-cookbook' ); ?></option>
				</select>
			</p>
			<p>
				<label for="pl_recipe_ingredients"><strong><?php esc_html_e( 'Ingredients:', 'pl-recipe-cookbook' ); ?></strong></label><br>
				<small><?php esc_html_e( 'Use [Section Name] to create sections. Example:', 'pl-recipe-cookbook' ); ?><br>
				<code>[<?php esc_html_e( 'For the dough', 'pl-recipe-cookbook' ); ?>]<br>500g flour<br>2 eggs<br><br>[<?php esc_html_e( 'For the filling', 'pl-recipe-cookbook' ); ?>]<br>300g cheese<br>Salt</code></small><br>
				<textarea id="pl_recipe_ingredients" name="pl_recipe_ingredients" rows="10" class="widefat"><?php echo esc_textarea( $ingredients ); ?></textarea>
			</p>
			<p>
				<label for="pl_recipe_instructions"><strong><?php esc_html_e( 'Instructions (one step per line):', 'pl-recipe-cookbook' ); ?></strong></label><br>
				<textarea id="pl_recipe_instructions" name="pl_recipe_instructions" rows="10" class="widefat"><?php echo esc_textarea( $instructions ); ?></textarea>
			</p>
		</div>
		<?php
	}

	/**
	 * Save Recipe Meta Data
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_recipe_meta( $post_id ) {
		if ( ! isset( $_POST['pl_recipe_details_nonce'] ) || ! wp_verify_nonce( $_POST['pl_recipe_details_nonce'], 'pl_recipe_details_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'pl_recipe_prep_time'    => 'intval',
			'pl_recipe_cook_time'    => 'intval',
			'pl_recipe_servings'     => 'intval',
			'pl_recipe_difficulty'   => 'sanitize_text_field',
			'pl_recipe_ingredients'  => 'sanitize_textarea_field',
			'pl_recipe_instructions' => 'sanitize_textarea_field',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, $_POST[ $field ] );
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}
	}

	/**
	 * Enqueue Frontend Styles
	 */
	public function enqueue_frontend_styles() {
		if ( is_singular( 'pl_recipe' ) || is_post_type_archive( 'pl_recipe' ) || is_tax( array( 'pl_recipe_cat', 'pl_recipe_tag' ) ) ) {
			wp_enqueue_style(
				'pl-recipe-cookbook-frontend',
				plugin_dir_url( __FILE__ ) . 'assets/css/frontend.css',
				array(),
				self::VERSION
			);

			wp_enqueue_script(
				'pl-recipe-cookbook-frontend',
				plugin_dir_url( __FILE__ ) . 'assets/js/frontend.js',
				array(),
				self::VERSION,
				true
			);

			// Load more filters script for archive pages
			if ( is_post_type_archive( 'pl_recipe' ) || is_tax( array( 'pl_recipe_cat', 'pl_recipe_tag' ) ) ) {
				wp_enqueue_script(
					'pl-recipe-load-more-filters',
					plugin_dir_url( __FILE__ ) . 'assets/js/load-more-filters.js',
					array( 'jquery' ),
					self::VERSION,
					true
				);

				wp_localize_script(
					'pl-recipe-load-more-filters',
					'plRecipeAjax',
					array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'nonce'   => wp_create_nonce( 'pl_load_more_terms' ),
					)
				);
			}
		}
	}

	/**
	 * Enqueue Admin Styles
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_styles( $hook ) {
		if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
			global $post_type;
			if ( 'pl_recipe' === $post_type ) {
				wp_enqueue_style(
					'pl-recipe-cookbook-admin',
					plugin_dir_url( __FILE__ ) . 'assets/css/admin.css',
					array(),
					self::VERSION
				);
				
				// Enqueue slug transliteration JavaScript
				wp_enqueue_script(
					'pl-recipe-cookbook-admin-slug',
					plugin_dir_url( __FILE__ ) . 'assets/js/admin-slug-transliterate.js',
					array( 'jquery' ),
					self::VERSION,
					true
				);
			}
		}
	}

	/**
	 * Load Custom Template for Recipe Post Type
	 *
	 * @param string $template Current template path.
	 * @return string Modified template path.
	 */
	public function recipe_template_loader( $template ) {
		// Check for page templates.
		if ( is_page() ) {
			global $post;
			$page_template = get_post_meta( $post->ID, '_wp_page_template', true );
			
			// Recipe search template.
			if ( 'page-recipe-search.php' === $page_template ) {
				$plugin_template = plugin_dir_path( __FILE__ ) . 'templates/page-recipe-search.php';
				if ( file_exists( $plugin_template ) ) {
					return $plugin_template;
				}
			}
		}

		// Check for slug-based template (page with slug 'nameri-recepta').
		if ( is_page( 'nameri-recepta' ) ) {
			$plugin_template = plugin_dir_path( __FILE__ ) . 'templates/page-recipe-search.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		if ( is_singular( 'pl_recipe' ) ) {
			$plugin_template = plugin_dir_path( __FILE__ ) . 'templates/single-pl-recipe.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		if ( is_post_type_archive( 'pl_recipe' ) ) {
			$plugin_template = plugin_dir_path( __FILE__ ) . 'templates/archive-pl-recipe.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		if ( is_tax( 'pl_recipe_cat' ) ) {
			$plugin_template = plugin_dir_path( __FILE__ ) . 'templates/taxonomy-pl_recipe_cat.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		if ( is_tax( 'pl_recipe_tag' ) ) {
			$plugin_template = plugin_dir_path( __FILE__ ) . 'templates/taxonomy-pl_recipe_tag.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	/**
	 * Transliterate recipe slug to Latin characters
	 *
	 * @param string $title     Sanitized title.
	 * @param string $raw_title The title prior to sanitization.
	 * @param string $context   The context for which the title is being sanitized.
	 * @return string Modified title.
	 */
	public function transliterate_recipe_slug( $title, $raw_title = '', $context = '' ) {
		// Only process when context is 'save' (creating/updating posts).
		if ( 'save' !== $context ) {
			return $title;
		}

		// Check if raw title contains non-Latin characters.
		if ( empty( $raw_title ) || ! preg_match( '/[^\x00-\x7F]/', $raw_title ) ) {
			return $title;
		}

		// Check if we're dealing with a recipe post type.
		$is_recipe = false;
		
		// Try to get post type from global $post.
		global $post;
		if ( isset( $post->post_type ) && 'pl_recipe' === $post->post_type ) {
			$is_recipe = true;
		}
		
		// Also check $_POST for new posts.
		if ( ! $is_recipe && isset( $_POST['post_type'] ) && 'pl_recipe' === $_POST['post_type'] ) {
			$is_recipe = true;
		}

		// If this is a recipe, transliterate it.
		if ( $is_recipe ) {
			return $this->transliterate_string( $raw_title );
		}

		return $title;
	}

	/**
	 * Force transliterate slug before saving post
	 *
	 * @param array $data    An array of slashed post data.
	 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
	 * @return array Modified post data.
	 */
	public function force_transliterate_slug( $data, $postarr ) {
		// Only process recipe post type.
		if ( 'pl_recipe' !== $data['post_type'] ) {
			return $data;
		}

		// Check if post_name (slug) contains non-Latin characters.
		if ( ! empty( $data['post_name'] ) && preg_match( '/[^\x00-\x7F]/', $data['post_name'] ) ) {
			// Transliterate the slug.
			$data['post_name'] = $this->transliterate_string( $data['post_name'] );
		}

		// Also check post_title if post_name is empty (auto-draft or new post).
		if ( empty( $data['post_name'] ) && ! empty( $data['post_title'] ) && preg_match( '/[^\x00-\x7F]/', $data['post_title'] ) ) {
			$data['post_name'] = $this->transliterate_string( $data['post_title'] );
		}

		return $data;
	}

	/**
	 * Transliterate unique slug for recipes
	 *
	 * @param string $slug          The desired slug.
	 * @param int    $post_ID       Post ID.
	 * @param string $post_status   Post status.
	 * @param string $post_type     Post type.
	 * @param int    $post_parent   Post parent ID.
	 * @param string $original_slug The original slug.
	 * @return string Modified slug.
	 */
	public function transliterate_unique_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
		// Only process recipe post type.
		if ( 'pl_recipe' !== $post_type ) {
			return $slug;
		}

		// Check if original slug contains non-Latin characters.
		if ( ! empty( $original_slug ) && preg_match( '/[^\x00-\x7F]/', $original_slug ) ) {
			$transliterated = $this->transliterate_string( $original_slug );
			
			// Make sure it's unique.
			global $wpdb;
			$check_sql = "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND post_type = %s AND ID != %d LIMIT 1";
			$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $transliterated, $post_type, $post_ID ) );
			
			if ( $post_name_check ) {
				$suffix = 2;
				do {
					$alt_post_name = _truncate_post_slug( $transliterated, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
					$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_type, $post_ID ) );
					$suffix++;
				} while ( $post_name_check );
				$transliterated = $alt_post_name;
			}
			
			return $transliterated;
		}

		return $slug;
	}

	/**
	 * Transliterate Cyrillic and other characters to Latin
	 *
	 * @param string $string String to transliterate.
	 * @return string Transliterated string.
	 */
	public function transliterate_string( $string ) {
		// Bulgarian Cyrillic to Latin transliteration map.
		$transliteration_table = array(
			// Uppercase Bulgarian Cyrillic.
			'А' => 'A',
			'Б' => 'B',
			'В' => 'V',
			'Г' => 'G',
			'Д' => 'D',
			'Е' => 'E',
			'Ж' => 'Zh',
			'З' => 'Z',
			'И' => 'I',
			'Й' => 'Y',
			'К' => 'K',
			'Л' => 'L',
			'М' => 'M',
			'Н' => 'N',
			'О' => 'O',
			'П' => 'P',
			'Р' => 'R',
			'С' => 'S',
			'Т' => 'T',
			'У' => 'U',
			'Ф' => 'F',
			'Х' => 'H',
			'Ц' => 'Ts',
			'Ч' => 'Ch',
			'Ш' => 'Sh',
			'Щ' => 'Sht',
			'Ъ' => 'A',
			'Ь' => 'Y',
			'Ю' => 'Yu',
			'Я' => 'Ya',
			// Lowercase Bulgarian Cyrillic.
			'а' => 'a',
			'б' => 'b',
			'в' => 'v',
			'г' => 'g',
			'д' => 'd',
			'е' => 'e',
			'ж' => 'zh',
			'з' => 'z',
			'и' => 'i',
			'й' => 'y',
			'к' => 'k',
			'л' => 'l',
			'м' => 'm',
			'н' => 'n',
			'о' => 'o',
			'п' => 'p',
			'р' => 'r',
			'с' => 's',
			'т' => 't',
			'у' => 'u',
			'ф' => 'f',
			'х' => 'h',
			'ц' => 'ts',
			'ч' => 'ch',
			'ш' => 'sh',
			'щ' => 'sht',
			'ъ' => 'a',
			'ь' => 'y',
			'ю' => 'yu',
			'я' => 'ya',
		);

		// Apply transliteration.
		$string = strtr( $string, $transliteration_table );

		// Convert to lowercase.
		$string = strtolower( $string );

		// Replace non-alphanumeric characters with hyphens.
		$string = preg_replace( '/[^a-z0-9]+/', '-', $string );

		// Remove leading/trailing hyphens.
		$string = trim( $string, '-' );

		// Remove consecutive hyphens.
		$string = preg_replace( '/-+/', '-', $string );

		return $string;
	}

	/**
	 * AJAX handler for loading more terms (categories/tags)
	 */
	public function ajax_load_more_terms() {
		check_ajax_referer( 'pl_load_more_terms', 'nonce' );

		$taxonomy        = isset( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';
		$offset          = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
		$current_term_id = isset( $_POST['current_term_id'] ) ? absint( $_POST['current_term_id'] ) : 0;

		if ( empty( $taxonomy ) || ! in_array( $taxonomy, array( 'pl_recipe_cat', 'pl_recipe_tag' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid taxonomy', 'pl-recipe-cookbook' ) ) );
		}

		$per_page = ( 'pl_recipe_cat' === $taxonomy ) ? 10 : 10;

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'number'     => $per_page,
				'offset'     => $offset,
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			wp_send_json_error( array( 'message' => __( 'No more items', 'pl-recipe-cookbook' ) ) );
		}

		$html = '';
		foreach ( $terms as $term ) {
			$term_link = get_term_link( $term );
			if ( is_wp_error( $term_link ) ) {
				continue;
			}
			$is_active = ( $current_term_id && $term->term_id === $current_term_id );
			$html .= sprintf(
				'<a href="%s" class="filter-pill%s" data-term-id="%d">%s<span class="filter-count">%d</span></a>',
				esc_url( $term_link ),
				$is_active ? ' active' : '',
				absint( $term->term_id ),
				esc_html( $term->name ),
				absint( $term->count )
			);
		}

		// Check if there are more terms.
		$has_more_terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'number'     => 1,
				'offset'     => $offset + $per_page,
				'fields'     => 'ids',
			)
		);

		$has_more = ! is_wp_error( $has_more_terms ) && ! empty( $has_more_terms );

		wp_send_json_success(
			array(
				'html'            => $html,
				'has_more'        => $has_more,
				'new_offset'      => $offset + $per_page,
				'current_term_id' => $current_term_id,
			)
		);
	}
}

// Initialize the plugin.
PL_Recipe_Manager::get_instance();

/**
 * Plugin activation hook
 */
function pl_recipe_manager_activate() {
	// Load database class.
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-database.php';

	// Initialize main plugin class.
	PL_Recipe_Manager::get_instance();

	// Create custom tables.
	PL_Recipe_Database::create_tables();

	// Import seed data.
	PL_Recipe_Database::import_seed_data();

	// Flush rewrite rules.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'pl_recipe_manager_activate' );

/**
 * Plugin deactivation hook
 */
function pl_recipe_manager_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'pl_recipe_manager_deactivate' );
