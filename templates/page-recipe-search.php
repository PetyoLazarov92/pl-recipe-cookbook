<?php
/**
 * Template Name: Recipe Search by Ingredients
 * Template Post Type: page
 *
 * @package PLRecipeCookbook
 */

get_header();

/**
 * Capitalize first letter of text (UTF-8 safe)
 *
 * @param string $text Text to capitalize.
 * @return string Capitalized text.
 */
function pl_ucfirst( $text ) {
	if ( empty( $text ) ) {
		return $text;
	}
	return mb_strtoupper( mb_substr( $text, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $text, 1, null, 'UTF-8' );
}

$categories = PL_Recipe_Ingredient_Search::get_categories_with_ingredients();
?>

<main id="recipe-search-main" class="recipe-search-page">
	<div class="recipe-search-container">
		<header class="archive-header">
			<div class="archive-title-wrapper">
				<h1 class="archive-title"><?php esc_html_e( 'Find Recipe by Ingredients', 'pl-recipe-cookbook' ); ?></h1>
			</div>
			<p class="archive-description">
				<?php esc_html_e( 'Select the ingredients you have and discover delicious recipes you can make!', 'pl-recipe-cookbook' ); ?>
			</p>
		</header>

		<div class="recipe-search-main">
			<aside class="recipe-search-sidebar">
				 <!-- Selected Ingredients Display (First) -->
				<div class="selected-ingredients-widget">
					<div class="widget-header">
						<h3><?php esc_html_e( 'Selected:', 'pl-recipe-cookbook' ); ?> <span id="selected-count">0</span></h3>
						<button type="button" id="clear-all-btn" class="clear-all-button" style="display: none;">
							<?php esc_html_e( 'Clear All', 'pl-recipe-cookbook' ); ?>
						</button>
					</div>
					<div class="widget-content">
						<div id="selected-ingredients" class="selected-ingredients-list">
							<p class="no-selection"><?php esc_html_e( 'No ingredients selected yet', 'pl-recipe-cookbook' ); ?></p>
						</div>
					</div>
					<button type="button" class="widget-toggle" data-expanded="false" style="display: none;">
						<?php esc_html_e( 'Show More', 'pl-recipe-cookbook' ); ?>
					</button>
				</div>

				<!-- Ingredient Selection Filters -->
				<div class="search-filters">
					<h2><?php esc_html_e( 'Select Ingredients', 'pl-recipe-cookbook' ); ?></h2>

					<!-- Search Mode Toggle -->
					<div class="search-mode-toggle">
						<label class="search-mode-option">
							<input type="radio" name="search_logic" value="AND" checked>
							<span class="radio-custom"></span>
							<span class="radio-label"><?php esc_html_e( 'All selected (exact match)', 'pl-recipe-cookbook' ); ?></span>
						</label>
						<label class="search-mode-option">
							<input type="radio" name="search_logic" value="OR">
							<span class="radio-custom"></span>
							<span class="radio-label"><?php esc_html_e( 'At least one ingredient', 'pl-recipe-cookbook' ); ?></span>
						</label>
					</div>

					<!-- Categories with Ingredients -->
					<div class="ingredient-categories">
						<?php if ( ! empty( $categories ) ) : ?>
							<?php foreach ( $categories as $category ) : ?>
								<div class="ingredient-category">
									<h3 class="category-title">
										<?php echo esc_html( pl_ucfirst( $category->name ) ); ?>
										<span class="category-count">(<?php echo esc_html( $category->ingredient_count ); ?>)</span>
									</h3>
									<div class="ingredient-list">
										<?php foreach ( $category->ingredients as $ingredient ) : ?>
											<label class="ingredient-checkbox">
												<input type="checkbox" 
													   name="ingredients[]" 
													   value="<?php echo esc_attr( $ingredient->id ); ?>"
													   data-name="<?php echo esc_attr( $ingredient->name ); ?>">
												<span><?php echo esc_html( pl_ucfirst( $ingredient->name ) ); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						<?php else : ?>
							<p><?php esc_html_e( 'No ingredients available.', 'pl-recipe-cookbook' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</aside>

			<main class="recipe-search-content">
				<div class="search-results-header">
					<h2>
						<?php esc_html_e( 'Search Results', 'pl-recipe-cookbook' ); ?>
						<span id="results-count" class="results-count"></span>
					</h2>
					<div id="search-status" class="search-status"></div>
				</div>

				<div id="recipes-container" class="recipe-grid">
					<div class="search-prompt">
						<span class="search-prompt-icon">🔍</span>
						<p><?php esc_html_e( 'Select ingredients from the left to find recipes', 'pl-recipe-cookbook' ); ?></p>
					</div>
				</div>

				<div class="load-more-container">
					<button type="button" id="load-more-btn" class="button load-more-button" style="display: none;">
						<?php esc_html_e( 'Load More Recipes', 'pl-recipe-cookbook' ); ?>
					</button>
				</div>
			</main>
		</div>
	</div>
</main>

<?php get_footer(); ?>
