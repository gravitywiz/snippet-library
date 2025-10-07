<?php
/**
 * Experimental! See important notes below.
 *
 * Gravity Perks // GP Entry Blocks // Show "Download Entries as CSV" link for Entries Table blocks
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Installation:
 *  1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * Important notes:
 *  * This will show the link on all Entries Table blocks for now.
 *  * The CSV export does not take into consideration the filters in an Entries Table's parent Entries block.
 *  * Exporting will only export for 20 seconds. Anything after that will not be included in the export. We will need to implement AJAX
 *    much like Gravity Forms does in the admin to enable exporting for longer than that.
 *  * This is a server-side request to handle exporting. If this block is visible to the public, it could potentially create
 *    performance issues if frequently clicked.
 */
add_action( 'gpeb_before_render_block', function( $block ) {
	// Do not show this in the Block Editor (or when saving in the BE)
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	if ( $block->name !== 'gp-entry-blocks/entries-table' ) {
		return;
	}

	global $post;

	$export_url = add_query_arg( array(
		'export_block_entries'       => $block->context['gp-entry-blocks/uuid'],
		'export_block_entries_nonce' => wp_create_nonce( $block->context['gp-entry-blocks/uuid'] ),
		'block_type'                 => $block->context['gp-entry-blocks/uuid'],
	), \GP_Entry_Blocks\get_current_url() );

	echo '<a href="' . $export_url . '" target="_blank" class="gpeb-export-csv-link">Download Entries as CSV</a>';
} );

add_action( 'template_redirect', function() {
	$block_uuid = rgget( 'export_block_entries' );

	if ( ! $block_uuid ) {
		return;
	}

	if ( ! wp_verify_nonce( rgget( 'export_block_entries_nonce' ), $block_uuid ) ) {
		return;
	}

	$parsed_entries_block       = \GP_Entry_Blocks\get_parsed_block_by_uuid( $block_uuid, 'gp-entry-blocks/entries' );
	$parsed_entries_table_block = \GP_Entry_Blocks\get_parsed_block_by_uuid( $block_uuid, 'gp-entry-blocks/entries-table' );

	if ( ! $parsed_entries_block || ! $parsed_entries_table_block ) {
		return;
	}

	$block = new WP_Block( $parsed_entries_block, $parsed_entries_block['availableContext'] );

	// Use $block->attributes with the keys prefixed with "gp-entry-blocks/" as the context.
	$context = array_combine(
		array_map( function( $key ) {
			return 'gp-entry-blocks/' . $key;
		}, array_keys( $block->attributes ) ),
		$block->attributes
	);

	// @todo Wire up to `gform_search_criteria_export_entries`
	$queryer = \GP_Entry_Blocks\GF_Queryer::attach( $context );

	require_once( GFCommon::get_base_path() . '/export.php' );

	$form = GFAPI::get_form( $block->attributes['formId'] );

	// Make unique to GPEB.
	$export_id = wp_hash( uniqid( 'export', true ) );
	$export_id = sanitize_key( $export_id );

	// Use the summary columns from the Entries Table block in the export.
	$summary_columns = $parsed_entries_table_block['attrs']['formFields'];

	$_POST['export_field'] = array();

	foreach ( $summary_columns as $summary_column ) {
		switch ( $summary_column['type'] ) {
			case 'id':
				$_POST['export_field'][] = $summary_column['type'];
				break;

			case 'field':
				$_POST['export_field'][] = $summary_column['meta']['fieldId'];
				break;
		}
	}

	/**
	 * @var array{
	 *  status: 'complete'|'in_progress',
	 *  offset: int,
	 *  exportId: string,
	 *  progress: string,
	 * } $export_result
	 */
	// Note, the way this works is it'll only export for 20 seconds (or whatever is set in `gform_export_max_execution_time`).
	// Anything after that will not be included in the export.
	$export_result = GFExport::start_export( $form, 0, $export_id );

	$filename      = sanitize_title_with_dashes( $form['title'] ) . '-' . gmdate( 'Y-m-d', GFCommon::get_local_timestamp( time() ) ) . '.csv';
	$export_folder = RGFormsModel::get_upload_root() . 'export/';
	$file          = $export_folder . sanitize_file_name( 'export-' . $export_id . '.csv' );

	$charset = get_option( 'blog_charset' );
	header( 'Content-Description: File Transfer' );
	header( "Content-Disposition: attachment; filename=$filename" );
	header( 'Content-Type: text/csv; charset=' . $charset, true );
	$buffer_length = ob_get_length(); //length or false if no buffer
	if ( $buffer_length > 1 ) {
		ob_clean();
	}

	$result = readfile( $file );

	if ( $result === false ) {
		GFCommon::log_error( __METHOD__ . '(): An issue occurred whilst reading the file.' );
	} else {
		@unlink( $file );
		GFCommon::log_debug( __METHOD__ . '(): Number of bytes read from the file: ' . print_r( $result, 1 ) );
	}

	die();
} );
