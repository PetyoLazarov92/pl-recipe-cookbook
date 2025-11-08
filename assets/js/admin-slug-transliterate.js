/**
 * Recipe Slug Transliteration JS
 *
 * @package PLRecipeCookbook
 */

(function($) {
	'use strict';

	// Bulgarian to Latin transliteration map
	const translitMap = {
		'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E',
		'Ж': 'Zh', 'З': 'Z', 'И': 'I', 'Й': 'Y', 'К': 'K', 'Л': 'L',
		'М': 'M', 'Н': 'N', 'О': 'O', 'П': 'P', 'Р': 'R', 'С': 'S',
		'Т': 'T', 'У': 'U', 'Ф': 'F', 'Х': 'H', 'Ц': 'Ts', 'Ч': 'Ch',
		'Ш': 'Sh', 'Щ': 'Sht', 'Ъ': 'A', 'Ь': 'Y', 'Ю': 'Yu', 'Я': 'Ya',
		'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e',
		'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l',
		'м': 'm', 'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's',
		'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch',
		'ш': 'sh', 'щ': 'sht', 'ъ': 'a', 'ь': 'y', 'ю': 'yu', 'я': 'ya'
	};

	/**
	 * Transliterate string to Latin
	 */
	function transliterate(str) {
		let result = '';
		for (let i = 0; i < str.length; i++) {
			const char = str[i];
			result += translitMap[char] || char;
		}
		return result;
	}

	/**
	 * Create slug from title
	 */
	function createSlug(title) {
		// Transliterate
		let slug = transliterate(title);
		
		// Convert to lowercase
		slug = slug.toLowerCase();
		
		// Replace non-alphanumeric with hyphens
		slug = slug.replace(/[^a-z0-9]+/g, '-');
		
		// Remove leading/trailing hyphens
		slug = slug.replace(/^-+|-+$/g, '');
		
		// Remove consecutive hyphens
		slug = slug.replace(/-+/g, '-');
		
		return slug;
	}

	// Wait for DOM ready
	$(document).ready(function() {
		// Check if we're on recipe post edit screen
		const $body = $('body');
		if (!$body.hasClass('post-type-pl_recipe')) {
			return;
		}

		const $titleInput = $('#title');
		
		if ($titleInput.length === 0) {
			return;
		}

		// Override WordPress slug generation on title change
		$titleInput.on('blur', function() {
			const title = $(this).val();
			
			if (title.length > 0 && /[^\x00-\x7F]/.test(title)) {
				const slug = createSlug(title);
				
				// Update the slug via AJAX (WordPress way)
				const data = {
					action: 'sample-permalink',
					post_id: $('#post_ID').val(),
					new_title: title,
					new_slug: slug,
					samplepermalinknonce: $('#samplepermalinknonce').val()
				};
				
				$.post(ajaxurl, data, function(response) {
					if (response && response.data) {
						$('#edit-slug-box').html(response.data);
					}
				});
			}
		});
	});

})(jQuery);
