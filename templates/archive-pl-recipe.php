<?php
/**
 * Template for displaying recipe archive
 *
 * @package PLRecipeCookbook
 */

get_header();
?>

<main id="recipe-archive-main" class="recipe-archive-wrapper">
	<header class="archive-header">
		<div class="archive-title-wrapper">
			<span class="archive-icon">🍽️</span>
			<h1 class="archive-title"><?php esc_html_e( 'All Recipes', 'pl-recipe-cookbook' ); ?></h1>
		</div>
		
		<!-- Search Bar -->
		<div class="recipe-search-bar">
			<form role="search" method="get" class="recipe-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="hidden" name="post_type" value="pl_recipe">
				<input type="search" 
				       class="recipe-search-input" 
				       placeholder="<?php esc_attr_e( 'Search recipes...', 'pl-recipe-cookbook' ); ?>" 
				       value="<?php echo get_search_query(); ?>" 
				       name="s">
				<button type="submit" class="recipe-search-button">
					<svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="10" cy="10" r="7"></circle>
						<line x1="21" y1="21" x2="15" y2="15"></line>
					</svg>
					<span class="screen-reader-text"><?php esc_html_e( 'Search', 'pl-recipe-cookbook' ); ?></span>
				</button>
			</form>
		</div>
	</header>
	
	<?php
	if ( get_the_archive_description() ) :
		?>
		<div class="archive-description">
			<?php the_archive_description(); ?>
		</div>
	<?php endif; ?>

	<!-- Recipe Filters -->
	<div class="recipe-filters">
		<div class="recipe-filters-inner">
			<?php
			$categories = get_terms(
				array(
					'taxonomy'   => 'pl_recipe_cat',
					'hide_empty' => true,
					'number'     => 10,
				)
			);

			$tags = get_terms(
				array(
					'taxonomy'   => 'pl_recipe_tag',
					'hide_empty' => true,
					'number'     => 10,
				)
			);

			if ( is_wp_error( $categories ) ) {
				$categories = array();
			}

			if ( is_wp_error( $tags ) ) {
				$tags = array();
			}

			// Check if there are more categories.
			$total_categories = wp_count_terms(
				array(
					'taxonomy'   => 'pl_recipe_cat',
					'hide_empty' => true,
				)
			);
			$has_more_categories = ( ! is_wp_error( $total_categories ) && $total_categories > 10 );

			// Check if there are more tags.
			$total_tags = wp_count_terms(
				array(
					'taxonomy'   => 'pl_recipe_tag',
					'hide_empty' => true,
				)
			);
			$has_more_tags = ( ! is_wp_error( $total_tags ) && $total_tags > 10 );

			$current_category = '';
			$current_tag      = '';
			
			// Get current term if on taxonomy archive
			if ( is_tax( 'pl_recipe_cat' ) ) {
				$current_category = get_queried_object();
			} elseif ( is_tax( 'pl_recipe_tag' ) ) {
				$current_tag = get_queried_object();
			}
			?>

			<?php if ( ! empty( $categories ) || ! empty( $tags ) ) : ?>
				<!-- Categories Filter -->
				<?php if ( ! empty( $categories ) ) : ?>
					<div class="filter-group">
						<h3 class="filter-title"><?php esc_html_e( 'Categories', 'pl-recipe-cookbook' ); ?></h3>
						<div class="filter-pills">
							<a href="<?php echo esc_url( get_post_type_archive_link( 'pl_recipe' ) ); ?>" 
							   class="filter-pill active">
								<?php esc_html_e( 'All', 'pl-recipe-cookbook' ); ?>
							</a>
							<?php foreach ( $categories as $category ) : 
								$term_link = get_term_link( $category );
								if ( is_wp_error( $term_link ) ) {
									continue;
								}
								$is_current = ( $current_category && $current_category->term_id === $category->term_id );
							?>
								<a href="<?php echo esc_url( $term_link ); ?>" 
								   class="filter-pill <?php echo $is_current ? 'active' : ''; ?>"
								   data-term-id="<?php echo esc_attr( $category->term_id ); ?>">
									<?php echo esc_html( $category->name ); ?>
									<span class="filter-count"><?php echo esc_html( $category->count ); ?></span>
								</a>
							<?php endforeach; ?>
							<?php if ( $has_more_categories ) : ?>
								<button class="filter-pill load-more-categories load-more-button" 
								        data-offset="10" 
								        data-original-text="<?php esc_attr_e( 'Load More', 'pl-recipe-cookbook' ); ?>"
								        data-loading-text="<?php esc_attr_e( 'Loading...', 'pl-recipe-cookbook' ); ?>">
									<?php esc_html_e( 'Load More', 'pl-recipe-cookbook' ); ?>
								</button>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Tags Filter -->
				<?php if ( ! empty( $tags ) ) : ?>
					<div class="filter-group">
						<h3 class="filter-title"><?php esc_html_e( 'Tags', 'pl-recipe-cookbook' ); ?></h3>
						<div class="filter-pills">
							<a href="<?php echo esc_url( get_post_type_archive_link( 'pl_recipe' ) ); ?>" 
							   class="filter-pill active">
								<?php esc_html_e( 'All', 'pl-recipe-cookbook' ); ?>
							</a>
							<?php foreach ( $tags as $tag ) : 
								$term_link = get_term_link( $tag );
								if ( is_wp_error( $term_link ) ) {
									continue;
								}
								$is_current = ( $current_tag && $current_tag->term_id === $tag->term_id );
							?>
								<a href="<?php echo esc_url( $term_link ); ?>" 
								   class="filter-pill <?php echo $is_current ? 'active' : ''; ?>"
								   data-term-id="<?php echo esc_attr( $tag->term_id ); ?>">
									<?php echo esc_html( $tag->name ); ?>
									<span class="filter-count"><?php echo esc_html( $tag->count ); ?></span>
								</a>
							<?php endforeach; ?>
							<?php if ( $has_more_tags ) : ?>
								<button class="filter-pill load-more-tags load-more-button" 
								        data-offset="10" 
								        data-original-text="<?php esc_attr_e( 'Load More', 'pl-recipe-cookbook' ); ?>"
								        data-loading-text="<?php esc_attr_e( 'Loading...', 'pl-recipe-cookbook' ); ?>">
									<?php esc_html_e( 'Load More', 'pl-recipe-cookbook' ); ?>
								</button>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="recipe-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				$prep_time  = get_post_meta( get_the_ID(), '_pl_recipe_prep_time', true );
				$cook_time  = get_post_meta( get_the_ID(), '_pl_recipe_cook_time', true );
				$servings   = get_post_meta( get_the_ID(), '_pl_recipe_servings', true );
				$difficulty = get_post_meta( get_the_ID(), '_pl_recipe_difficulty', true );
				?>

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'recipe-card' ); ?>>
					<a href="<?php the_permalink(); ?>" class="recipe-card-link">
						<div class="recipe-card-image">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium_large' ); ?>
							<?php else : ?>
								<?php
								$placeholder_svg = plugins_url( 'assets/images/recipe-placeholder.svg', dirname( __FILE__ ) );
								?>
								<img src="<?php echo esc_url( $placeholder_svg ); ?>" 
								     alt="<?php the_title_attribute(); ?>" 
								     class="recipe-placeholder-image">
							<?php endif; ?>
							<?php if ( $difficulty ) : 
								$difficulty_labels = array(
									'easy' => __( 'Easy', 'pl-recipe-cookbook' ),
									'medium' => __( 'Medium', 'pl-recipe-cookbook' ),
									'hard' => __( 'Hard', 'pl-recipe-cookbook' ),
								);
								$difficulty_label = isset( $difficulty_labels[ $difficulty ] ) ? $difficulty_labels[ $difficulty ] : ucfirst( $difficulty );
							?>
								<span class="recipe-badge recipe-badge-<?php echo esc_attr( $difficulty ); ?>">
									<?php echo esc_html( $difficulty_label ); ?>
								</span>
							<?php endif; ?>
						</div>

						<div class="recipe-card-content">
							<h2 class="recipe-card-title"><?php the_title(); ?></h2>

							<?php if ( has_excerpt() ) : ?>
								<div class="recipe-card-excerpt">
									<?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?>
								</div>
							<?php endif; ?>

							<div class="recipe-card-meta">
								<?php if ( $prep_time || $cook_time ) : ?>
									<span class="meta-time">
										⏰ <?php echo esc_html( ( $prep_time + $cook_time ) ); ?> <?php esc_html_e( 'min', 'pl-recipe-cookbook' ); ?>
									</span>
								<?php endif; ?>

								<?php if ( $servings ) : ?>
									<span class="meta-servings">
										🍽️ <?php echo esc_html( $servings ); ?> <?php esc_html_e( 'servings', 'pl-recipe-cookbook' ); ?>
									</span>
								<?php endif; ?>
							</div>
						</div>
					</a>
				</article>

			<?php endwhile; ?>
		</div>

		<div class="recipe-pagination">
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'prev_text' => __( '&laquo; Previous', 'pl-recipe-cookbook' ),
					'next_text' => __( 'Next &raquo;', 'pl-recipe-cookbook' ),
				)
			);
			?>
		</div>

	<?php else : ?>
		<div class="no-recipes">
			<p><?php esc_html_e( 'No recipes found.', 'pl-recipe-cookbook' ); ?></p>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
