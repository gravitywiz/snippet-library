<?php
/**
 * Gravity Perks // Notification Scheduler // Pending Activation Reminders
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 *
 * Schedule reminders for users who signed up but never activated their accounts.
 *
 * Requirements:
 * 
 * 1. [User Registration](https://www.gravityforms.com/add-ons/user-registration/)
 * 2. [Notification Scheduler](https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/)
 * 3. [GW Conditional Logic: Entry Meta](https://github.com/gravitywiz/snippet-library/blob/master/gravity-forms/gw-conditional-logic-entry-meta.php)
 *
 * Instructions:
 * 
 * 1. Ensure that all required plugins have been installed and activated.
 * 2. Setup a User Registration feed to create a new user with "User Activation" enabled.
 * 3. Setup a notification triggered by the "User is pending activation" event.
 * 4. Configure your desired reminder schedule for the notification.
 * 5. Configure conditional logic for this notification so that it only sends if "Pending Activation" is "Yes".
 *
 * Plugin Name:  GPNS Pending Activation Reminders
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 * Description:  Schedule reminderes for users who signed up but never activated their account.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gform_entry_meta', function ( $entry_meta, $form_id ) {

	if ( ! is_callable( 'gf_user_registration' ) || ! gf_user_registration()->has_feed_type( 'create', array( 'id' => $form_id ) ) ) {
		return $entry_meta;
	}

	$entry_meta['gw_gfur_is_pending_activation'] = array(
		'label'                      => 'Pending Activation',
		'is_numeric'                 => false,
		'update_entry_meta_callback' => function( $key, $entry, $form ) {
			return false;
		},
		'is_default_column'          => false,
		'filter'                     => array(
			'key'       => 'gw_gfur_is_pending_activation',
			'text'      => 'Pending Activation',
			'operators' => array(
				'is',
				'isnot',
			),
			'choices' => array(
				array(
					'text' => 'Yes',
					'value' => 1,
					'isSelected' => false,
				),
				array(
					'text' => 'No',
					'value' => '',
					'isSelected' => false,
				),
			),
		),
	);

	return $entry_meta;
}, 10, 2 );

add_filter( 'gform_field_filters', function( $field_filters, $form ) {
	foreach ( $field_filters as &$field_filter ) {
		if ( $field_filter['key'] !== 'gw_gfur_is_pending_activation' ) {
			continue;
		}
		$field_filter['values'] = array(
			array(
				'text' => 'Yes',
				'value' => 1,
				'isSelected' => false,
			),
			array(
				'text' => 'No',
				'value' => '',
				'isSelected' => false,
			),
		);
	}
	return $field_filters;
}, 10, 2 );

add_action( 'after_signup_user', function( $user, $user_email, $key, $meta ) {
	$entry_id = rgar( $meta, 'entry_id' );
	if ( ! empty( $entry_id ) ) {
		gform_update_meta( $entry_id, 'gw_gfur_is_pending_activation', true );
	}
}, 10, 4 );

add_action( 'wpmu_activate_user', 'gw_gfur_set_pending_activation_meta', 10, 3 );
add_action( 'gform_activate_user', 'gw_gfur_set_pending_activation_meta', 10, 3 );
function gw_gfur_set_pending_activation_meta( $user_id, $password, $meta ) {
	$entry_id = rgar( $meta, 'entry_id' );
	if ( ! empty( $entry_id ) ) {
		gform_update_meta( $entry_id, 'gw_gfur_is_pending_activation', '' );
	}
}

add_filter( 'gwclem_runtime_entry_meta_keys', function( $keys ) {
	$keys[] = 'gw_gfur_is_pending_activation';
	return $keys;
} );
