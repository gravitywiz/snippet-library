<?php
/**
 * Clear all indexes for GPUID.
 */
add_action( 'admin_init', function() {
	if ( ! rgget( 'gpuid_clear_indexes' ) ) {
		return;
	}
	// Ensure GPUID is disabled
	if ( class_exists( 'GP_Unique_ID' ) ) {
		wp_redirect( '/wp-admin/plugins.php?plugin_status=active&guid_clear_indexes_error_active=true' );
		exit();
	}
	global $wpdb;
	// Get Indexes
	$table_name    = "{$wpdb->prefix}gpui_sequence";
	$sql           = "SHOW INDEX FROM `{$table_name}`;";
	$index_results = $wpdb->get_results( $sql );
	$indexes       = array();
	foreach ( $index_results as $index_result ) {
		$indexes[ $index_result->Key_name ] = true;
	}
	$indexes = array_keys( $indexes );
	// Clear all indexes
	foreach ( $indexes as $index ) {
		$sql = "DROP INDEX `{$index}` ON `{$table_name}`;";
		$wpdb->query( $sql );
	}
	// All done, redirect
	wp_redirect( '/wp-admin/plugins.php?plugin_status=recently_activated&guid_clear_indexes_success=true' );
	exit();
} );

add_action( 'admin_notices', function ( $messages ) {
	if ( rgget( 'guid_clear_indexes_error_active' ) ) :
		?>
		<div class="notice notice-error">
			<p>Please disable GP Unique ID before attempting to clear the indexes.</p>
		</div>
		<?php
	elseif ( rgget( 'guid_clear_indexes_success' ) ) :
		?>
		<div class="notice notice-success">
			<p>GP Unique ID table indexes were cleared. Please de-activate this snippet and enable GPUID again.</p>
		</div>
		<?php
	else :
		?>
		<div class="notice">
			<p>GP Unique ID Clear Indexes snippet is active. If you have already cleared the indexes please remove it.
				Otherwise you can <a href="/wp-admin/plugins.php?gpuid_clear_indexes=true">start clearing indexes here</a>.</p>
		</div>
		<?php
	endif;
} );
