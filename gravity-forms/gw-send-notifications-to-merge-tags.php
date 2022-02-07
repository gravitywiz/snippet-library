<?php
/**
 * Gravity Wiz // Gravity Forms // Send Notifications to Merge Tags
 * https://gravitywiz.com/
 *
 * Plugin Name:  Gravity Forms Send Notifications to Merge Tags
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Use any merge tag in your notification's "Send to Email" setting.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   https://gravitywiz.com/
 *
 * By default, Gravity Forms Notifications only support Email fields and a pre-population merge tags. In some cases, you
 * may have email values in non-Email fields (i.e. populating a Drop Down field with users where their email as the
 * value).
 *
 * This plugin will process *any* merge tag used in your notification's "Send to Email" setting.
 *
 * ![Example](https://gwiz.io/34ZzYW9)
 */
add_filter( 'gform_notification', function( $notification, $form, $entry ) {
	$notification['to'] = GFCommon::replace_variables( $notification['to'], $form, $entry, false, false, false, 'text' );
	return $notification;
}, 10, 3 );
