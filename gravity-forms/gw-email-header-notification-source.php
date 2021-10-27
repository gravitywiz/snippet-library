<?php
/**
 * Gravity Wiz // Gravity Forms // Email Header to Identify Notification Source
 * https://gravitywiz.com/
 *
 * You've installed Gravity Forms on a ton of sites and now you're getting a ton of notifications. The problem is there
 * is no clear indicator which site is sending the notification. How frustrating!
 *
 * This simple plugin automatically adds a custom header that identifies the URL that generated the Gravity Forms notification.
 * This is most useful when installed as an MU plugin at the start of development on each site.
 *
 * Plugin Name:  Gravity Forms - Email Header: Notification Source
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Add a custom email header to identify the URL that generated the Gravity Forms notification.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gform_pre_send_email', function( $email ) {
	$email['headers']['X-gravity-forms-source'] = sprintf( 'X-gravity-forms-source: %s', GFFormsModel::get_current_page_url() );
	return $email;
} );
