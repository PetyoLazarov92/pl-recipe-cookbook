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
							<span class="meta-icon">⏱️</span>
							<span class="meta-label"><?php esc_html_e( 'Prep Time:', 'pl-recipe-cookbook' ); ?></span>
							<span class="meta-value"><?php echo esc_html( $prep_time ); ?> <?php esc_html_e( 'min', 'pl-recipe-cookbook' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( $cook_time ) : ?>
						<div class="recipe-meta-item">
							<span class="meta-icon">🔥</span>
							<span class="meta-label"><?php esc_html_e( 'Cook Time:', 'pl-recipe-cookbook' ); ?></span>
							<span class="meta-value"><?php echo esc_html( $cook_time ); ?> <?php esc_html_e( 'min', 'pl-recipe-cookbook' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( $prep_time && $cook_time ) : ?>
						<div class="recipe-meta-item">
							<span class="meta-icon">⏰</span>
							<span class="meta-label"><?php esc_html_e( 'Total Time:', 'pl-recipe-cookbook' ); ?></span>
							<span class="meta-value"><?php echo esc_html( $prep_time + $cook_time ); ?> <?php esc_html_e( 'min', 'pl-recipe-cookbook' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( $servings ) : ?>
						<div class="recipe-meta-item">
							<span class="meta-icon">👥</span>
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
							<span class="meta-icon">📊</span>
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

			// Smooth scroll for TOC links
			document.querySelectorAll('.toc-link').forEach(link => {
				link.addEventListener('click', function(e) {
					e.preventDefault();
					const target = document.querySelector(this.getAttribute('href'));
					if (target) {
						target.scrollIntoView({ behavior: 'smooth', block: 'start' });
					}
				});
			});

			// Highlight active TOC link on scroll
			window.addEventListener('scroll', function() {
				const sections = document.querySelectorAll('[id]');
				const tocLinks = document.querySelectorAll('.toc-link');
				
				let current = '';
				sections.forEach(section => {
					const sectionTop = section.offsetTop;
					const sectionHeight = section.clientHeight;
					if (pageYOffset >= sectionTop - 100) {
						current = section.getAttribute('id');
					}
				});

				tocLinks.forEach(link => {
					link.classList.remove('active');
					if (link.getAttribute('href') === '#' + current) {
						link.classList.add('active');
					}
				});
			});
			</script>

	<?php endwhile; ?>
</main>

<?php
get_footer();
