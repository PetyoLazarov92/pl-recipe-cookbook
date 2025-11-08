<?php
/**
 * Template for displaying recipe tag taxonomy
 *
 * @package PLRecipeCookbook
 */

get_header();

$term = get_queried_object();
?>

<main id="recipe-archive-main" class="recipe-archive-wrapper">
	<header class="archive-header">
		<div class="archive-title-wrapper">
			<span class="archive-icon">🏷️</span>
			<div class="archive-title-content">
				<span class="archive-label"><?php esc_html_e( 'Tag', 'pl-recipe-cookbook' ); ?></span>
				<h1 class="archive-title"><?php echo esc_html( $term->name ); ?></h1>
			</div>
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
					<span class="dashicons dashicons-search"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Search', 'pl-recipe-cookbook' ); ?></span>
				</button>
			</form>
		</div>
	</header>
	
	<?php if ( ! empty( $term->description ) ) : ?>
		<div class="archive-description">
			<?php echo wp_kses_post( $term->description ); ?>
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
			?>

			<?php if ( ! empty( $categories ) || ! empty( $tags ) ) : ?>
				<!-- Categories Filter -->
				<?php if ( ! empty( $categories ) && count( $categories ) > 0 ) : ?>
					<div class="filter-group">
						<h3 class="filter-title"><?php esc_html_e( 'Categories', 'pl-recipe-cookbook' ); ?></h3>
						<div class="filter-pills">
							<a href="<?php echo esc_url( get_post_type_archive_link( 'pl_recipe' ) ); ?>" 
							   class="filter-pill">
								<?php esc_html_e( 'All', 'pl-recipe-cookbook' ); ?>
							</a>
							<?php 
							foreach ( $categories as $category ) : 
								$term_link = get_term_link( $category );
								if ( is_wp_error( $term_link ) ) {
									continue;
								}
								$is_current = ( $category->term_id === $term->term_id );
							?>
								<a href="<?php echo esc_url( $term_link ); ?>" 
								   class="filter-pill <?php echo $is_current ? 'active' : ''; ?>">
									<?php echo esc_html( $category->name ); ?>
									<span class="filter-count"><?php echo esc_html( $category->count ); ?></span>
								</a>
							<?php endforeach; ?>
							<?php if ( $has_more_categories ) : ?>
								<button class="filter-pill load-more-categories load-more-button" 
								        data-offset="10" 
								        data-taxonomy="pl_recipe_cat"
								        data-current-term="<?php echo esc_attr( $term->term_id ); ?>"
								        data-original-text="<?php esc_attr_e( 'Load More', 'pl-recipe-cookbook' ); ?>"
								        data-loading-text="<?php esc_attr_e( 'Loading...', 'pl-recipe-cookbook' ); ?>">
									<?php esc_html_e( 'Load More', 'pl-recipe-cookbook' ); ?>
								</button>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Tags Filter -->
				<?php if ( ! empty( $tags ) && count( $tags ) > 0 ) : ?>
					<div class="filter-group">
						<h3 class="filter-title"><?php esc_html_e( 'Tags', 'pl-recipe-cookbook' ); ?></h3>
						<div class="filter-pills">
							<a href="<?php echo esc_url( get_post_type_archive_link( 'pl_recipe' ) ); ?>" 
							   class="filter-pill">
								<?php esc_html_e( 'All', 'pl-recipe-cookbook' ); ?>
							</a>
							<?php 
							foreach ( $tags as $tag ) : 
								$term_link = get_term_link( $tag );
								if ( is_wp_error( $term_link ) ) {
									continue;
								}
								$is_current = ( $tag->term_id === $term->term_id );
							?>
								<a href="<?php echo esc_url( $term_link ); ?>" 
								   class="filter-pill <?php echo $is_current ? 'active' : ''; ?>">
									<?php echo esc_html( $tag->name ); ?>
									<span class="filter-count"><?php echo esc_html( $tag->count ); ?></span>
								</a>
							<?php endforeach; ?>
							<?php if ( $has_more_tags ) : ?>
								<button class="filter-pill load-more-tags load-more-button" 
								        data-offset="10" 
								        data-taxonomy="pl_recipe_tag"
								        data-current-term="<?php echo esc_attr( $term->term_id ); ?>"
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
								<div class="recipe-placeholder-svg">
									<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
										<defs>
											<linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
												<stop offset="0%" style="stop-color:#4ade80;stop-opacity:0.2" />
												<stop offset="100%" style="stop-color:#22c55e;stop-opacity:0.3" />
											</linearGradient>
										</defs>
										<rect width="400" height="300" fill="url(#grad1)"/>
										<circle cx="200" cy="120" r="50" fill="#22c55e" opacity="0.2"/>
										<path d="M 150 140 Q 200 100 250 140" stroke="#22c55e" stroke-width="3" fill="none" opacity="0.4"/>
										<circle cx="170" cy="140" r="8" fill="#22c55e" opacity="0.3"/>
										<circle cx="200" cy="125" r="8" fill="#22c55e" opacity="0.3"/>
										<circle cx="230" cy="140" r="8" fill="#22c55e" opacity="0.3"/>
										<path d="M 180 180 L 220 180 L 225 200 L 175 200 Z" fill="#22c55e" opacity="0.2"/>
										<ellipse cx="200" cy="200" rx="35" ry="15" fill="#22c55e" opacity="0.15"/>
									</svg>
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

		<!-- Pagination -->
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
			<p><?php esc_html_e( 'No recipes found with this tag.', 'pl-recipe-cookbook' ); ?></p>
		</div>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
