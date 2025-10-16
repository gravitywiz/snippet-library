<?php
/**
 * Gravity Wiz // Gravity Forms // Draft Resume Change Notice
 * https://gravitywiz.com/
 *
 * Use this snippet to display a notice when the user resumes draft from a different location, browser or device.
 */
add_filter( 'gform_get_form_filter', function( $form_markup, $form ) {

	if ( empty( $_GET['gf_token'] ) ) {
		return $form_markup;
	}
	$token = sanitize_text_field( wp_unslash( $_GET['gf_token'] ) );

	global $wpdb;
	$table = GFFormsModel::get_draft_submissions_table_name();

	$draft = $wpdb->get_row(
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$wpdb->prepare(
			sprintf(
				'SELECT form_id, ip, submission FROM `%s` WHERE uuid = %%s',
				esc_sql( $table )
			),
			$token
		)
	);

	if ( ! $draft ) {
		return $form_markup;
	}

	if ( (int) $form['id'] !== (int) $draft->form_id ) {
		return $form_markup;
	}

	$submission_data = json_decode( $draft->submission, true );
	$submission_data = is_array( $submission_data ) ? $submission_data : array();

	$stored_user_agent  = $submission_data['partial_entry']['user_agent'] ?? '';
	$current_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

	$stored_ip  = $draft->ip ?? '';
	$current_ip = GFFormsModel::get_ip();

	$ip_changed      = ( $stored_ip && $current_ip && $stored_ip !== $current_ip );
	$browser_changed = ( $stored_user_agent && $current_user_agent && $stored_user_agent !== $current_user_agent );

	if ( ! $ip_changed && ! $browser_changed ) {
		return $form_markup;
	}

	// Configure Messages
	$ip_changed_message      = '🌍 Your location has changed since last editing this draft';
	$browser_changed_message = '💻 Your browser or device has changed since last editing this draft';
	$both_changed_message    = '🔒 Your location AND device have both changed since last editing this draft';

	$message = $both_changed_message;
	if ( $ip_changed && ! $browser_changed ) {
		$message = $ip_changed_message;
	} elseif ( $browser_changed && ! $ip_changed ) {
		$message = $browser_changed_message;
	}

	$warning  = '<div style="background:#fff3cd;border:1px solid #ffc107;padding:15px;margin-bottom:15px;">';
	$warning .= '<strong style="color:#856404;">' . esc_html( $message ) . '</strong>';
	$warning .= '</div>';

	return $warning . $form_markup;

}, 10, 2 );
