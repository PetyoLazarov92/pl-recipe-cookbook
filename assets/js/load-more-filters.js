/**
 * Load More Filters AJAX
 *
 * @package PLRecipeCookbook
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Load more categories
		$('.load-more-categories').on('click', function(e) {
			e.preventDefault();
			var button = $(this);
			var offset = button.data('offset');
			var currentTerm = button.data('current-term');
			var container = button.closest('.filter-group').find('.filter-pills');

			button.prop('disabled', true).text(button.data('loading-text'));

			$.ajax({
				url: plRecipeAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'pl_load_more_terms',
					taxonomy: 'pl_recipe_cat',
					offset: offset,
					current_term_id: currentTerm,
					nonce: plRecipeAjax.nonce
				},
				success: function(response) {
					if (response.success && response.data.html) {
						// Insert new items before the button
						button.before(response.data.html);
						
						if (response.data.has_more) {
							button.data('offset', response.data.new_offset);
							button.prop('disabled', false).text(button.data('original-text'));
						} else {
							button.fadeOut();
						}
					} else {
						button.fadeOut();
					}
				},
				error: function() {
					button.prop('disabled', false).text(button.data('original-text'));
				}
			});
		});

		// Load more tags
		$('.load-more-tags').on('click', function(e) {
			e.preventDefault();
			var button = $(this);
			var offset = button.data('offset');
			var currentTerm = button.data('current-term');
			var container = button.closest('.filter-group').find('.filter-pills');

			button.prop('disabled', true).text(button.data('loading-text'));

			$.ajax({
				url: plRecipeAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'pl_load_more_terms',
					taxonomy: 'pl_recipe_tag',
					offset: offset,
					current_term_id: currentTerm,
					nonce: plRecipeAjax.nonce
				},
				success: function(response) {
					if (response.success && response.data.html) {
						// Insert new items before the button
						button.before(response.data.html);
						
						if (response.data.has_more) {
							button.data('offset', response.data.new_offset);
							button.prop('disabled', false).text(button.data('original-text'));
						} else {
							button.fadeOut();
						}
					} else {
						button.fadeOut();
					}
				},
				error: function() {
					button.prop('disabled', false).text(button.data('original-text'));
				}
			});
		});
	});

})(jQuery);
