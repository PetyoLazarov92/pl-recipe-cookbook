<?php
/**
 * Database Management Class
 *
 * Handles creation and management of custom database tables.
 *
 * @package PLRecipeCookbook
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PL_Recipe_Database Class
 */
class PL_Recipe_Database {

	/**
	 * Create all custom tables
	 *
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix;

		// Table 1: Ingredient Categories.
		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS {$prefix}pl_ingredient_categories (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(100) NOT NULL,
				name_en VARCHAR(100) DEFAULT NULL,
				slug VARCHAR(100) NOT NULL UNIQUE,
				description TEXT DEFAULT NULL,
				display_order INT DEFAULT 0,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				KEY idx_order (display_order)
			) $charset_collate"
		);

		// Table 2: Ingredients.
		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS {$prefix}pl_ingredients (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				category_id BIGINT UNSIGNED NOT NULL,
				name VARCHAR(150) NOT NULL,
				name_en VARCHAR(150) DEFAULT NULL,
				slug VARCHAR(150) NOT NULL UNIQUE,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_category (category_id),
				KEY idx_name (name)
			) $charset_collate"
		);

		// Table 3: Recipe-Ingredients Mapping.
		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS {$prefix}pl_recipe_ingredients (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				recipe_id BIGINT UNSIGNED NOT NULL,
				ingredient_id BIGINT UNSIGNED NOT NULL,
				quantity VARCHAR(50) DEFAULT NULL,
				unit VARCHAR(20) DEFAULT NULL,
				raw_text VARCHAR(255) DEFAULT NULL,
				section VARCHAR(100) DEFAULT NULL,
				notes TEXT DEFAULT NULL,
				display_order INT DEFAULT 0,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				UNIQUE KEY idx_unique_pair (recipe_id, ingredient_id),
				KEY idx_search (ingredient_id, recipe_id),
				KEY idx_recipe (recipe_id)
			) $charset_collate"
		);

		// Add foreign keys if they don't exist (with unique names for multisite).
		$fk_name1 = str_replace( array( '-', '_' ), '', $prefix ) . 'fk_ing_cat';
		$fk_name2 = str_replace( array( '-', '_' ), '', $prefix ) . 'fk_rec_ing';

		$constraints = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT CONSTRAINT_NAME 
				FROM information_schema.TABLE_CONSTRAINTS 
				WHERE TABLE_SCHEMA = DATABASE() 
				AND TABLE_NAME = %s 
				AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
				$prefix . 'pl_ingredients'
			)
		);

		if ( empty( $constraints ) ) {
			$wpdb->query(
				"ALTER TABLE {$prefix}pl_ingredients 
				ADD CONSTRAINT {$fk_name1}
				FOREIGN KEY (category_id) 
				REFERENCES {$prefix}pl_ingredient_categories(id) 
				ON DELETE CASCADE"
			);
		}

		$constraints2 = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT CONSTRAINT_NAME 
				FROM information_schema.TABLE_CONSTRAINTS 
				WHERE TABLE_SCHEMA = DATABASE() 
				AND TABLE_NAME = %s 
				AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
				$prefix . 'pl_recipe_ingredients'
			)
		);

		if ( empty( $constraints2 ) ) {
			$wpdb->query(
				"ALTER TABLE {$prefix}pl_recipe_ingredients 
				ADD CONSTRAINT {$fk_name2}
				FOREIGN KEY (ingredient_id) 
				REFERENCES {$prefix}pl_ingredients(id) 
				ON DELETE CASCADE"
			);
		}

		// Add notes and updated_at columns to existing tables if they don't exist.
		self::add_missing_columns();
	}

	/**
	 * Add missing columns to existing tables
	 *
	 * @return void
	 */
	private static function add_missing_columns() {
		global $wpdb;
		$prefix = $wpdb->prefix;

		// Check if notes column exists.
		$notes_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COLUMN_NAME FROM information_schema.COLUMNS 
				WHERE TABLE_SCHEMA = DATABASE() 
				AND TABLE_NAME = %s 
				AND COLUMN_NAME = 'notes'",
				$prefix . 'pl_recipe_ingredients'
			)
		);

		if ( empty( $notes_exists ) ) {
			$wpdb->query(
				"ALTER TABLE {$prefix}pl_recipe_ingredients 
				ADD COLUMN notes TEXT DEFAULT NULL AFTER section"
			);
		}

		// Check if updated_at column exists.
		$updated_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COLUMN_NAME FROM information_schema.COLUMNS 
				WHERE TABLE_SCHEMA = DATABASE() 
				AND TABLE_NAME = %s 
				AND COLUMN_NAME = 'updated_at'",
				$prefix . 'pl_recipe_ingredients'
			)
		);

		if ( empty( $updated_exists ) ) {
			$wpdb->query(
				"ALTER TABLE {$prefix}pl_recipe_ingredients 
				ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
			);
		}
	}

	/**
	 * Import seed data from SQL file
	 *
	 * @return bool
	 */
	public static function import_seed_data() {
		global $wpdb;

		// Check if already seeded.
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}pl_ingredient_categories" );
		if ( $count > 0 ) {
			return false; // Already seeded.
		}

		$sql_file = plugin_dir_path( dirname( __FILE__ ) ) . 'data/seed-data.sql';
		
		if ( ! file_exists( $sql_file ) ) {
			return false;
		}

		$sql = file_get_contents( $sql_file );
		
		// Replace placeholder with actual prefix.
		$sql = str_replace( '{prefix}', $wpdb->prefix, $sql );

		// Split by semicolon and execute each query.
		$queries = explode( ';', $sql );
		
		foreach ( $queries as $query ) {
			$query = trim( $query );
			if ( ! empty( $query ) ) {
				$wpdb->query( $query );
			}
		}

		return true;
	}

	/**
	 * Get ingredients for a recipe from custom tables
	 *
	 * @param int $recipe_id Recipe post ID.
	 * @return array Array of ingredients with details.
	 */
	public static function get_recipe_ingredients( $recipe_id ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
					ri.id,
					ri.quantity,
					ri.unit,
					ri.raw_text,
					ri.section,
					ri.notes,
					ri.display_order,
					i.name as ingredient_name,
					i.name_en as ingredient_name_en,
					c.name as category_name,
					c.name_en as category_name_en
				FROM {$wpdb->prefix}pl_recipe_ingredients ri
				LEFT JOIN {$wpdb->prefix}pl_ingredients i ON ri.ingredient_id = i.id
				LEFT JOIN {$wpdb->prefix}pl_ingredient_categories c ON i.category_id = c.id
				WHERE ri.recipe_id = %d
				ORDER BY ri.section, ri.display_order, ri.id",
				$recipe_id
			),
			ARRAY_A
		);

		return $results ? $results : array();
	}

	/**
	 * Drop all custom tables (for uninstall)
	 *
	 * @return void
	 */
	public static function drop_tables() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pl_recipe_ingredients" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pl_ingredients" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pl_ingredient_categories" );
	}
}
