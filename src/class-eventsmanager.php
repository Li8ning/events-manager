<?php
/**
 *
 * Plugin file to register custom post type and taxonomy.
 *
 * @package EventsManager
 */

namespace EventsManager;

use WP_Query;

/**
 * Main plugin class EventsManager.
 *
 * @since 1.0.0
 */
class EventsManager {

	/**
	 * Initiate plugin name.
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * Initiate plugin version.
	 *
	 * @var string
	 */
	protected $plugin_version;

	/**
	 * Class constructor function.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$plugin_name = 'Events Manager';

		$plugin_version = '1.0.0';

		// Add required action hooks.
		add_action( 'init', array( $this, 'register_events_cpt' ) );
		add_action( 'init', array( $this, 'register_cities_taxonomy' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_user_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_event_date_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_event_date_meta_box' ) );
		add_shortcode( 'event_registration_form', array( $this, 'render_event_registration_form' ) );
		add_action( 'init', array( $this, 'handle_event_registration_form_submission' ) );
		add_shortcode( 'display_events', array( $this, 'display_events' ) );
		add_action( 'wp_ajax_nopriv_get_events_ajax', array( $this, 'get_events_ajax' ) );
		add_action( 'wp_ajax_get_events_ajax', array( $this, 'get_events_ajax' ) );
		add_action( 'wp_ajax_nopriv_load_more_events_ajax', array( $this, 'load_more_events_ajax' ) );
		add_action( 'wp_ajax_load_more_events_ajax', array( $this, 'load_more_events_ajax' ) );
	}

	/**
	 * Enqueue required admin scripts and styles.
	 */
	public function enqueue_admin_scripts() {

		// Enqueue jquery ui datepicker script.
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// Enqueue jquery ui styles.
		wp_enqueue_style( 'jquery-ui-css', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css', array(), '1.13.2' );

		// Enqueue plugin admin script.
		wp_enqueue_script( 'eventmanager-admin-js', plugins_url( 'assets/js/eventmanager-admin.js', dirname( __FILE__ ) ), array(), '1.0.0', true );

	}

	/**
	 * Enqueue required user scripts and styles.
	 */
	public function enqueue_user_scripts() {

		// Enqueue jquery ui datepicker script.
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// Enqueue jquery ui styles.
		wp_enqueue_style( 'jquery-ui-css', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css', array(), '1.13.2' );

		wp_enqueue_script( 'eventmanager-ajax', plugins_url( 'assets/js/eventmanager-ajax.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0', true );
		// the_ajax_script will use to print admin-ajaxurl in custom ajax.js.
		wp_localize_script( 'eventmanager-ajax', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		// Enqueue plugin user script.
		wp_enqueue_script( 'eventmanager-user-js', plugins_url( 'assets/js/eventmanager-user.js', dirname( __FILE__ ) ), array(), '1.0.0', true );
		wp_enqueue_style( 'eventmanager-user-css', plugins_url( 'assets/css/eventmanager-user.css', dirname( __FILE__ ) ), array(), '1.0.0' );

	}

	/**
	 * Plugin activation function.
	 *
	 * @since 1.0.0
	 */
	public function activate() {

		// Flush to make sure CPT and taxonomies are registered successfully.
		flush_rewrite_rules();
	}

	/**
	 * Register Events CPT.
	 *
	 * @since 1.0.0
	 */
	public function register_events_cpt() {

		$labels = array(
			'name'          => __( 'Events', 'events-manager' ),
			'singular_name' => __( 'Event', 'events-manager' ),
		);

		$args = array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => true,
			'rewrite'       => array( 'slug' => 'tasks' ),
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
			'taxonomies'    => array( 'cities' ),
			'menu_position' => 75,
			'menu_icon'     => 'dashicons-video-alt',
		);

		register_post_type( 'events', $args );

	}

	/**
	 * Register Cities taxonomy.
	 *
	 * @since 1.0.0
	 */
	public function register_cities_taxonomy() {

		$labels = array(
			'name'          => __( 'Cities', 'events-manager' ),
			'singular_name' => __( 'City', 'events-manager' ),
		);

		$args = array(
			'labels'       => $labels,
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => false,
			'hierarchical' => true,
		);

		register_taxonomy( 'cities', 'events', $args );

	}

	/**
	 * Register Event Date meta box.
	 */
	public function register_event_date_meta_box() {

		add_meta_box( 'event-date', __( 'Event Date', 'events-manager' ), array( $this, 'render_event_date_meta_box' ), 'events', 'side', 'default' );

	}

	/**
	 * Render event date meta box.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_event_date_meta_box( $post ) {

		wp_nonce_field( 'event_date_meta_box', 'event_date_meta_box_nonce' );

		// Get saved event date.
		$event_date = get_post_meta( $post->ID, 'event-date', true );

		// Render HTML code.
		echo '<label for="event_date">' . esc_html_e( 'Event Date: ', 'events-manager' ) . '</label>';
		echo '<input type="text" id="event_date" name="event_date" value="' . esc_attr( $event_date ) . '"/>';

	}

	/**
	 * Save event date meta box.
	 *
	 * @param WP_Post $post_id The post object.
	 */
	public function save_event_date_meta_box( $post_id ) {

		// Exit if nonce verification fails.
		if ( ! isset( $_POST['event_date_meta_box_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['event_date_meta_box_nonce'] ), 'event_date_meta_box' ) ) {
			return;
		}

		// Do not save when post is being autosaved.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Do not save if user does not have permission to edit post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$event_date = sanitize_text_field( $_POST['event_date'] );
		update_post_meta( $post_id, 'event-date', $event_date );
	}

	/**
	 * Render event registration form.
	 */
	public function render_event_registration_form() {
		ob_start();
		require_once plugin_dir_path( __FILE__ ) . '../templates/event-registration-form.php';
		return ob_get_clean();
	}

	/**
	 * Handle event registration form submission.
	 */
	public function handle_event_registration_form_submission() {

		if ( isset( $_POST['save_event_registration_form_nonce'] ) && wp_verify_nonce( $_POST['save_event_registration_form_nonce'], 'save_event_registration_form' ) ) {
			$event_name        = sanitize_text_field( $_POST['event_name'] );
			$event_description = sanitize_textarea_field( $_POST['event_description'] );
			$event_date        = sanitize_text_field( $_POST['event_date'] );
			$event_city        = esc_html( $_POST['event_city'] );
			$event_photo       = $_FILES['event_photo'];

			if ( empty( $event_photo ) ) {

				wp_die( 'No event photo selected' );
			}

			if ( empty( $event_name ) || empty( $event_description ) || empty( $event_date ) || empty( $event_city ) ) {

				wp_die( 'Please input all form fields' );
			}

			$args = array(
				'post_content' => $event_description,
				'post_title'   => $event_name,
				'post_status'  => 'pending',
				'post_type'    => 'events',
				'tax_input'    => array( 'cities' => $event_city ),
				'meta_input'   => array( 'event-date' => $event_date ),
			);

			$post_id = wp_insert_post( $args );

			require_once ABSPATH . 'wp-admin/includes/file.php';

			$upload = wp_handle_upload(
				$event_photo,
				array( 'test_form' => false )
			);

			if ( ! empty( $upload['error'] ) ) {
				wp_die( $upload['error'] );
			}

			// it is time to add our uploaded image into WordPress media library.
			$attachment_id = wp_insert_attachment(
				array(
					'guid'           => $upload['url'],
					'post_mime_type' => $upload['type'],
					'post_title'     => basename( $upload['file'] ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				),
				$upload['file']
			);

			if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
				wp_die( 'Upload error.' );
			}

			// update medatata, regenerate image sizes.
			require_once ABSPATH . 'wp-admin/includes/image.php';

			wp_update_attachment_metadata(
				$attachment_id,
				wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
			);

			set_post_thumbnail( $post_id, $attachment_id );

			wp_safe_redirect( 'http://localhost/solvative-interview/thank-you/' );
			exit;

		}

	}

	/**
	 * Get all added cities from cities taxonomy.
	 *
	 * @return array
	 */
	public function get_events_cities() {
		$event_cities = get_terms(
			'cities',
			array(
				'hide_empty' => false,
			)
		);
		return $event_cities;
	}

	/**
	 * Render select option for selection of cities.
	 *
	 * @param array $event_cities Array of cities from cities taxonomy.
	 * @return html
	 */
	public function render_event_cities_selection_box( $event_cities ) {

		$html = '<div class="select-city-container">
		<label for="event-city">Choose a city:</label>
		<select name="event-city" id="event-city">
		<option value="all">All</option>';
		foreach ( $event_cities as $city ) {
			$html .= '<option value="' . esc_html( $city->slug ) . '">' . esc_html( $city->name ) . '</option>';
		}
		$html .= '</select>
		</div>';

		return $html;
	}

	/**
	 * Get Events.
	 *
	 * @param array $city_slug Contains city slug.
	 * @return array
	 */
	public function get_events( $city_slug = array() ) {

		if ( empty( $city_slug ) || in_array( 'all', $city_slug ) || 1 === count( $city_slug ) && 'all' === $city_slug[0] ) {
			$tax_query = array(
				'taxonomy' => 'cities',
				'field'    => 'slug',
			);
		}
		else {
			$tax_query = array(
				array(
					'taxonomy' => 'cities',
					'field'    => 'slug',
					'terms'    => $city_slug,
				),
			);
		}

		// Get Event Posts.
		$event_posts_array = new WP_Query(
			array(
				'posts_per_page' => 2,
				'post_type'      => 'events',
				'post_status'    => array( 'publish', 'pending', 'draft' ),
				'tax_query'      => $tax_query,
			)
		);

		return $event_posts_array;
	}

	/**
	 * Render event display table.
	 *
	 * @param array $posts_array Array of retrieved events.
	 * @return html
	 */
	public function render_html_for_events_display( $posts_array ) {
		$html = '<table>
		<tr>
			<th>Event Name</th>
			<th>Event Description</th>
			<th>Event Date</th>
			<th>Event City</th>
			<th>Event Photo</th>
		</tr><tbody id="event-table">';
		$html .= $this->fetch_events_into_html_table( $posts_array );
		$html .= '</tbody></table>
		<input type="hidden" id="totalpages" value="' . $posts_array->max_num_pages . '">
		<button id="more_posts">Load More</button>';

		return $html;
	}

	/**
	 * Display events into html table rows
	 *
	 * @param array $events_array Array of retrieved events
	 * @return html
	 */
	public function fetch_events_into_html_table( $events_array ) {
		$html = '';
		if ( $events_array->have_posts() ) {
			while ( $events_array->have_posts() ) {
				$events_array->the_post();
				$event_cities = implode( ',', wp_get_post_terms( get_the_ID(), 'cities', array( 'fields' => 'names' ) ) );
				$html        .= '<tr>
				<td>' . get_the_title() . '</td>
				<td>' . get_the_content() . '</td>
				<td>' . get_post_meta( get_the_ID(), 'event-date', true ) . '</td>
				<td>' . esc_html( $event_cities ) . '</td>
				<td><img src="' . esc_url( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ) . '" /></td>
				</tr>';
			}
		} else {
			$html .= '<td colspan="5">No Events Found</td>';
		}

		return $html;
	}

	/**
	 * Ajax call to filter events
	 *
	 * @param string $city_name
	 * @return html
	 */
	public function get_events_ajax( $city_name = '' ) {

		// Retrieve the selected city value from the Ajax request
		$city_name = sanitize_text_field($_GET['city_name']);

		if ( empty( $city_name ) || 'all' === $city_name ) {
			$tax_query = array(
				'taxonomy' => 'cities',
				'field'    => 'slug',
			);
		}
		else {
			$tax_query = array(
				array(
					'taxonomy' => 'cities',
					'field'    => 'slug',
					'terms'    => $city_name,
				),
			);
		}

		// Get Event Posts.
		$event_posts_array = new WP_Query(
			array(
				'posts_per_page' => 2,
				'post_type'      => 'events',
				'post_status'    => array( 'publish', 'pending', 'draft' ),
				'tax_query'      => $tax_query,
			)
		);

		$html = $this->fetch_events_into_html_table( $event_posts_array );

		// Return the HTML and total pages as JSON
		$response = array(
			'html' => $html,
			'total_pages' => $event_posts_array->max_num_pages,
		);
		wp_send_json_success( $response );

		wp_die();

	}

	public function load_more_events_ajax() {

		$page = (isset($_POST['pageNumber'])) ? $_POST['pageNumber'] : 0;
		$city_name = (isset($_POST['city_name'])) ? $_POST['city_name'] : 'all';
  
		header("Content-Type: text/html");

		if ( empty( $city_name ) || 'all' === $city_name ) {
			$tax_query = array(
				'taxonomy' => 'cities',
				'field'    => 'slug',
			);
		}
		else {
			$tax_query = array(
				array(
					'taxonomy' => 'cities',
					'field'    => 'slug',
					'terms'    => $city_name,
				),
			);
		}

		// Get Event Posts.
		$event_posts_array = new WP_Query(
			array(
				'suppress_filters' => true,
				'post_type' => 'events',
				'posts_per_page' => 2,
				'paged' => $page,
				'post_status'    => array( 'publish', 'pending', 'draft' ),
				'tax_query'      => $tax_query,
			)
		);

		$html = $this->fetch_events_into_html_table( $event_posts_array );

		wp_reset_postdata();
		die( $html );
	}

	/**
	 * Render event display.
	 *
	 * @param array $atts Shortcode arguments.
	 */
	public function display_events( $atts ) {

		// Parse shortcode attributes.
		$city_args = shortcode_atts(
			array(
				'city' => 'all',
			),
			$atts
		);
		$cities = explode( ',', $city_args['city'] );
		ob_start();
		require_once plugin_dir_path( __FILE__ ) . '../templates/event-display.php';
		return ob_get_clean();
	}

}
