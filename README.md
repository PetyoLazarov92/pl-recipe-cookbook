# PL Recipe Cookbook

A WordPress plugin for managing cooking recipes with a beautiful, modern interface.

## Features

- Custom post type for recipes with custom taxonomies (categories and tags)
- Beautiful single recipe template with:
  - Responsive image display
  - Recipe metadata (prep time, cook time, servings, difficulty)
  - Collapsible ingredient sections with optional section grouping
  - Numbered instruction steps with collapsible sections
  - Shopping mode with checkboxes for ingredients
  - Sticky table of contents sidebar
  - Print-friendly layout
- Archive pages with:
  - Category and tag filters
  - Search functionality
  - Load more functionality for filters
  - Default fallback image for recipes without featured images
- Fully translated to Bulgarian (bg_BG)
- Automatic slug transliteration (Cyrillic to Latin)
- Responsive design optimized for mobile, tablet, and desktop (including 2K displays)

## Installation

1. Upload the `pl-recipe-cookbook` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Start creating recipes under the "Рецепти" menu item

## Usage

### Creating a Recipe

1. Go to "Рецепти" → "Добави нова"
2. Enter recipe title and description
3. Add recipe metadata in the right sidebar:
   - Prep time (minutes)
   - Cook time (minutes)
   - Servings
   - Difficulty level
4. Add ingredients (one per line, optionally grouped with `[Section Name]` headers)
5. Add instructions (one per line, optionally grouped with `[Section Name]` headers)
6. Set a featured image
7. Assign categories and tags

### Ingredient Sections

To group ingredients into sections, use square brackets:

```
[За тестото]
500 г брашно
300 мл вода
10 г сол

[За плънката]
200 г сирене
3 яйца
```

### Instruction Sections

Instructions can also be grouped with the same format:

```
[Приготвяне на тестото]
Смесете брашното със солта
Добавете водата постепенно

[Печене]
Загрейте фурната до 200°C
Печете 25 минути
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Translations

The plugin is fully translated to Bulgarian. The translation files are located in the `languages/` directory.

## Author

Created for plazarov.com

## License

GPL v2 or later

## Changelog

### 1.0.0
- Initial release
- Custom recipe post type
- Beautiful single and archive templates
- Bulgarian translation
- Automatic slug transliteration
