<?php
/**
 * Recipe Ingredients Meta Box
 *
 * Handles ingredient selection in recipe admin.
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
function pl_meta_ucfirst( $text ) {
	if ( empty( $text ) ) {
		return $text;
	}
	return mb_strtoupper( mb_substr( $text, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $text, 1, null, 'UTF-8' );
}

/**
 * PL_Recipe_Ingredients_Meta Class
 */
class PL_Recipe_Ingredients_Meta {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_ingredients_meta_box' ) );
		add_action( 'save_post_pl_recipe', array( $this, 'save_recipe_ingredients' ), 20, 2 );
		add_action( 'before_delete_post', array( $this, 'cleanup_recipe_ingredients' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add ingredients meta box
	 *
	 * @return void
	 */
	public function add_ingredients_meta_box() {
		add_meta_box(
			'pl_recipe_ingredients_selection',
			__( 'Recipe Ingredients (for search)', 'pl-recipe-cookbook' ),
			array( $this, 'render_ingredients_meta_box' ),
			'pl_recipe',
			'normal',
			'default'
		);
	}

	/**
	 * Render ingredients meta box
	 *
	 * @param WP_Post $post Current post object.
	 * @return void
	 */
	public function render_ingredients_meta_box( $post ) {
		wp_nonce_field( 'pl_recipe_ingredients_meta', 'pl_recipe_ingredients_nonce' );

		global $wpdb;

		// Get categories.
		$categories = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}pl_ingredient_categories ORDER BY display_order ASC, name ASC"
		);

		// Get selected ingredients for this recipe.
		$selected_ingredients = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ri.*, i.name, i.category_id 
				FROM {$wpdb->prefix}pl_recipe_ingredients ri
				LEFT JOIN {$wpdb->prefix}pl_ingredients i ON ri.ingredient_id = i.id
				WHERE ri.recipe_id = %d
				ORDER BY ri.display_order ASC",
				$post->ID
			)
		);

		?>
		<div id="pl-ingredients-selector">
			<p class="description">
				<?php esc_html_e( 'Select ingredients used in this recipe. This is used for the ingredient search functionality on the frontend.', 'pl-recipe-cookbook' ); ?>
			</p>

			<div class="pl-ingredient-controls" style="margin: 15px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
				<div style="display: flex; gap: 10px; margin-bottom: 10px;">
					<div style="flex: 1;">
						<label><strong><?php esc_html_e( 'Select Category:', 'pl-recipe-cookbook' ); ?></strong></label>
						<select id="pl-category-select" style="width: 100%;">
							<option value=""><?php esc_html_e( '-- Select Category --', 'pl-recipe-cookbook' ); ?></option>
							<?php foreach ( $categories as $category ) : ?>
								<option value="<?php echo esc_attr( $category->id ); ?>">
									<?php echo esc_html( pl_meta_ucfirst( $category->name ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div style="flex: 1;">
						<label><strong><?php esc_html_e( 'Select Ingredient:', 'pl-recipe-cookbook' ); ?></strong></label>
						<select id="pl-ingredient-select" style="width: 100%;" disabled>
							<option value=""><?php esc_html_e( '-- Select category first --', 'pl-recipe-cookbook' ); ?></option>
						</select>
					</div>
				</div>
				<button type="button" id="pl-add-ingredient" class="button" disabled>
					<?php esc_html_e( 'Add Ingredient', 'pl-recipe-cookbook' ); ?>
				</button>
			</div>

			<div id="pl-selected-ingredients">
				<h4><?php esc_html_e( 'Selected Ingredients:', 'pl-recipe-cookbook' ); ?></h4>
				<table class="wp-list-table widefat fixed striped" id="pl-ingredients-table">
					<thead>
						<tr>
							<th style="width: 40px;"><?php esc_html_e( 'Order', 'pl-recipe-cookbook' ); ?></th>
							<th><?php esc_html_e( 'Ingredient', 'pl-recipe-cookbook' ); ?></th>
							<th style="width: 100px;"><?php esc_html_e( 'Quantity', 'pl-recipe-cookbook' ); ?></th>
							<th style="width: 70px;"><?php esc_html_e( 'Unit', 'pl-recipe-cookbook' ); ?></th>
							<th style="width: 120px;"><?php esc_html_e( 'Section', 'pl-recipe-cookbook' ); ?></th>
							<th style="width: 200px;"><?php esc_html_e( 'Notes', 'pl-recipe-cookbook' ); ?></th>
							<th style="width: 80px;"><?php esc_html_e( 'Actions', 'pl-recipe-cookbook' ); ?></th>
						</tr>
					</thead>
					<tbody id="pl-ingredients-list">
						<?php if ( ! empty( $selected_ingredients ) ) : ?>
							<?php foreach ( $selected_ingredients as $index => $ingredient ) : ?>
								<tr data-ingredient-id="<?php echo esc_attr( $ingredient->ingredient_id ); ?>">
									<td>
										<span class="pl-order-handle" style="cursor: move;">☰</span>
										<input type="hidden" name="pl_ingredients[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $ingredient->ingredient_id ); ?>">
										<input type="hidden" name="pl_ingredients[<?php echo esc_attr( $index ); ?>][order]" value="<?php echo esc_attr( $ingredient->display_order ); ?>" class="pl-order-input">
									</td>
									<td>
										<strong><?php echo esc_html( pl_meta_ucfirst( $ingredient->name ) ); ?></strong>
									</td>
									<td>
										<input type="text" name="pl_ingredients[<?php echo esc_attr( $index ); ?>][quantity]" 
											   value="<?php echo esc_attr( $ingredient->quantity ); ?>" 
											   style="width: 100%;" placeholder="500">
									</td>
									<td>
										<input type="text" name="pl_ingredients[<?php echo esc_attr( $index ); ?>][unit]" 
											   value="<?php echo esc_attr( $ingredient->unit ); ?>" 
											   style="width: 100%;" placeholder="г">
									</td>
									<td>
										<input type="text" name="pl_ingredients[<?php echo esc_attr( $index ); ?>][section]" 
											   value="<?php echo esc_attr( $ingredient->section ); ?>" 
											   style="width: 100%;" placeholder="<?php esc_attr_e( 'Optional', 'pl-recipe-cookbook' ); ?>">
									</td>
									<td>
										<input type="text" name="pl_ingredients[<?php echo esc_attr( $index ); ?>][notes]" 
											   value="<?php echo esc_attr( $ingredient->notes ); ?>" 
											   style="width: 100%;" placeholder="<?php esc_attr_e( 'Optional', 'pl-recipe-cookbook' ); ?>">
									</td>
									<td>
										<button type="button" class="button pl-remove-ingredient"><?php esc_html_e( 'Remove', 'pl-recipe-cookbook' ); ?></button>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="pl-no-ingredients">
								<td colspan="7" style="text-align: center; color: #666;">
									<?php esc_html_e( 'No ingredients added yet.', 'pl-recipe-cookbook' ); ?>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<style>
			#pl-ingredients-table { margin-top: 10px; }
			#pl-ingredients-table th { text-align: left; padding: 8px; }
			#pl-ingredients-table td { padding: 8px; vertical-align: middle; }
			.pl-order-handle { display: inline-block; padding: 0 5px; color: #999; }
			.pl-order-handle:hover { color: #333; }
			#pl-ingredients-list tr { cursor: move; }
			#pl-ingredients-list tr:hover { background: #f5f5f5; }
		</style>
		<?php
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		global $post_type;
		if ( 'pl_recipe' !== $post_type ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_enqueue_script(
			'pl-recipe-ingredients-admin',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/admin-ingredients.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'pl-recipe-ingredients-admin',
			'plRecipeAdmin',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'pl_recipe_admin' ),
			)
		);
	}

	/**
	 * Save recipe ingredients
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public function save_recipe_ingredients( $post_id, $post ) {
		// Check nonce.
		if ( ! isset( $_POST['pl_recipe_ingredients_nonce'] ) || 
			 ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pl_recipe_ingredients_nonce'] ) ), 'pl_recipe_ingredients_meta' ) ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		global $wpdb;

		// Delete existing ingredients.
		$wpdb->delete(
			$wpdb->prefix . 'pl_recipe_ingredients',
			array( 'recipe_id' => $post_id ),
			array( '%d' )
		);

		// Add new ingredients.
		if ( isset( $_POST['pl_ingredients'] ) && is_array( $_POST['pl_ingredients'] ) ) {
			foreach ( $_POST['pl_ingredients'] as $index => $ingredient ) {
				$ingredient_id  = isset( $ingredient['id'] ) ? absint( $ingredient['id'] ) : 0;
				$quantity       = isset( $ingredient['quantity'] ) ? sanitize_text_field( wp_unslash( $ingredient['quantity'] ) ) : '';
				$unit           = isset( $ingredient['unit'] ) ? sanitize_text_field( wp_unslash( $ingredient['unit'] ) ) : '';
				$section        = isset( $ingredient['section'] ) ? sanitize_text_field( wp_unslash( $ingredient['section'] ) ) : '';
				$notes          = isset( $ingredient['notes'] ) ? sanitize_text_field( wp_unslash( $ingredient['notes'] ) ) : '';
				$display_order  = isset( $ingredient['order'] ) ? absint( $ingredient['order'] ) : $index;

				if ( $ingredient_id ) {
					// Get ingredient name.
					$ingredient_name = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT name FROM {$wpdb->prefix}pl_ingredients WHERE id = %d",
							$ingredient_id
						)
					);

					// Build raw_text with quantity, unit and ingredient name.
					$raw_text_parts = array();
					if ( ! empty( $quantity ) ) {
						$raw_text_parts[] = $quantity;
					}
					if ( ! empty( $unit ) ) {
						$raw_text_parts[] = $unit;
					}
					if ( ! empty( $ingredient_name ) ) {
						$raw_text_parts[] = $ingredient_name;
					}
					if ( ! empty( $notes ) ) {
						$raw_text_parts[] = '(' . $notes . ')';
					}
					$raw_text = implode( ' ', $raw_text_parts );
					
					$wpdb->insert(
						$wpdb->prefix . 'pl_recipe_ingredients',
						array(
							'recipe_id'      => $post_id,
							'ingredient_id'  => $ingredient_id,
							'quantity'       => $quantity,
							'unit'           => $unit,
							'raw_text'       => $raw_text,
							'section'        => $section,
							'notes'          => $notes,
							'display_order'  => $display_order,
						),
						array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d' )
					);
				}
			}
		}
	}

	/**
	 * Cleanup recipe ingredients on post delete
	 *
	 * @param int $post_id Post ID being deleted.
	 * @return void
	 */
	public function cleanup_recipe_ingredients( $post_id ) {
		if ( 'pl_recipe' !== get_post_type( $post_id ) ) {
			return;
		}

		global $wpdb;
		$wpdb->delete(
			$wpdb->prefix . 'pl_recipe_ingredients',
			array( 'recipe_id' => $post_id ),
			array( '%d' )
		);
	}
}

// Initialize.
new PL_Recipe_Ingredients_Meta();
