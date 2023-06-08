<?php
/**
 * Render Event Display Code.
 *
 * @package EventsManager
 */

// Include the class file.
require_once plugin_dir_path( __FILE__ ) . '../src/class-eventsmanager.php';
// Check if the EventsManager class is defined.
if ( class_exists( 'EventsManager\EventsManager' ) ) {

	// Create an instance of the EventsManager class.
	$events_manager = new EventsManager\EventsManager();

	// Call the get_events from EventsManager.
	$posts_array = $events_manager->get_events();

	// Call the get_events_cities from EventsManager.
	$event_cities = $events_manager->get_events_cities();

	// Render event filter by city select box.
	echo wp_kses(
		$events_manager->render_event_cities_selection_box( $event_cities ),
		array(
			'div'    => array(),
			'label'  => array(
				'for' => array(),
			),
			'select' => array(
				'name' => array(),
				'id'   => array(),
			),
			'option' => array(
				'value' => array(),
			),
		)
	);

	// Render table to display events.
	echo wp_kses(
		$events_manager->render_html_for_events_display( $posts_array ),
		array(
			'table' => array(),
			'th'    => array(),
			'td'    => array(),
			'tr'    => array(),
			'img'   => array(
				'src' => array(),
			),
			'input' => array(
				'type'  => array(),
				'id'    => array(),
				'value' => array(),
			),
			'div'   => array(
				'id' => array(),
			),
		)
	);
}
wp_reset_postdata();
