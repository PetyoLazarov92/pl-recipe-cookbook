<?php
/**
 * Recipe Widgets
 *
 * @package PLRecipeCookbook
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PL_Recipe_Widgets
 *
 * Handles widget registration and sidebar areas for recipe pages.
 */
class PL_Recipe_Widgets {

	/**
	 * Initialize widgets
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'widgets_init', array( __CLASS__, 'register_sidebars' ) );
		add_action( 'widgets_init', array( __CLASS__, 'register_widgets' ) );
	}

	/**
	 * Register sidebar areas
	 *
	 * @return void
	 */
	public static function register_sidebars() {
		register_sidebar(
			array(
				'name'          => __( 'Recipe Sidebar', 'pl-recipe-cookbook' ),
				'id'            => 'recipe-sidebar',
				'description'   => __( 'Widget area displayed below the table of contents on single recipe pages.', 'pl-recipe-cookbook' ),
				'before_widget' => '<div id="%1$s" class="recipe-widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="recipe-widget-title">',
				'after_title'   => '</h3>',
			)
		);
	}

	/**
	 * Register custom widgets
	 *
	 * @return void
	 */
	public static function register_widgets() {
		register_widget( 'PL_Recipe_Cooking_Helper_Widget' );
	}
}

/**
 * Class PL_Recipe_Cooking_Helper_Widget
 *
 * Widget displaying cooking measurement references and abbreviations.
 */
class PL_Recipe_Cooking_Helper_Widget extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			'pl_recipe_cooking_helper',
			__( 'Cooking Helper', 'pl-recipe-cookbook' ),
			array(
				'description' => __( 'Displays a reference guide for cooking measurements and abbreviations.', 'pl-recipe-cookbook' ),
			)
		);
	}

	/**
	 * Front-end display of widget
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];

		$title       = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Cooking Helper', 'pl-recipe-cookbook' );
		$description = ! empty( $instance['description'] ) ? $instance['description'] : '';
		$full_list_page = ! empty( $instance['full_list_page'] ) ? absint( $instance['full_list_page'] ) : 0;

		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		// Get measurement abbreviations.
		$measurements = $this->get_measurements();

		echo '<div class="cooking-helper-content">';

		if ( ! empty( $description ) ) {
			echo '<p class="cooking-helper-intro">' . esc_html( $description ) . '</p>';
		}

		echo '<div class="cooking-helper-collapsible">';
		echo '<ul class="cooking-helper-list">';

		$count = 0;
		foreach ( $measurements as $measurement ) {
			$collapsed_class = $count > 0 ? ' collapsed-item' : '';
			echo '<li class="' . esc_attr( $collapsed_class ) . '">';
			echo '<span class="measurement-abbr">' . esc_html( $measurement['abbr'] ) . '</span>';
			echo '<span class="measurement-full">' . esc_html( $measurement['full'] ) . '</span>';
			echo '</li>';
			$count++;
		}

		echo '</ul>';

		if ( $full_list_page ) {
			$page_link = get_permalink( $full_list_page );
			if ( $page_link ) {
				echo '<a href="' . esc_url( $page_link ) . '" class="cooking-helper-link collapsed-item">' . esc_html__( 'Full List', 'pl-recipe-cookbook' ) . '</a>';
			}
		}

		echo '<button type="button" class="cooking-helper-toggle" aria-expanded="false">';
		echo '<span class="toggle-text-expand">' . esc_html__( 'Show More', 'pl-recipe-cookbook' ) . '</span>';
		echo '<span class="toggle-text-collapse" style="display:none;">' . esc_html__( 'Show Less', 'pl-recipe-cookbook' ) . '</span>';
		echo '</button>';

		echo '</div>';

		echo '</div>';

		echo $args['after_widget'];
	}

	/**
	 * Get measurement abbreviations and their full names
	 *
	 * @return array Array of measurements with 'abbr' and 'full' keys.
	 */
	private function get_measurements() {
		return array(
			array(
				/* translators: Tea cup abbreviation */
				'abbr' => __( 'c.c.', 'pl-recipe-cookbook' ),
				/* translators: Tea cup full name */
				'full' => __( 'tea cup (250 ml)', 'pl-recipe-cookbook' ),
			),
			array(
				/* translators: Tablespoon abbreviation */
				'abbr' => __( 'tbsp', 'pl-recipe-cookbook' ),
				/* translators: Tablespoon full name */
				'full' => __( 'tablespoon (15 ml)', 'pl-recipe-cookbook' ),
			),
			array(
				/* translators: Teaspoon abbreviation */
				'abbr' => __( 'tsp', 'pl-recipe-cookbook' ),
				/* translators: Teaspoon full name */
				'full' => __( 'teaspoon (5 ml)', 'pl-recipe-cookbook' ),
			),
			array(
				/* translators: Pinch abbreviation */
				'abbr' => _x( 'pinch', 'cooking measurement abbr', 'pl-recipe-cookbook' ),
				/* translators: Pinch full name */
				'full' => _x( 'pinch', 'cooking measurement name', 'pl-recipe-cookbook' ),
			),
			array(
				/* translators: Piece abbreviation */
				'abbr' => __( 'pc', 'pl-recipe-cookbook' ),
				/* translators: Piece full name */
				'full' => __( 'piece', 'pl-recipe-cookbook' ),
			),
			array(
				/* translators: Kilogram abbreviation */
				'abbr' => __( 'kg', 'pl-recipe-cookbook' ),
				/* translators: Kilogram full name */
				'full' => __( 'kilogram', 'pl-recipe-cookbook' ),
			),
			array(
				/* translators: Gram abbreviation */
				'abbr' => __( 'g', 'pl-recipe-cookbook' ),
				/* translators: Gram full name */
				'full' => __( 'gram', 'pl-recipe-cookbook' ),
			),
		);
	}

	/**
	 * Back-end widget form
	 *
	 * @param array $instance Previously saved values from database.
	 * @return void
	 */
	public function form( $instance ) {
		$title          = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Cooking Helper', 'pl-recipe-cookbook' );
		$description    = ! empty( $instance['description'] ) ? $instance['description'] : '';
		$full_list_page = ! empty( $instance['full_list_page'] ) ? absint( $instance['full_list_page'] ) : 0;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'pl-recipe-cookbook' ); ?>
			</label>
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
				type="text" 
				value="<?php echo esc_attr( $title ); ?>"
			>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>">
				<?php esc_html_e( 'Description:', 'pl-recipe-cookbook' ); ?>
			</label>
			<textarea 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>" 
				rows="3"
			><?php echo esc_textarea( $description ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'full_list_page' ) ); ?>">
				<?php esc_html_e( 'Full List Page:', 'pl-recipe-cookbook' ); ?>
			</label>
			<?php
			wp_dropdown_pages(
				array(
					'name'              => $this->get_field_name( 'full_list_page' ),
					'id'                => $this->get_field_id( 'full_list_page' ),
					'selected'          => $full_list_page,
					'show_option_none'  => __( '— Select —', 'pl-recipe-cookbook' ),
					'option_none_value' => 0,
					'class'             => 'widefat',
				)
			);
			?>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                    = array();
		$instance['title']           = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['description']     = ( ! empty( $new_instance['description'] ) ) ? sanitize_textarea_field( $new_instance['description'] ) : '';
		$instance['full_list_page']  = ( ! empty( $new_instance['full_list_page'] ) ) ? absint( $new_instance['full_list_page'] ) : 0;
		return $instance;
	}
}
