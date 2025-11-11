/**
 * Recipe Search by Ingredients - Frontend JS
 */
(function($) {
	'use strict';

	class RecipeIngredientSearch {
		constructor() {
			this.selectedIngredients = [];
			this.cursor = null;
			this.isLoading = false;
			this.logic = 'AND';

			this.init();
		}

		init() {
			this.loadFromURL();
			this.bindEvents();
		}

		/**
		 * Load state from URL hash parameter
		 */
		loadFromURL() {
			const urlParams = new URLSearchParams(window.location.search);
			const hash = urlParams.get('ing');
			const logicParam = urlParams.get('logic');
			
			// Load search logic
			if (logicParam === 'OR' || logicParam === 'AND') {
				this.logic = logicParam;
				$(`input[name="search_logic"][value="${logicParam}"]`).prop('checked', true);
			}
			
			if (!hash) {
				return;
			}

			try {
				// Decode the hash (base64 encoded ingredient IDs)
				const decoded = atob(hash);
				const ingredientIds = decoded.split(',').map(id => parseInt(id)).filter(id => !isNaN(id));
				
				if (ingredientIds.length === 0) {
					return;
				}

				// Check checkboxes and add ingredients
				ingredientIds.forEach(id => {
					const $checkbox = $(`.ingredient-checkbox input[value="${id}"]`);
					if ($checkbox.length) {
						$checkbox.prop('checked', true);
						const name = $checkbox.data('name');
						this.selectedIngredients.push({ id, name });
					}
				});

				this.updateCategoryActiveStates();
				this.updateSelectedDisplay();
				
				// Load recipes after a short delay to ensure DOM is ready
				setTimeout(() => {
					this.resetSearch();
				}, 100);

			} catch (e) {
				console.error('Error loading from URL:', e);
			}
		}

		/**
		 * Update URL with current selection
		 */
		updateURL() {
			const url = new URL(window.location);
			
			if (this.selectedIngredients.length === 0) {
				// Remove parameters if no selection
				url.searchParams.delete('ing');
				url.searchParams.delete('logic');
				window.history.replaceState({}, '', url);
				return;
			}

			// Create hash from ingredient IDs
			const ids = this.selectedIngredients.map(i => i.id).join(',');
			const hash = btoa(ids); // Base64 encode

			// Update URL without reload
			url.searchParams.set('ing', hash);
			url.searchParams.set('logic', this.logic);
			window.history.replaceState({}, '', url);
		}

		bindEvents() {
			// Ingredient checkbox change
			$(document).on('change', '.ingredient-checkbox input[type="checkbox"]', (e) => {
				this.handleIngredientChange(e);
			});

			// Search logic toggle
			$('input[name="search_logic"]').on('change', (e) => {
				this.logic = $(e.target).val();
				this.updateURL();
				this.resetSearch();
			});

			// Remove selected ingredient
			$(document).on('click', '.remove-ingredient', (e) => {
				const ingredientId = $(e.target).data('ingredient-id');
				this.removeIngredient(ingredientId);
			});

			// Clear all button
			$('#clear-all-btn').on('click', () => {
				this.clearAllIngredients();
			});

			// Load more button
			$('#load-more-btn').on('click', () => {
				this.loadRecipes(true);
			});

			// Category toggle
			$(document).on('click', '.category-title', function() {
				const $category = $(this).parent('.ingredient-category');
				const $list = $(this).next('.ingredient-list');
				const isOpen = $list.is(':visible');
				
				if (isOpen) {
					$category.removeClass('active');
					$list.slideUp(200);
				} else {
					$category.addClass('active');
					$list.slideDown(200);
				}
			});

			// Selected ingredients widget toggle
			$(document).on('click', '.selected-ingredients-widget .widget-toggle', function() {
				const $widget = $(this).closest('.selected-ingredients-widget');
				const $content = $widget.find('.widget-content');
				const $btn = $(this);
				const isExpanded = $btn.attr('data-expanded') === 'true';

				if (isExpanded) {
					$content.removeClass('expanded');
					$btn.attr('data-expanded', 'false')
						.text(plRecipeSearch.i18n.showMore);
				} else {
					$content.addClass('expanded');
					$btn.attr('data-expanded', 'true')
						.text(plRecipeSearch.i18n.hide);
				}
			});
		}

		handleIngredientChange(e) {
			const $checkbox = $(e.target);
			const ingredientId = parseInt($checkbox.val());
			const ingredientName = $checkbox.data('name');

			if ($checkbox.is(':checked')) {
				this.addIngredient(ingredientId, ingredientName);
			} else {
				this.removeIngredient(ingredientId);
			}
		}

		addIngredient(id, name) {
			if (this.selectedIngredients.find(i => i.id === id)) {
				return;
			}

			this.selectedIngredients.push({ id, name });
			this.updateCategoryActiveStates();
			this.updateSelectedDisplay();
			this.updateURL();
			this.resetSearch();
		}

		removeIngredient(id) {
			this.selectedIngredients = this.selectedIngredients.filter(i => i.id !== id);
			
			// Uncheck checkbox
			$(`.ingredient-checkbox input[value="${id}"]`).prop('checked', false);
			
			this.updateCategoryActiveStates();
			this.updateSelectedDisplay();
			this.updateURL();
			this.resetSearch();
		}

		clearAllIngredients() {
			// Clear all checkboxes
			$('.ingredient-checkbox input[type="checkbox"]').prop('checked', false);
			
			// Clear selection
			this.selectedIngredients = [];
			
			this.updateCategoryActiveStates();
			this.updateSelectedDisplay();
			this.updateURL();
			this.resetSearch();
		}

		updateSelectedDisplay() {
			const $container = $('#selected-ingredients');
			const $clearBtn = $('#clear-all-btn');
			const $toggleBtn = $('.selected-ingredients-widget .widget-toggle');
			const count = this.selectedIngredients.length;

			$('#selected-count').text(count);

			if (count === 0) {
				$container.html(`<p class="no-selection">${plRecipeSearch.i18n.noSelection}</p>`);
				$clearBtn.hide();
				$toggleBtn.css('visibility', 'hidden');
				return;
			}

			let html = '';
			this.selectedIngredients.forEach(ingredient => {
				html += `
					<div class="selected-tag">
						<span>${ingredient.name}</span>
						<span class="remove remove-ingredient" data-ingredient-id="${ingredient.id}">×</span>
					</div>
				`;
			});

			$container.html(html);
			$clearBtn.show();

			// Show toggle button if content height exceeds collapsed height
			setTimeout(() => {
			    const $content = $('.selected-ingredients-widget .widget-content');
			    const contentHeight = $content[0].scrollHeight;
			    if (contentHeight > 80) {
			        $toggleBtn.css('visibility', 'visible');
			    } else {
			        $toggleBtn.css('visibility', 'hidden');
			    }
			}, 50);
		}

		/**
		 * Update category active states based on selected ingredients
		 */
		updateCategoryActiveStates() {
			// Remove all active states first
			$('.ingredient-category').removeClass('has-selected');

			if (this.selectedIngredients.length === 0) {
				return;
			}

			// Get selected ingredient IDs
			const selectedIds = this.selectedIngredients.map(i => i.id);

			// Check each category for selected ingredients
			$('.ingredient-category').each(function() {
				const $category = $(this);
				const hasSelected = $category.find('.ingredient-checkbox input:checked').length > 0;
				
				if (hasSelected) {
					$category.addClass('has-selected');
				}
			});
		}

		resetSearch() {
			this.cursor = null;
			$('#recipes-container').empty();
			$('#load-more-btn').hide();
			$('#results-count').text('');
			$('#search-status').empty();

			if (this.selectedIngredients.length > 0) {
				this.loadRecipes(false);
			} else {
				$('#recipes-container').html(`
					<div class="search-prompt">
						<span class="search-prompt-icon">🔍</span>
						<p>${plRecipeSearch.i18n.selectToSearch}</p>
					</div>
				`);
			}
		}

		loadRecipes(append = false) {
			if (this.isLoading) {
				return;
			}

			if (this.selectedIngredients.length === 0) {
				return;
			}

			this.isLoading = true;

			const $button = $('#load-more-btn');
			const originalText = $button.text();
			$button.prop('disabled', true).text(plRecipeSearch.i18n.loading);

			$('#search-status').html(`<span class="loading">${plRecipeSearch.i18n.searching}</span>`);

			const ingredientIds = this.selectedIngredients.map(i => i.id);

			$.ajax({
				url: plRecipeSearch.ajaxurl,
				type: 'POST',
				data: {
					action: 'pl_search_recipes',
					nonce: plRecipeSearch.nonce,
					ingredient_ids: ingredientIds,
					logic: this.logic,
					cursor: this.cursor
				},
				success: (response) => {
					if (response.success) {
						if (append) {
							$('#recipes-container').append(response.data.html);
						} else {
							if (response.data.html && response.data.html.trim() !== '') {
								$('#recipes-container').html(response.data.html);
							} else {
								$('#recipes-container').html(`
									<div class="no-results">
										<span class="search-prompt-icon">😔</span>
										<p>${plRecipeSearch.i18n.noRecipesFound}</p>
									</div>
								`);
							}
						}

						this.cursor = response.data.next_cursor;

						// Update results count
						if (!append && response.data.total_found > 0) {
							$('#results-count').text(`(${response.data.total_found} ${plRecipeSearch.i18n.found})`);
						}

						// Show/hide load more button
						if (response.data.has_more) {
							$button.show().prop('disabled', false).text(originalText);
						} else {
							$button.hide();
						}

						$('#search-status').empty();
					} else {
						$('#search-status').html(`<span class="error">${plRecipeSearch.i18n.errorLoading}</span>`);
					}
				},
				error: () => {
					$('#search-status').html(`<span class="error">${plRecipeSearch.i18n.errorLoading}</span>`);
					$button.prop('disabled', false).text(originalText);
				},
				complete: () => {
					this.isLoading = false;
				}
			});
		}
	}

	// Initialize on document ready
	$(document).ready(function() {
		new RecipeIngredientSearch();

		// Collapse all categories by default except first
		$('.ingredient-category').each(function(index) {
			if (index > 0) {
				$(this).find('.ingredient-list').hide();
			} else {
				$(this).addClass('active');
			}
		});
	});

})(jQuery);
