<?php
/**
 * Gravity Perks // Entry Blocks // Archive Entry Link
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Add an Archive Link action to the Entries Table. Only users who can edit an entry can archive it.
 * Requires GP Entry Blocks 1.0-beta-2.38 or newer and GF Entry Archive.
 *
 * Instructions:
 *
 * 1. Install this snippet with your preferred snippet management solution.
 *    https://gravitywiz.com/documentation/managing-snippets/
 *
 * 2. Edit your Entries Table block and add "Archive Link" to its Summary Columns.
 *
 * 3. Optionally customize the Link Label in the column settings. The default label is "Archive".
 */
class GPEB_Archive_Entry_Link {

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		if ( ! function_exists( 'gp_entry_blocks' ) || ! class_exists( 'GFAPI' ) ) {
			return;
		}

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_script' ) );
		add_filter( 'gpeb_table_row_cell_content', array( $this, 'render_archive_link' ), 10, 3 );
		add_action( 'template_redirect', array( $this, 'archive_entry' ) );
	}

	public function enqueue_editor_script() {
		wp_register_script( 'gpeb-archive-entry-link', false, array( 'wp-hooks', 'wp-i18n' ), '0.1', true );
		wp_enqueue_script( 'gpeb-archive-entry-link' );
		wp_add_inline_script( 'gpeb-archive-entry-link', <<<'JS'
wp.hooks.addFilter(
	'gpeb_entries_table_action_items',
	'gpeb/archive-entry-link',
	function( actionItems, context ) {
		return actionItems.concat( {
			type: 'archive-link',
			label: wp.i18n.__( 'Archive Link', 'gp-entry-blocks' ),
			group: wp.i18n.__( 'Actions', 'gp-entry-blocks' ),
			customOptions: context.LinkColumnOptions,
		} );
	}
);
JS
		);
	}

	public function render_archive_link( $content, $column, $row ) {
		if ( $column->type !== 'archive-link' ) {
			return $content;
		}

		if ( ! gp_entry_blocks()->permissions->can_current_user_edit_entry( $row->entry ) ) {
			return '';
		}

		$label = rgar( $column->meta, 'linkLabel' );
		$label = $label ? $label : __( 'Archive', 'gp-entry-blocks' );
		$url   = wp_nonce_url(
			add_query_arg( 'archive_entry', $row->entry['id'], \GP_Entry_Blocks\cleaned_current_url( false ) ),
			'archive_entry_' . $row->entry['id']
		);

		return sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $label ) );
	}

	public function archive_entry() {
		$entry_id = rgget( 'archive_entry' );
		$nonce    = rgget( '_wpnonce' );

		if ( ! is_scalar( $entry_id ) || ! ctype_digit( (string) $entry_id ) || ! is_string( $nonce ) ) {
			return;
		}

		$entry_id = absint( $entry_id );

		if ( ! $entry_id || ! wp_verify_nonce( $nonce, 'archive_entry_' . $entry_id ) ) {
			return;
		}

		$entry = GFAPI::get_entry( $entry_id );

		if ( ! $entry || is_wp_error( $entry ) || ! gp_entry_blocks()->permissions->can_current_user_edit_entry( $entry ) ) {
			return;
		}

		// GF Entry Archive stores archived entries with the "archived" status.
		if ( false === GFAPI::update_entry_property( $entry_id, 'status', 'archived' ) ) {
			wp_die( esc_html__( 'The entry could not be archived. Please try again.', 'gp-entry-blocks' ) );
		}

		wp_safe_redirect( remove_query_arg( array( 'archive_entry', '_wpnonce' ) ) );
		exit;
	}

}

new GPEB_Archive_Entry_Link();
