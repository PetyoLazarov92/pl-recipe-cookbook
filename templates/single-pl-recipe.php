<?php
/**
 * Template for displaying single recipe
 *
 * @package PLRecipeCookbook
 */

get_header();
?>

<main id="recipe-main" class="recipe-single-wrapper">
	<?php
	while ( have_posts() ) :
		the_post();
		$prep_time    = get_post_meta( get_the_ID(), '_pl_recipe_prep_time', true );
		$cook_time    = get_post_meta( get_the_ID(), '_pl_recipe_cook_time', true );
		$servings     = get_post_meta( get_the_ID(), '_pl_recipe_servings', true );
		$difficulty   = get_post_meta( get_the_ID(), '_pl_recipe_difficulty', true );
		$ingredients  = get_post_meta( get_the_ID(), '_pl_recipe_ingredients', true );
		$instructions = get_post_meta( get_the_ID(), '_pl_recipe_instructions', true );
		$categories   = get_the_terms( get_the_ID(), 'pl_recipe_cat' );
		$tags         = get_the_terms( get_the_ID(), 'pl_recipe_tag' );
		?>

		<div class="recipe-layout-container">
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'recipe-single' ); ?>>
			<header class="recipe-header">
				<h1 class="recipe-title"><?php the_title(); ?></h1>
				
				<?php if ( has_excerpt() ) : ?>
					<div class="recipe-excerpt">
						<?php the_excerpt(); ?>
					</div>
				<?php endif; ?>
			</header>

			<!-- Hero Section: Image + Meta Info -->
			<div class="recipe-hero-section">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="recipe-featured-image">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
				<?php endif; ?>

				<div class="recipe-meta-info">
					<?php if ( $prep_time ) : ?>
						<div class="recipe-meta-item">
							<span class="meta-icon">
								<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/>
								</svg>
							</span>
							<span class="meta-label"><?php esc_html_e( 'Prep Time:', 'pl-recipe-cookbook' ); ?></span>
							<span class="meta-value"><?php echo esc_html( $prep_time ); ?> <?php esc_html_e( 'min', 'pl-recipe-cookbook' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( $cook_time ) : ?>
						<div class="recipe-meta-item">
							<span class="meta-icon">
								<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path d="M8.1 13.34l2.83-2.83L3.91 3.5c-1.56 1.56-1.56 4.09 0 5.66l4.19 4.18zm6.78-1.81c1.53.71 3.68.21 5.27-1.38 1.91-1.91 2.28-4.65.81-6.12-1.46-1.46-4.2-1.1-6.12.81-1.59 1.59-2.09 3.74-1.38 5.27L3.7 19.87l1.41 1.41L12 14.41l6.88 6.88 1.41-1.41L13.41 13l1.47-1.47z"/>
								</svg>
							</span>
							<span class="meta-label"><?php esc_html_e( 'Cook Time:', 'pl-recipe-cookbook' ); ?></span>
							<span class="meta-value"><?php echo esc_html( $cook_time ); ?> <?php esc_html_e( 'min', 'pl-recipe-cookbook' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( $prep_time && $cook_time ) : ?>
						<div class="recipe-meta-item">
							<span class="meta-icon">
								<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
								</svg>
							</span>
							<span class="meta-label"><?php esc_html_e( 'Total Time:', 'pl-recipe-cookbook' ); ?></span>
							<span class="meta-value"><?php echo esc_html( $prep_time + $cook_time ); ?> <?php esc_html_e( 'min', 'pl-recipe-cookbook' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( $servings ) : ?>
						<div class="recipe-meta-item">
							<span class="meta-icon">
								<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
								</svg>
							</span>
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
							<span class="meta-icon">
								<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
								</svg>
							</span>
							<span class="meta-label"><?php esc_html_e( 'Difficulty:', 'pl-recipe-cookbook' ); ?></span>
							<span class="meta-value"><?php echo esc_html( $difficulty_label ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Main Content + Sidebar -->
			<div class="recipe-content-wrapper">
				<!-- Main Content Area -->
				<div class="recipe-main-content">
					
					<!-- Mobile Table of Contents -->
					<div class="recipe-toc-mobile">
						<div class="recipe-toc-mobile-wrapper">
							<h3 class="recipe-toc-title"><?php esc_html_e( 'Jump to Section', 'pl-recipe-cookbook' ); ?></h3>
							<nav class="recipe-toc">
								<ul class="recipe-toc-list">
									<?php if ( get_the_content() ) : ?>
										<li><a href="#description" class="toc-link"><?php esc_html_e( 'Description', 'pl-recipe-cookbook' ); ?></a></li>
									<?php endif; ?>
									
									<?php if ( $ingredients ) : ?>
										<li><a href="#ingredients" class="toc-link"><?php esc_html_e( 'Ingredients', 'pl-recipe-cookbook' ); ?></a></li>
									<?php endif; ?>
									
									<?php if ( $instructions ) : ?>
										<li><a href="#instructions" class="toc-link"><?php esc_html_e( 'Instructions', 'pl-recipe-cookbook' ); ?></a></li>
									<?php endif; ?>
									
									<?php if ( $categories || $tags ) : ?>
										<li><a href="#categories-tags" class="toc-link"><?php esc_html_e( 'Categories & Tags', 'pl-recipe-cookbook' ); ?></a></li>
									<?php endif; ?>
								</ul>
							</nav>
						</div>
					</div>
					
					<?php if ( get_the_content() ) : ?>
						<div class="recipe-description" id="description">
							<h2><?php esc_html_e( 'Description', 'pl-recipe-cookbook' ); ?></h2>
							<div class="recipe-content">
								<?php the_content(); ?>
							</div>
						</div>
					<?php endif; ?>

					<div class="recipe-details-wrapper">
						<?php if ( $ingredients ) : ?>
							<div class="recipe-ingredients" id="ingredients">
								<div class="recipe-section-header" onclick="toggleSection(this)">
									<h2>
										<span class="recipe-section-icon">🛒</span>
										<?php esc_html_e( 'Ingredients', 'pl-recipe-cookbook' ); ?>
									</h2>
									<span class="recipe-section-toggle">▼</span>
								</div>
								<div class="recipe-section-content">
									<div class="recipe-section-content-inner">
										<div class="shopping-mode-toggle">
											<label>
												<input type="checkbox" id="shopping-mode-checkbox" onchange="toggleShoppingMode()">
												<span>🛍️ <?php esc_html_e( 'Shopping Mode', 'pl-recipe-cookbook' ); ?></span>
											</label>
										</div>
										<div id="ingredients-list">
											<?php
											$ingredients_array = explode( "\n", $ingredients );
											$current_section = '';
											
											foreach ( $ingredients_array as $line ) {
												$line = trim( $line );
												if ( empty( $line ) ) {
													continue;
												}
												
												// Check if line is a section header [Section Name]
												if ( preg_match( '/^\[(.+)\]$/', $line, $matches ) ) {
													if ( $current_section ) {
														echo '</ul></div>'; // Close previous section
													}
													$current_section = $matches[1];
													echo '<div class="ingredients-section">';
													echo '<h3 class="ingredients-section-title">' . esc_html( $current_section ) . '</h3>';
													echo '<ul class="ingredients-list">';
												} else {
													// Regular ingredient
													if ( ! $current_section ) {
														// Start default section if none exists
														$current_section = 'default';
														echo '<div class="ingredients-section">';
														echo '<ul class="ingredients-list">';
													}
													echo '<li>';
													echo '<input type="checkbox" class="ingredient-checkbox" onchange="toggleIngredient(this)">';
													echo '<span class="ingredient-text">' . esc_html( $line ) . '</span>';
													echo '</li>';
												}
											}
											
											if ( $current_section ) {
												echo '</ul></div>'; // Close last section
											}
											?>
										</div>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $instructions ) : ?>
							<div class="recipe-instructions" id="instructions">
								<div class="recipe-section-header" onclick="toggleSection(this)">
									<h2>
										<span class="recipe-section-icon">📝</span>
										<?php esc_html_e( 'Instructions', 'pl-recipe-cookbook' ); ?>
									</h2>
									<span class="recipe-section-toggle">▼</span>
								</div>
								<div class="recipe-section-content">
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
							</div>
						<?php endif; ?>
					</div>

					<?php if ( $categories || $tags ) : ?>
						<div class="recipe-footer" id="categories-tags">
							<?php if ( $categories ) : ?>
								<div class="recipe-categories">
									<strong><?php esc_html_e( 'Categories:', 'pl-recipe-cookbook' ); ?></strong>
									<?php
									foreach ( $categories as $category ) {
										echo '<a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
										if ( $category !== end( $categories ) ) {
											echo ', ';
										}
									}
									?>
								</div>
							<?php endif; ?>

							<?php if ( $tags ) : ?>
								<div class="recipe-tags">
									<strong><?php esc_html_e( 'Tags:', 'pl-recipe-cookbook' ); ?></strong>
									<?php
									foreach ( $tags as $tag ) {
										echo '<a href="' . esc_url( get_term_link( $tag ) ) . '" class="recipe-tag">' . esc_html( $tag->name ) . '</a>';
									}
									?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

				</div><!-- .recipe-main-content -->

			</div><!-- .recipe-content-wrapper -->

		</article>

		<!-- Sidebar with Table of Contents (Outside main container) -->
		<aside class="recipe-sidebar">
			<div class="recipe-toc-wrapper">
				<h3 class="recipe-toc-title"><?php esc_html_e( 'Jump to Section', 'pl-recipe-cookbook' ); ?></h3>
				<nav class="recipe-toc">
					<ul class="recipe-toc-list">
						<?php if ( get_the_content() ) : ?>
							<li><a href="#description" class="toc-link"><?php esc_html_e( 'Description', 'pl-recipe-cookbook' ); ?></a></li>
						<?php endif; ?>
						
						<?php if ( $ingredients ) : ?>
							<li><a href="#ingredients" class="toc-link"><?php esc_html_e( 'Ingredients', 'pl-recipe-cookbook' ); ?></a></li>
						<?php endif; ?>
						
						<?php if ( $instructions ) : ?>
							<li><a href="#instructions" class="toc-link"><?php esc_html_e( 'Instructions', 'pl-recipe-cookbook' ); ?></a></li>
						<?php endif; ?>
						
						<?php if ( $categories || $tags ) : ?>
							<li><a href="#categories-tags" class="toc-link"><?php esc_html_e( 'Categories & Tags', 'pl-recipe-cookbook' ); ?></a></li>
						<?php endif; ?>
					</ul>
				</nav>
			</div>
		</aside>

			<?php
			// Related Recipes Section - Outside article, inside container
			$current_categories = wp_get_post_terms( get_the_ID(), 'pl_recipe_cat', array( 'fields' => 'ids' ) );
			
			if ( ! empty( $current_categories ) && ! is_wp_error( $current_categories ) ) {
				$related_args = array(
					'post_type'      => 'pl_recipe',
					'posts_per_page' => 3,
					'post__not_in'   => array( get_the_ID() ),
					'tax_query'      => array(
						array(
							'taxonomy' => 'pl_recipe_cat',
							'field'    => 'term_id',
							'terms'    => $current_categories,
						),
					),
					'orderby'        => 'rand',
				);
				
				$related_query = new WP_Query( $related_args );
				
				if ( $related_query->have_posts() ) :
					?>
					<div class="related-recipes-section">
						<h2 class="related-recipes-title"><?php esc_html_e( 'You might also like', 'pl-recipe-cookbook' ); ?></h2>
						<div class="related-recipes-grid">
							<?php
							while ( $related_query->have_posts() ) :
								$related_query->the_post();
								$rel_difficulty = get_post_meta( get_the_ID(), '_pl_recipe_difficulty', true );
								$rel_prep_time  = get_post_meta( get_the_ID(), '_pl_recipe_prep_time', true );
								$rel_cook_time  = get_post_meta( get_the_ID(), '_pl_recipe_cook_time', true );
								?>
								<article class="related-recipe-card">
									<a href="<?php the_permalink(); ?>" class="related-recipe-link">
										<div class="related-recipe-image">
											<?php
											if ( has_post_thumbnail() ) {
												the_post_thumbnail( 'medium' );
											} else {
												// Default image
												echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" class="default-recipe-image">
													<defs>
														<linearGradient id="grad' . get_the_ID() . '" x1="0%" y1="0%" x2="100%" y2="100%">
															<stop offset="0%" style="stop-color:#4CAF50;stop-opacity:1" />
															<stop offset="100%" style="stop-color:#45a049;stop-opacity:1" />
														</linearGradient>
													</defs>
													<rect width="400" height="300" fill="url(#grad' . get_the_ID() . ')"/>
													<circle cx="200" cy="120" r="50" fill="rgba(255,255,255,0.2)"/>
													<path d="M 150 150 Q 200 100 250 150 Q 200 200 150 150" fill="rgba(255,255,255,0.3)"/>
													<circle cx="170" cy="140" r="8" fill="rgba(255,255,255,0.4)"/>
													<circle cx="230" cy="140" r="8" fill="rgba(255,255,255,0.4)"/>
													<circle cx="200" cy="170" r="6" fill="rgba(255,255,255,0.4)"/>
												</svg>';
											}
											?>
											<?php if ( $rel_difficulty ) : 
												$difficulty_labels = array(
													'easy' => __( 'Easy', 'pl-recipe-cookbook' ),
													'medium' => __( 'Medium', 'pl-recipe-cookbook' ),
													'hard' => __( 'Hard', 'pl-recipe-cookbook' ),
												);
												$difficulty_label = isset( $difficulty_labels[ $rel_difficulty ] ) ? $difficulty_labels[ $rel_difficulty ] : ucfirst( $rel_difficulty );
											?>
												<span class="difficulty-badge difficulty-<?php echo esc_attr( $rel_difficulty ); ?>">
													<?php echo esc_html( $difficulty_label ); ?>
												</span>
											<?php endif; ?>
										</div>
										<div class="related-recipe-content">
											<h3 class="related-recipe-title"><?php the_title(); ?></h3>
											<div class="related-recipe-meta">
												<?php if ( $rel_prep_time || $rel_cook_time ) : ?>
													<span class="related-meta-item">
														<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
															<path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
														</svg>
														<?php 
														$total = ( $rel_prep_time ? intval( $rel_prep_time ) : 0 ) + ( $rel_cook_time ? intval( $rel_cook_time ) : 0 );
														echo esc_html( $total ) . ' ' . esc_html__( 'min', 'pl-recipe-cookbook' );
														?>
													</span>
												<?php endif; ?>
											</div>
										</div>
									</a>
								</article>
								<?php
							endwhile;
							wp_reset_postdata();
							?>
						</div>
					</div>
					<?php
				endif;
			}
			?>

	</div><!-- .recipe-layout-container -->

	<script>
			function toggleSection(header) {
				header.classList.toggle('collapsed');
				const content = header.nextElementSibling;
				content.classList.toggle('collapsed');
			}

			function toggleShoppingMode() {
				const checkbox = document.getElementById('shopping-mode-checkbox');
				const container = document.getElementById('ingredients-list');
				if (checkbox.checked) {
					container.classList.add('shopping-mode-active');
				} else {
					container.classList.remove('shopping-mode-active');
					// Uncheck all ingredients
					const checkboxes = container.querySelectorAll('.ingredient-checkbox');
					checkboxes.forEach(cb => {
						cb.checked = false;
						cb.parentElement.classList.remove('checked');
					});
				}
			}

			function toggleIngredient(checkbox) {
				if (checkbox.checked) {
					checkbox.parentElement.classList.add('checked');
				} else {
					checkbox.parentElement.classList.remove('checked');
				}
			}

			// Smooth scroll for TOC links with offset for sticky header
			document.querySelectorAll('.toc-link').forEach(link => {
				link.addEventListener('click', function(e) {
					e.preventDefault();
					const target = document.querySelector(this.getAttribute('href'));
					if (target) {
						const headerOffset = 120; // Offset for sticky navigation
						const elementPosition = target.getBoundingClientRect().top;
						const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
						
						window.scrollTo({
							top: offsetPosition,
							behavior: 'smooth'
						});
					}
				});
			});

			// Highlight active TOC link on scroll
			let ticking = false;
			window.addEventListener('scroll', function() {
				if (!ticking) {
					window.requestAnimationFrame(function() {
						const tocLinks = document.querySelectorAll('.toc-link');
						const headerOffset = 150;
						
						let current = '';
						let currentPosition = -1;
						
						tocLinks.forEach(link => {
							const href = link.getAttribute('href');
							if (!href) return;
							
							const section = document.querySelector(href);
							if (!section) return;
							
							const sectionTop = section.getBoundingClientRect().top + window.pageYOffset;
							const sectionBottom = sectionTop + section.offsetHeight;
							const scrollPos = window.pageYOffset + headerOffset;
							
							if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
								if (sectionTop > currentPosition) {
									currentPosition = sectionTop;
									current = href;
								}
							}
						});
						
						tocLinks.forEach(link => {
							link.classList.remove('active');
							if (link.getAttribute('href') === current) {
								link.classList.add('active');
							}
						});
						
						ticking = false;
					});
					ticking = true;
				}
			});
			</script>

	<?php endwhile; ?>
</main>

<?php
get_footer();
