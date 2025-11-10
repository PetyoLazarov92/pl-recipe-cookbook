<?php
/**
 * Ingredients Admin Management
 *
 * Handles admin interface for managing categories and ingredients.
 *
 * @package PLRecipeCookbook
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capitalize first letter of text (UTF-8 safe)
 *
 * @param string $text Text to capitalize.
 * @return string Capitalized text.
 */
function pl_admin_ucfirst( $text ) {
	if ( empty( $text ) ) {
		return $text;
	}
	return mb_strtoupper( mb_substr( $text, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $text, 1, null, 'UTF-8' );
}

/**
 * PL_Recipe_Ingredients_Admin Class
 */
class PL_Recipe_Ingredients_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
		add_action( 'admin_post_pl_save_category', array( $this, 'save_category' ) );
		add_action( 'admin_post_pl_delete_category', array( $this, 'delete_category' ) );
		add_action( 'admin_post_pl_save_ingredient', array( $this, 'save_ingredient' ) );
		add_action( 'admin_post_pl_delete_ingredient', array( $this, 'delete_ingredient' ) );
		add_action( 'wp_ajax_pl_get_ingredients_by_category', array( $this, 'ajax_get_ingredients_by_category' ) );
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'pl_recipe_page_pl-ingredients' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'pl-admin-ingredients',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/admin-ingredients.css',
			array(),
			'1.0.0'
		);
	}

	/**
	 * Show admin notices
	 *
	 * @return void
	 */
	public function show_admin_notices() {
		if ( ! isset( $_GET['page'] ) || 'pl-ingredients' !== $_GET['page'] ) {
			return;
		}

		if ( isset( $_GET['message'] ) ) {
			$message = sanitize_text_field( wp_unslash( $_GET['message'] ) );
			$type    = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : 'success';

			$messages = array(
				'category_added'   => __( 'Category added successfully.', 'pl-recipe-cookbook' ),
				'category_updated' => __( 'Category updated successfully.', 'pl-recipe-cookbook' ),
				'category_deleted' => __( 'Category deleted successfully.', 'pl-recipe-cookbook' ),
				'ingredient_added'   => __( 'Ingredient added successfully.', 'pl-recipe-cookbook' ),
				'ingredient_updated' => __( 'Ingredient updated successfully.', 'pl-recipe-cookbook' ),
				'ingredient_deleted' => __( 'Ingredient deleted successfully.', 'pl-recipe-cookbook' ),
			);

			if ( isset( $messages[ $message ] ) ) {
				printf(
					'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
					esc_attr( $type ),
					esc_html( $messages[ $message ] )
				);
			}
		}
	}

	/**
	 * Add admin menu pages
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=pl_recipe',
			__( 'Manage Ingredients', 'pl-recipe-cookbook' ),
			__( 'Ingredients', 'pl-recipe-cookbook' ),
			'manage_options',
			'pl-ingredients',
			array( $this, 'render_ingredients_page' )
		);
	}

	/**
	 * Render ingredients management page
	 *
	 * @return void
	 */
	public function render_ingredients_page() {
		global $wpdb;

		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'categories';

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Manage Ingredients', 'pl-recipe-cookbook' ); ?></h1>

			<nav class="nav-tab-wrapper">
				<a href="?post_type=pl_recipe&page=pl-ingredients&tab=categories" 
				   class="nav-tab <?php echo 'categories' === $tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Categories', 'pl-recipe-cookbook' ); ?>
				</a>
				<a href="?post_type=pl_recipe&page=pl-ingredients&tab=ingredients" 
				   class="nav-tab <?php echo 'ingredients' === $tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Ingredients', 'pl-recipe-cookbook' ); ?>
				</a>
			</nav>

			<div class="tab-content">
				<?php
				if ( 'categories' === $tab ) {
					$this->render_categories_tab();
				} else {
					$this->render_ingredients_tab();
				}
				?>
			</div>
		</div>

		<style>
			.tab-content { margin-top: 20px; }
			.pl-admin-form { max-width: 600px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; }
			.pl-admin-form .form-field { margin-bottom: 15px; }
			.pl-admin-form label { display: block; margin-bottom: 5px; font-weight: 600; }
			.pl-admin-form input[type="text"],
			.pl-admin-form input[type="number"],
			.pl-admin-form select,
			.pl-admin-form textarea { width: 100%; max-width: 100%; }
			.pl-table { margin-top: 20px; }
			.pl-table th { text-align: left; }
		</style>
		<?php
	}

	/**
	 * Render categories tab
	 *
	 * @return void
	 */
	private function render_categories_tab() {
		global $wpdb;

		$edit_id   = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
		$edit_data = null;

		if ( $edit_id ) {
			$edit_data = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}pl_ingredient_categories WHERE id = %d",
					$edit_id
				)
			);
		}

		?>
		<div class="pl-admin-form">
			<h2><?php echo $edit_id ? esc_html__( 'Edit Category', 'pl-recipe-cookbook' ) : esc_html__( 'Add New Category', 'pl-recipe-cookbook' ); ?></h2>
			
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'pl_save_category', 'pl_category_nonce' ); ?>
				<input type="hidden" name="action" value="pl_save_category">
				<?php if ( $edit_id ) : ?>
					<input type="hidden" name="category_id" value="<?php echo esc_attr( $edit_id ); ?>">
				<?php endif; ?>

				<div class="form-field">
					<label><?php esc_html_e( 'Name (Bulgarian)', 'pl-recipe-cookbook' ); ?> *</label>
					<input type="text" name="name" required 
						   value="<?php echo $edit_data ? esc_attr( $edit_data->name ) : ''; ?>">
				</div>

				<div class="form-field">
					<label><?php esc_html_e( 'Name (English)', 'pl-recipe-cookbook' ); ?></label>
					<input type="text" name="name_en" 
						   value="<?php echo $edit_data ? esc_attr( $edit_data->name_en ) : ''; ?>">
				</div>

				<div class="form-field">
					<label><?php esc_html_e( 'Slug', 'pl-recipe-cookbook' ); ?> *</label>
					<input type="text" name="slug" required 
						   value="<?php echo $edit_data ? esc_attr( $edit_data->slug ) : ''; ?>">
				</div>

				<div class="form-field">
					<label><?php esc_html_e( 'Display Order', 'pl-recipe-cookbook' ); ?></label>
					<input type="number" name="display_order" value="<?php echo $edit_data ? esc_attr( $edit_data->display_order ) : '0'; ?>">
				</div>

				<div class="form-field">
					<label><?php esc_html_e( 'Description', 'pl-recipe-cookbook' ); ?></label>
					<textarea name="description" rows="3"><?php echo $edit_data ? esc_textarea( $edit_data->description ) : ''; ?></textarea>
				</div>

				<p>
					<button type="submit" class="button button-primary">
						<?php echo $edit_id ? esc_html__( 'Update Category', 'pl-recipe-cookbook' ) : esc_html__( 'Add Category', 'pl-recipe-cookbook' ); ?>
					</button>
					<?php if ( $edit_id ) : ?>
						<a href="?post_type=pl_recipe&page=pl-ingredients&tab=categories" class="button">
							<?php esc_html_e( 'Cancel', 'pl-recipe-cookbook' ); ?>
						</a>
					<?php endif; ?>
				</p>
			</form>
		</div>

		<?php
		// List categories.
		$categories = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}pl_ingredient_categories ORDER BY display_order ASC, name ASC"
		);
		?>

		<table class="wp-list-table widefat fixed striped pl-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Order', 'pl-recipe-cookbook' ); ?></th>
					<th><?php esc_html_e( 'Name', 'pl-recipe-cookbook' ); ?></th>
					<th><?php esc_html_e( 'English Name', 'pl-recipe-cookbook' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'pl-recipe-cookbook' ); ?></th>
					<th><?php esc_html_e( 'Ingredients', 'pl-recipe-cookbook' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'pl-recipe-cookbook' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $categories ) ) : ?>
					<?php foreach ( $categories as $category ) : ?>
						<?php
						$count = $wpdb->get_var(
							$wpdb->prepare(
								"SELECT COUNT(*) FROM {$wpdb->prefix}pl_ingredients WHERE category_id = %d",
								$category->id
							)
						);
						?>
						<tr>
							<td><?php echo esc_html( $category->display_order ); ?></td>
							<td><strong><?php echo esc_html( pl_admin_ucfirst( $category->name ) ); ?></strong></td>
							<td><?php echo esc_html( pl_admin_ucfirst( $category->name_en ) ); ?></td>
							<td><?php echo esc_html( $category->slug ); ?></td>
							<td><?php echo esc_html( $count ); ?></td>
							<td>
								<a href="?post_type=pl_recipe&page=pl-ingredients&tab=categories&edit=<?php echo esc_attr( $category->id ); ?>" class="button button-small">
									<?php esc_html_e( 'Edit', 'pl-recipe-cookbook' ); ?>
								</a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=pl_delete_category&id=' . $category->id ), 'pl_delete_category_' . $category->id ) ); ?>" 
								   class="button button-small" 
								   onclick="return confirm('<?php esc_attr_e( 'Are you sure? This will also delete all ingredients in this category!', 'pl-recipe-cookbook' ); ?>')">
									<?php esc_html_e( 'Delete', 'pl-recipe-cookbook' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="7"><?php esc_html_e( 'No categories found.', 'pl-recipe-cookbook' ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render ingredients tab
	 *
	 * @return void
	 */
	private function render_ingredients_tab() {
		global $wpdb;

		$edit_id   = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
		$edit_data = null;

		if ( $edit_id ) {
			$edit_data = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}pl_ingredients WHERE id = %d",
					$edit_id
				)
			);
		}

		$categories = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}pl_ingredient_categories ORDER BY display_order ASC, name ASC"
		);

		?>
		<div class="pl-admin-form">
			<h2><?php echo $edit_id ? esc_html__( 'Edit Ingredient', 'pl-recipe-cookbook' ) : esc_html__( 'Add New Ingredient', 'pl-recipe-cookbook' ); ?></h2>
			
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'pl_save_ingredient', 'pl_ingredient_nonce' ); ?>
				<input type="hidden" name="action" value="pl_save_ingredient">
				<?php if ( $edit_id ) : ?>
					<input type="hidden" name="ingredient_id" value="<?php echo esc_attr( $edit_id ); ?>">
				<?php endif; ?>

				<div class="form-field">
					<label><?php esc_html_e( 'Category', 'pl-recipe-cookbook' ); ?> *</label>
					<select name="category_id" required>
						<option value=""><?php esc_html_e( 'Select Category', 'pl-recipe-cookbook' ); ?></option>
						<?php foreach ( $categories as $category ) : ?>
							<option value="<?php echo esc_attr( $category->id ); ?>" 
									<?php selected( $edit_data ? $edit_data->category_id : 0, $category->id ); ?>>
								<?php echo esc_html( pl_admin_ucfirst( $category->name ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-field">
					<label><?php esc_html_e( 'Name (Bulgarian)', 'pl-recipe-cookbook' ); ?> *</label>
					<input type="text" name="name" required 
						   value="<?php echo $edit_data ? esc_attr( $edit_data->name ) : ''; ?>">
				</div>

				<div class="form-field">
					<label><?php esc_html_e( 'Name (English)', 'pl-recipe-cookbook' ); ?></label>
					<input type="text" name="name_en" 
						   value="<?php echo $edit_data ? esc_attr( $edit_data->name_en ) : ''; ?>">
				</div>

				<div class="form-field">
					<label><?php esc_html_e( 'Slug', 'pl-recipe-cookbook' ); ?> *</label>
					<input type="text" name="slug" required 
						   value="<?php echo $edit_data ? esc_attr( $edit_data->slug ) : ''; ?>">
				</div>

				<p>
					<button type="submit" class="button button-primary">
						<?php echo $edit_id ? esc_html__( 'Update Ingredient', 'pl-recipe-cookbook' ) : esc_html__( 'Add Ingredient', 'pl-recipe-cookbook' ); ?>
					</button>
					<?php if ( $edit_id ) : ?>
						<a href="?post_type=pl_recipe&page=pl-ingredients&tab=ingredients" class="button">
							<?php esc_html_e( 'Cancel', 'pl-recipe-cookbook' ); ?>
						</a>
					<?php endif; ?>
				</p>
			</form>
		</div>

		<?php
		// List ingredients with search and filter.
		$search      = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$category_id = isset( $_GET['category_filter'] ) ? absint( $_GET['category_filter'] ) : 0;
		$paged       = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$per_page    = 50;
		$offset      = ( $paged - 1 ) * $per_page;

		$where = array( '1=1' );
		if ( $search ) {
			$where[] = $wpdb->prepare( '(i.name LIKE %s OR i.name_en LIKE %s)', '%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%' );
		}
		if ( $category_id ) {
			$where[] = $wpdb->prepare( 'i.category_id = %d', $category_id );
		}

		$where_clause = implode( ' AND ', $where );

		// Get total count.
		$total_items = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->prefix}pl_ingredients i
			WHERE {$where_clause}"
		);

		$total_pages = ceil( $total_items / $per_page );

		$ingredients = $wpdb->get_results(
			"SELECT i.*, c.name as category_name 
			FROM {$wpdb->prefix}pl_ingredients i
			LEFT JOIN {$wpdb->prefix}pl_ingredient_categories c ON i.category_id = c.id
			WHERE {$where_clause}
			ORDER BY i.name ASC
			LIMIT {$per_page} OFFSET {$offset}"
		);
		?>

		<div style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ccd0d4;">
			<form method="get">
				<input type="hidden" name="post_type" value="pl_recipe">
				<input type="hidden" name="page" value="pl-ingredients">
				<input type="hidden" name="tab" value="ingredients">
				
				<label><?php esc_html_e( 'Search:', 'pl-recipe-cookbook' ); ?></label>
				<input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search ingredients...', 'pl-recipe-cookbook' ); ?>">
				
				<label><?php esc_html_e( 'Category:', 'pl-recipe-cookbook' ); ?></label>
				<select name="category_filter">
					<option value=""><?php esc_html_e( 'All Categories', 'pl-recipe-cookbook' ); ?></option>
					<?php foreach ( $categories as $category ) : ?>
						<option value="<?php echo esc_attr( $category->id ); ?>" <?php selected( $category_id, $category->id ); ?>>
							<?php echo esc_html( $category->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				
				<button type="submit" class="button"><?php esc_html_e( 'Filter', 'pl-recipe-cookbook' ); ?></button>
				<a href="?post_type=pl_recipe&page=pl-ingredients&tab=ingredients" class="button"><?php esc_html_e( 'Reset', 'pl-recipe-cookbook' ); ?></a>
			</form>
		</div>

		<table class="wp-list-table widefat fixed striped pl-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'pl-recipe-cookbook' ); ?></th>
					<th><?php esc_html_e( 'English Name', 'pl-recipe-cookbook' ); ?></th>
					<th><?php esc_html_e( 'Category', 'pl-recipe-cookbook' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'pl-recipe-cookbook' ); ?></th>
					<th><?php esc_html_e( 'Used in Recipes', 'pl-recipe-cookbook' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'pl-recipe-cookbook' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $ingredients ) ) : ?>
					<?php foreach ( $ingredients as $ingredient ) : ?>
						<?php
						$recipe_count = $wpdb->get_var(
							$wpdb->prepare(
								"SELECT COUNT(DISTINCT recipe_id) FROM {$wpdb->prefix}pl_recipe_ingredients WHERE ingredient_id = %d",
								$ingredient->id
							)
						);
						?>
						<tr>
							<td><strong><?php echo esc_html( pl_admin_ucfirst( $ingredient->name ) ); ?></strong></td>
							<td><?php echo esc_html( pl_admin_ucfirst( $ingredient->name_en ) ); ?></td>
							<td><?php echo esc_html( pl_admin_ucfirst( $ingredient->category_name ) ); ?></td>
							<td><?php echo esc_html( $ingredient->slug ); ?></td>
							<td><?php echo esc_html( $recipe_count ); ?></td>
							<td>
								<a href="?post_type=pl_recipe&page=pl-ingredients&tab=ingredients&edit=<?php echo esc_attr( $ingredient->id ); ?>" class="button button-small">
									<?php esc_html_e( 'Edit', 'pl-recipe-cookbook' ); ?>
								</a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=pl_delete_ingredient&id=' . $ingredient->id ), 'pl_delete_ingredient_' . $ingredient->id ) ); ?>" 
								   class="button button-small" 
								   onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'pl-recipe-cookbook' ); ?>')">
									<?php esc_html_e( 'Delete', 'pl-recipe-cookbook' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="6"><?php esc_html_e( 'No ingredients found.', 'pl-recipe-cookbook' ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<span class="displaying-num">
						<?php
						printf(
							/* translators: %s: Number of items. */
							_n( '%s item', '%s items', $total_items, 'pl-recipe-cookbook' ),
							number_format_i18n( $total_items )
						);
						?>
					</span>
					<?php
					$base_url = add_query_arg(
						array(
							'post_type'       => 'pl_recipe',
							'page'            => 'pl-ingredients',
							'tab'             => 'ingredients',
							's'               => $search,
							'category_filter' => $category_id,
						),
						admin_url( 'edit.php' )
					);

					if ( $paged > 1 ) :
						?>
						<a class="first-page button" href="<?php echo esc_url( add_query_arg( 'paged', 1, $base_url ) ); ?>">
							<span aria-hidden="true">&laquo;</span>
						</a>
						<a class="prev-page button" href="<?php echo esc_url( add_query_arg( 'paged', max( 1, $paged - 1 ), $base_url ) ); ?>">
							<span aria-hidden="true">&lsaquo;</span>
						</a>
					<?php else : ?>
						<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
						<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
					<?php endif; ?>

					<span class="paging-input">
						<span class="tablenav-paging-text">
							<?php echo esc_html( $paged ); ?> <?php esc_html_e( 'of', 'pl-recipe-cookbook' ); ?> 
							<span class="total-pages"><?php echo esc_html( $total_pages ); ?></span>
						</span>
					</span>

					<?php if ( $paged < $total_pages ) : ?>
						<a class="next-page button" href="<?php echo esc_url( add_query_arg( 'paged', min( $total_pages, $paged + 1 ), $base_url ) ); ?>">
							<span aria-hidden="true">&rsaquo;</span>
						</a>
						<a class="last-page button" href="<?php echo esc_url( add_query_arg( 'paged', $total_pages, $base_url ) ); ?>">
							<span aria-hidden="true">&raquo;</span>
						</a>
					<?php else : ?>
						<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
						<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Save category
	 *
	 * @return void
	 */
	public function save_category() {
		check_admin_referer( 'pl_save_category', 'pl_category_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'pl-recipe-cookbook' ) );
		}

		global $wpdb;

		$category_id   = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
		$name          = isset( $_POST['name'] ) ? mb_strtolower( sanitize_text_field( wp_unslash( $_POST['name'] ) ), 'UTF-8' ) : '';
		$name_en       = isset( $_POST['name_en'] ) ? mb_strtolower( sanitize_text_field( wp_unslash( $_POST['name_en'] ) ), 'UTF-8' ) : '';
		$slug          = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
		$display_order = isset( $_POST['display_order'] ) ? absint( $_POST['display_order'] ) : 0;
		$description   = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

		$data = array(
			'name'          => $name,
			'name_en'       => $name_en,
			'slug'          => $slug,
			'display_order' => $display_order,
			'description'   => $description,
		);

		if ( $category_id ) {
			$wpdb->update(
				$wpdb->prefix . 'pl_ingredient_categories',
				$data,
				array( 'id' => $category_id ),
				array( '%s', '%s', '%s', '%d', '%s' ),
				array( '%d' )
			);
			$message = 'category_updated';
		} else {
			$wpdb->insert(
				$wpdb->prefix . 'pl_ingredient_categories',
				$data,
				array( '%s', '%s', '%s', '%d', '%s' )
			);
			$message = 'category_added';
		}

		wp_safe_redirect( admin_url( 'edit.php?post_type=pl_recipe&page=pl-ingredients&tab=categories&message=' . $message . '&type=success' ) );
		exit;
	}

	/**
	 * Delete category
	 *
	 * @return void
	 */
	public function delete_category() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

		check_admin_referer( 'pl_delete_category_' . $id );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'pl-recipe-cookbook' ) );
		}

		global $wpdb;
		$wpdb->delete(
			$wpdb->prefix . 'pl_ingredient_categories',
			array( 'id' => $id ),
			array( '%d' )
		);

		wp_safe_redirect( admin_url( 'edit.php?post_type=pl_recipe&page=pl-ingredients&tab=categories&message=category_deleted&type=success' ) );
		exit;
	}

	/**
	 * Save ingredient
	 *
	 * @return void
	 */
	public function save_ingredient() {
		check_admin_referer( 'pl_save_ingredient', 'pl_ingredient_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'pl-recipe-cookbook' ) );
		}

		global $wpdb;

		$ingredient_id = isset( $_POST['ingredient_id'] ) ? absint( $_POST['ingredient_id'] ) : 0;
		$category_id   = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
		$name          = isset( $_POST['name'] ) ? mb_strtolower( sanitize_text_field( wp_unslash( $_POST['name'] ) ), 'UTF-8' ) : '';
		$name_en       = isset( $_POST['name_en'] ) ? mb_strtolower( sanitize_text_field( wp_unslash( $_POST['name_en'] ) ), 'UTF-8' ) : '';
		$slug          = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';

		$data = array(
			'category_id' => $category_id,
			'name'        => $name,
			'name_en'     => $name_en,
			'slug'        => $slug,
		);

		if ( $ingredient_id ) {
			$wpdb->update(
				$wpdb->prefix . 'pl_ingredients',
				$data,
				array( 'id' => $ingredient_id ),
				array( '%d', '%s', '%s', '%s' ),
				array( '%d' )
			);
			$message = 'ingredient_updated';
		} else {
			$wpdb->insert(
				$wpdb->prefix . 'pl_ingredients',
				$data,
				array( '%d', '%s', '%s', '%s' )
			);
			$message = 'ingredient_added';
		}

		wp_safe_redirect( admin_url( 'edit.php?post_type=pl_recipe&page=pl-ingredients&tab=ingredients&message=' . $message . '&type=success' ) );
		exit;
	}

	/**
	 * Delete ingredient
	 *
	 * @return void
	 */
	public function delete_ingredient() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

		check_admin_referer( 'pl_delete_ingredient_' . $id );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'pl-recipe-cookbook' ) );
		}

		global $wpdb;
		$wpdb->delete(
			$wpdb->prefix . 'pl_ingredients',
			array( 'id' => $id ),
			array( '%d' )
		);

		wp_safe_redirect( admin_url( 'edit.php?post_type=pl_recipe&page=pl-ingredients&tab=ingredients&message=ingredient_deleted&type=success' ) );
		exit;
	}

	/**
	 * AJAX: Get ingredients by category
	 *
	 * @return void
	 */
	public function ajax_get_ingredients_by_category() {
		check_ajax_referer( 'pl_recipe_admin', 'nonce' );

		$category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;

		if ( ! $category_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid category', 'pl-recipe-cookbook' ) ) );
		}

		global $wpdb;
		$ingredients = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, name FROM {$wpdb->prefix}pl_ingredients WHERE category_id = %d ORDER BY name ASC",
				$category_id
			)
		);

		// Capitalize names for display.
		foreach ( $ingredients as $ingredient ) {
			$ingredient->name = pl_admin_ucfirst( $ingredient->name );
		}

		wp_send_json_success( array( 'ingredients' => $ingredients ) );
	}
}

// Initialize.
new PL_Recipe_Ingredients_Admin();
