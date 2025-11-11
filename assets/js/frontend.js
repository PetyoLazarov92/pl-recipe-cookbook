/**
 * Frontend JavaScript for Recipe Cookbook
 *
 * @package PLRecipeCookbook
 */

(function() {
	'use strict';

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

	// Initialize event listeners on DOM ready
	document.addEventListener('DOMContentLoaded', function() {
		// Section toggle listeners
		document.querySelectorAll('.recipe-section-header').forEach(function(header) {
			header.addEventListener('click', function() {
				toggleSection(this);
			});
		});

		// Shopping mode checkbox listener
		const shoppingCheckbox = document.getElementById('shopping-mode-checkbox');
		if (shoppingCheckbox) {
			shoppingCheckbox.addEventListener('change', toggleShoppingMode);
		}

		// Ingredient checkbox listeners
		document.querySelectorAll('.ingredient-checkbox').forEach(function(checkbox) {
			checkbox.addEventListener('change', function() {
				toggleIngredient(this);
			});
		});
	});

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

	// Cooking Helper Widget - Collapsible functionality
	document.addEventListener('DOMContentLoaded', function() {
		const toggleButtons = document.querySelectorAll('.cooking-helper-toggle');
		
		toggleButtons.forEach(function(button) {
			button.addEventListener('click', function() {
				const collapsible = this.closest('.cooking-helper-collapsible');
				const list = collapsible.querySelector('.cooking-helper-list');
				const link = collapsible.querySelector('.cooking-helper-link');
				const expandText = this.querySelector('.toggle-text-expand');
				const collapseText = this.querySelector('.toggle-text-collapse');
				const isExpanded = this.getAttribute('aria-expanded') === 'true';
				
				if (isExpanded) {
					// Collapse
					list.classList.remove('expanded');
					this.setAttribute('aria-expanded', 'false');
					expandText.style.display = '';
					collapseText.style.display = 'none';
					if (link) {
						link.classList.remove('show');
					}
				} else {
					// Expand
					list.classList.add('expanded');
					this.setAttribute('aria-expanded', 'true');
					expandText.style.display = 'none';
					collapseText.style.display = '';
					if (link) {
						link.classList.add('show');
					}
				}
			});
		});

		// Sidebar fade animation based on article scroll progress
		const sidebar = document.querySelector('.recipe-sidebar-widgets');
		const article = document.querySelector('.recipe-single');
		
		if (sidebar && article) {
			// Store original height
			let originalHeight = null;
			
			function updateSidebarOpacity() {
				const articleRect = article.getBoundingClientRect();
				const articleMiddle = articleRect.top + (articleRect.height / 2);
				const viewportHeight = window.innerHeight;
				
				// Get original height on first run
				if (originalHeight === null) {
					originalHeight = sidebar.offsetHeight;
				}
				
				// Start fading when article middle reaches viewport middle
				if (articleMiddle < viewportHeight / 2) {
					// Calculate how far past middle we are
					const distancePastMiddle = (viewportHeight / 2) - articleMiddle;
					const fadeDistance = articleRect.height / 2; // Fade over remaining half of article
					
					// Calculate opacity (1 at middle, 0 at end)
					let opacity = 1 - (distancePastMiddle / fadeDistance);
					opacity = Math.max(0, Math.min(1, opacity)); // Clamp between 0 and 1
					
					// Apply opacity and height
					sidebar.style.opacity = opacity;
					sidebar.style.maxHeight = (originalHeight * opacity) + 'px';
					sidebar.style.overflow = 'hidden';
				} else {
					sidebar.style.opacity = '1';
					sidebar.style.maxHeight = '';
					sidebar.style.overflow = '';
				}
			}
			
			// Update on scroll
			let sidebarTicking = false;
			window.addEventListener('scroll', function() {
				if (!sidebarTicking) {
					window.requestAnimationFrame(function() {
						updateSidebarOpacity();
						sidebarTicking = false;
					});
					sidebarTicking = true;
				}
			});
			
			// Initial check
			updateSidebarOpacity();
		}
	});

})();
