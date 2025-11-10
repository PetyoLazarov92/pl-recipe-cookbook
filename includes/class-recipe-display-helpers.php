<?php
/**
 * Recipe Display Helper Functions
 *
 * Provides helper functions and shortcodes for displaying recipe data.
 *
 * @package PLRecipeCookbook
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PL_Recipe_Display_Helpers Class
 */
class PL_Recipe_Display_Helpers {

	/**
	 * Initialize the class
	 *
	 * @return void
	 */
	public static function init() {
		add_shortcode( 'recipe_ingredients', array( __CLASS__, 'recipe_ingredients_shortcode' ) );
		add_shortcode( 'recipe_section', array( __CLASS__, 'recipe_section_shortcode' ) );
	}

	/**
	 * Get formatted ingredients for a recipe
	 *
	 * @param int    $recipe_id Recipe post ID.
	 * @param string $format    Output format: 'html', 'list', 'grouped'.
	 * @return string Formatted ingredients.
	 */
	public static function get_recipe_ingredients_formatted( $recipe_id, $format = 'html' ) {
		// Try to get from custom table.
		$db_ingredients = PL_Recipe_Database::get_recipe_ingredients( $recipe_id );

		// Fallback to meta field.
		if ( empty( $db_ingredients ) ) {
			$ingredients_meta = get_post_meta( $recipe_id, '_pl_recipe_ingredients', true );
			if ( empty( $ingredients_meta ) ) {
				return '';
			}
			return self::format_meta_ingredients( $ingredients_meta, $format );
		}

		return self::format_db_ingredients( $db_ingredients, $format );
	}

	/**
	 * Format ingredients from custom table
	 *
	 * @param array  $db_ingredients Array of ingredients from database.
	 * @param string $format         Output format.
	 * @return string Formatted output.
	 */
	private static function format_db_ingredients( $db_ingredients, $format = 'html' ) {
		if ( empty( $db_ingredients ) ) {
			return '';
		}

		$output = '';
		$grouped = array();

		// Group by section.
		foreach ( $db_ingredients as $ingredient ) {
			$section = ! empty( $ingredient['section'] ) ? $ingredient['section'] : '';
			if ( ! isset( $grouped[ $section ] ) ) {
				$grouped[ $section ] = array();
			}
			$grouped[ $section ][] = $ingredient;
		}

		switch ( $format ) {
			case 'html':
				$output .= '<div class="recipe-ingredients-display">';
				foreach ( $grouped as $section => $items ) {
					if ( $section ) {
						$output .= '<div class="ingredient-section">';
						$output .= '<h4 class="section-title">' . esc_html( $section ) . '</h4>';
					} else {
						$output .= '<div class="ingredient-section">';
					}
					$output .= '<ul class="ingredients-list">';
					foreach ( $items as $item ) {
						$text = self::build_ingredient_text( $item );
						$output .= '<li>' . esc_html( $text ) . '</li>';
					}
					$output .= '</ul></div>';
				}
				$output .= '</div>';
				break;

			case 'list':
				$list = array();
				foreach ( $db_ingredients as $item ) {
					$list[] = self::build_ingredient_text( $item );
				}
				$output = implode( "\n", $list );
				break;

			case 'grouped':
				foreach ( $grouped as $section => $items ) {
					if ( $section ) {
						$output .= '[' . $section . "]\n";
					}
					foreach ( $items as $item ) {
						$output .= self::build_ingredient_text( $item ) . "\n";
					}
					$output .= "\n";
				}
				break;
		}

		return $output;
	}

	/**
	 * Build ingredient text from parts
	 *
	 * @param array $ingredient Ingredient data array.
	 * @return string Formatted ingredient text.
	 */
	private static function build_ingredient_text( $ingredient ) {
		if ( ! empty( $ingredient['raw_text'] ) ) {
			return $ingredient['raw_text'];
		}

		$parts = array();
		if ( ! empty( $ingredient['quantity'] ) ) {
			$parts[] = $ingredient['quantity'];
		}
		if ( ! empty( $ingredient['unit'] ) ) {
			$parts[] = $ingredient['unit'];
		}
		if ( ! empty( $ingredient['ingredient_name'] ) ) {
			$parts[] = $ingredient['ingredient_name'];
		}
		if ( ! empty( $ingredient['notes'] ) ) {
			$parts[] = '(' . $ingredient['notes'] . ')';
		}

		return implode( ' ', $parts );
	}

	/**
	 * Format ingredients from meta field
	 *
	 * @param string $ingredients_meta Meta field content.
	 * @param string $format           Output format.
	 * @return string Formatted output.
	 */
	private static function format_meta_ingredients( $ingredients_meta, $format = 'html' ) {
		if ( empty( $ingredients_meta ) ) {
			return '';
		}

		$lines = explode( "\n", $ingredients_meta );

		switch ( $format ) {
			case 'html':
				$output = '<div class="recipe-ingredients-display"><ul class="ingredients-list">';
				foreach ( $lines as $line ) {
					$line = trim( $line );
					if ( ! empty( $line ) ) {
						if ( preg_match( '/^\[(.+)\]$/', $line, $matches ) ) {
							$output .= '</ul><h4 class="section-title">' . esc_html( $matches[1] ) . '</h4><ul class="ingredients-list">';
						} else {
							$output .= '<li>' . esc_html( $line ) . '</li>';
						}
					}
				}
				$output .= '</ul></div>';
				return $output;

			case 'list':
				return $ingredients_meta;

			case 'grouped':
				return $ingredients_meta;
		}

		return $ingredients_meta;
	}

