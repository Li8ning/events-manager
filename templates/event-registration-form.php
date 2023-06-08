<?php
/**
 * Event Registration User Form.
 *
 * @package EventsManager
 */

?>

<form id="event_registration_form" method="post" enctype="multipart/form-data">

	<div class="event_registration_field">
		<label for="event_name"><?php esc_html_e( 'Event Name', 'events-manager' ); ?></label>
		<input type="text" id="event_name" name="event_name" required>
	</div>
	<div class="event_registration_field">
		<label for="event_description"><?php esc_html_e( 'Event Description', 'events-manager' ); ?></label>
		<textarea type="text" id="event_description" name="event_description" required></textarea>
	</div>
	<div class="event_registration_field">
		<label for="event_date"><?php esc_html_e( 'Event Date', 'events-manager' ); ?></label>
		<input type="text" id="event_date" name="event_date" readonly="readonly" required>
	</div>
	<div class="event_registration_field">
		<label for="event_city"><?php esc_html_e( 'Event City', 'events-manager' ); ?></label>
		<?php
			wp_dropdown_categories(
				array(
					'name'       => 'event_city',
					'id'         => 'event_city',
					'taxonomy'   => 'cities',
					'hide_empty' => false,
					'required'   => true,
				)
			);
			?>
	</div>
	<div class="event_registration_field">
		<label for="event_photo"><?php esc_html_e( 'Event Photo', 'events-manager' ); ?></label>
		<input type="file" id="event_photo" name="event_photo" accept="image/*" required>
	</div>

	<?php
	// Add hook to add user defined custom fields.
	do_action( 'event_registration_custom_form_fields' );
	wp_nonce_field( 'save_event_registration_form', 'save_event_registration_form_nonce' );
	?>

	<button type="submit"><?php esc_html_e( 'Submit', 'events-manager' ); ?></button>

</form>
