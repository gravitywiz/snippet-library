<?php
/**
 * Gravity Perks // Nested Forms // Delay Child Notifications for Parent Payment
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
class GW_GPNF_Delay_Child_Notifications {
	public $form_ids;

	public function __construct( $args ) {
		$this->form_ids = $args['form_ids'];

		add_filter( 'gpnf_should_send_notification', function( $should_send_notification, $notification, $context, $parent_form, $nested_form_field, $entry, $child_form ) {

			if ( $context === 'parent' ) {
				$parent_entry             = GFAPI::get_entry( rgar( $entry, 'gpnf_entry_parent' ) );
				$should_send_notification = in_array( rgar( $parent_entry, 'payment_status' ), array( 'Paid', 'Active' ), true );
			}

			return $should_send_notification;
		}, 10, 7 );

		add_action( 'gform_post_payment_completed', function( $entry ) {
			if ( is_callable( 'gpnf_notification_processing' ) ) {
				gpnf_notification_processing()->maybe_send_child_notifications( $entry, GFAPI::get_form( $entry['form_id'] ) );
			}
		} );

		remove_filter( 'gform_entry_post_save', array( gpnf_notification_processing(), 'maybe_send_child_notifications' ), 11 );
		add_filter( 'gform_entry_post_save', array( $this, 'gform_entry_post_save' ), 11, 2 );
	}

	public function gform_entry_post_save( $entry, $form ) {
		/**
		 * Do not send notification for form ID 100 on the normal entry save hook. This form is expected to send
		 * notifications using the gform_post_payment_completed action.
		 */
		if ( in_array( rgar( $form, 'id' ), $this->form_ids, true ) ) {
				return $entry;
		}
				return gpnf_notification_processing()->maybe_send_child_notifications( $entry, $form );
	}
}

// Configuration
new GW_GPNF_Delay_Child_Notifications( array(
	'form_ids' => array( 3 ), // Add all parent form IDs to this array
) );