	/**
	 * Shortcode for displaying recipe ingredients
	 *
	 * Usage: [recipe_ingredients id="123" format="html"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Output HTML.
	 */
	public static function recipe_ingredients_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'     => get_the_ID(),
				'format' => 'html',
			),
			$atts,
			'recipe_ingredients'
		);

		$recipe_id = absint( $atts['id'] );
		if ( ! $recipe_id ) {
			return '';
		}

		return self::get_recipe_ingredients_formatted( $recipe_id, $atts['format'] );
	}

	/**
	 * Shortcode for displaying recipe section
	 *
	 * Usage: [recipe_section type="ingredients"] or [recipe_section type="instructions"]
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @return string Output HTML.
	 */
	public static function recipe_section_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts(
			array(
				'id'   => get_the_ID(),
				'type' => 'ingredients',
			),
			$atts,
			'recipe_section'
		);

		$recipe_id = absint( $atts['id'] );
		if ( ! $recipe_id ) {
			return '';
		}

		switch ( $atts['type'] ) {
			case 'ingredients':
				return self::render_ingredients_section( $recipe_id );

			case 'instructions':
				return self::render_instructions_section( $recipe_id );

			case 'meta':
				return self::render_meta_section( $recipe_id );

			default:
				return '';
		}
	}

	/**
	 * Render ingredients section
	 *
	 * @param int $recipe_id Recipe post ID.
	 * @return string Output HTML.
	 */
	private static function render_ingredients_section( $recipe_id ) {
		$db_ingredients = PL_Recipe_Database::get_recipe_ingredients( $recipe_id );
		$ingredients_meta = get_post_meta( $recipe_id, '_pl_recipe_ingredients', true );

		if ( empty( $db_ingredients ) && empty( $ingredients_meta ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="recipe-ingredients shortcode-section" id="ingredients">
			<h2>
				<span class="recipe-section-icon">🛒</span>
				<?php esc_html_e( 'Ingredients', 'pl-recipe-cookbook' ); ?>
			</h2>
			<div class="recipe-section-content-inner">
				<?php echo self::get_recipe_ingredients_formatted( $recipe_id, 'html' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render instructions section
	 *
	 * @param int $recipe_id Recipe post ID.
	 * @return string Output HTML.
	 */
	private static function render_instructions_section( $recipe_id ) {
		$instructions = get_post_meta( $recipe_id, '_pl_recipe_instructions', true );

		if ( empty( $instructions ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="recipe-instructions shortcode-section" id="instructions">
			<h2>
				<span class="recipe-section-icon">📝</span>
				<?php esc_html_e( 'Instructions', 'pl-recipe-cookbook' ); ?>
			</h2>
			<div class="recipe-section-content-inner">
				<ol class="instructions-list">
					<?php
					$instructions_array = explode( "\n", $instructions );
					$step = 1;
					foreach ( $instructions_array as $instruction ) {
						$instruction = trim( $instruction );
						if ( ! empty( $instruction ) ) {
							echo '<li class="instruction-step">';
							echo '<span class="step-number">' . esc_html( $step ) . '</span>';
							echo '<span class="step-text">' . esc_html( $instruction ) . '</span>';
							echo '</li>';
							$step++;
						}
					}
					?>
				</ol>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render meta info section
	 *
	 * @param int $recipe_id Recipe post ID.
	 * @return string Output HTML.
	 */
	private static function render_meta_section( $recipe_id ) {
		$prep_time = get_post_meta( $recipe_id, '_pl_recipe_prep_time', true );
		$cook_time = get_post_meta( $recipe_id, '_pl_recipe_cook_time', true );
		$servings  = get_post_meta( $recipe_id, '_pl_recipe_servings', true );
		$difficulty = get_post_meta( $recipe_id, '_pl_recipe_difficulty', true );

		if ( empty( $prep_time ) && empty( $cook_time ) && empty( $servings ) && empty( $difficulty ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="recipe-meta-info shortcode-section">
			<?php if ( $prep_time ) : ?>
				<div class="recipe-meta-item">
					<span class="meta-label"><?php esc_html_e( 'Prep Time:', 'pl-recipe-cookbook' ); ?></span>
					<span class="meta-value"><?php echo esc_html( $prep_time ); ?> <?php esc_html_e( 'min', 'pl-recipe-cookbook' ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( $cook_time ) : ?>
				<div class="recipe-meta-item">
					<span class="meta-label"><?php esc_html_e( 'Cook Time:', 'pl-recipe-cookbook' ); ?></span>
					<span class="meta-value"><?php echo esc_html( $cook_time ); ?> <?php esc_html_e( 'min', 'pl-recipe-cookbook' ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( $servings ) : ?>
				<div class="recipe-meta-item">
					<span class="meta-label"><?php esc_html_e( 'Servings:', 'pl-recipe-cookbook' ); ?></span>
					<span class="meta-value"><?php echo esc_html( $servings ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( $difficulty ) : 
				$difficulty_labels = array(
					'easy' => __( 'Easy', 'pl-recipe-cookbook' ),
					'medium' => __( 'Medium', 'pl-recipe-cookbook' ),
					'hard' => __( 'Hard', 'pl-recipe-cookbook' ),
				);
				$difficulty_label = isset( $difficulty_labels[ $difficulty ] ) ? $difficulty_labels[ $difficulty ] : ucfirst( $difficulty );
			?>
				<div class="recipe-meta-item">
					<span class="meta-label"><?php esc_html_e( 'Difficulty:', 'pl-recipe-cookbook' ); ?></span>
					<span class="meta-value"><?php echo esc_html( $difficulty_label ); ?></span>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}

// Initialize on plugins_loaded.
add_action( 'plugins_loaded', array( 'PL_Recipe_Display_Helpers', 'init' ) );
