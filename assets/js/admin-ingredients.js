/**
 * Admin Ingredients Management JS
 */
(function($) {
	'use strict';

	let ingredientCounter = 0;

	$(document).ready(function() {
		// Initialize counter based on existing rows.
		ingredientCounter = $('#pl-ingredients-list tr:not(.pl-no-ingredients)').length;

		// Category selection change.
		$('#pl-category-select').on('change', function() {
			const categoryId = $(this).val();
			
			if (!categoryId) {
				$('#pl-ingredient-select').prop('disabled', true).html('<option value="">-- Select category first --</option>');
				$('#pl-add-ingredient').prop('disabled', true);
				return;
			}

			// Load ingredients for selected category.
			$('#pl-ingredient-select').prop('disabled', true).html('<option value="">Loading...</option>');
			
			$.ajax({
				url: plRecipeAdmin.ajaxurl,
				type: 'POST',
				data: {
					action: 'pl_get_ingredients_by_category',
					nonce: plRecipeAdmin.nonce,
					category_id: categoryId
				},
				success: function(response) {
					if (response.success && response.data.ingredients) {
						let options = '<option value="">-- Select ingredient --</option>';
						
						response.data.ingredients.forEach(function(ingredient) {
							options += `<option value="${ingredient.id}">${ingredient.name}</option>`;
						});
						
						$('#pl-ingredient-select').html(options).prop('disabled', false);
						$('#pl-add-ingredient').prop('disabled', false);
					}
				},
				error: function() {
					$('#pl-ingredient-select').html('<option value="">Error loading ingredients</option>');
				}
			});
		});

		// Add ingredient button.
		$('#pl-add-ingredient').on('click', function() {
			const ingredientId = $('#pl-ingredient-select').val();
			const ingredientName = $('#pl-ingredient-select option:selected').text();
			
			if (!ingredientId || ingredientId === '') {
				alert('Please select an ingredient');
				return;
			}

			// Check if already added.
			if ($(`#pl-ingredients-list tr[data-ingredient-id="${ingredientId}"]`).length > 0) {
				alert('This ingredient is already added');
				return;
			}

			// Remove "no ingredients" message if present.
			$('.pl-no-ingredients').remove();

			// Add new row.
			const row = `
				<tr data-ingredient-id="${ingredientId}">
					<td>
						<span class="pl-order-handle" style="cursor: move;">☰</span>
						<input type="hidden" name="pl_ingredients[${ingredientCounter}][id]" value="${ingredientId}">
						<input type="hidden" name="pl_ingredients[${ingredientCounter}][order]" value="${ingredientCounter}" class="pl-order-input">
					</td>
					<td><strong>${ingredientName}</strong></td>
					<td>
						<input type="text" name="pl_ingredients[${ingredientCounter}][quantity]" 
							   style="width: 100%;" placeholder="500">
					</td>
					<td>
						<input type="text" name="pl_ingredients[${ingredientCounter}][unit]" 
							   style="width: 100%;" placeholder="г">
					</td>
					<td>
						<input type="text" name="pl_ingredients[${ingredientCounter}][section]" 
							   style="width: 100%;" placeholder="Optional">
					</td>
					<td>
						<button type="button" class="button pl-remove-ingredient">Remove</button>
					</td>
				</tr>
			`;

			$('#pl-ingredients-list').append(row);
			ingredientCounter++;

			// Reset selects.
			$('#pl-category-select').val('');
			$('#pl-ingredient-select').html('<option value="">-- Select category first --</option>').prop('disabled', true);
			$('#pl-add-ingredient').prop('disabled', true);
		});

		// Remove ingredient.
		$(document).on('click', '.pl-remove-ingredient', function() {
			$(this).closest('tr').remove();

			// Show "no ingredients" message if list is empty.
			if ($('#pl-ingredients-list tr').length === 0) {
				$('#pl-ingredients-list').html(`
					<tr class="pl-no-ingredients">
						<td colspan="6" style="text-align: center; color: #666;">
							No ingredients added yet.
						</td>
					</tr>
				`);
			}

			// Update order numbers.
			updateOrderNumbers();
		});

		// Make list sortable.
		$('#pl-ingredients-list').sortable({
			handle: '.pl-order-handle',
			placeholder: 'ui-state-highlight',
			update: function() {
				updateOrderNumbers();
			}
		});

		/**
		 * Update order numbers after sorting or removal.
		 */
		function updateOrderNumbers() {
			$('#pl-ingredients-list tr:not(.pl-no-ingredients)').each(function(index) {
				$(this).find('.pl-order-input').val(index);
			});
		}
	});

})(jQuery);
