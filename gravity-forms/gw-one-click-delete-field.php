<?php
/**
 * Gravity Wiz // Gravity Forms // One-click Delete Field in Compact View
 * https://gravitywiz.com/
 *
 * This snippet adds a one-click delete button (no confirmation) to each field when viewing the
 * form in compact view.
 *
 * Instructions:
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 */
add_action('admin_footer', function() {
	if ( GFForms::get_page() !== 'form_editor' ) {
		return;
	}
	?>
	<style>
		.gform-editor--compact .gw-delete-field {
			display: block !important;
		}
	</style>
	<script>
		jQuery( '.gform-compact-view-overflow-menu' ).each( function() {
			var fieldId = jQuery( this )[0].id.split( '_' )[2];
			jQuery( this ).before( '<button class="gw-delete-field gform-droplist__item-trigger--info gform-compact-view-overflow-menu__item-delete" onclick="proceedWithDeletion( ' + fieldId + ' )" style="display: none;min-height: auto;background: transparent;box-shadow: none;padding: 0;border-color:transparent;"><span class="gform-icon gform-icon--trash gform-droplist__item-trigger-icon" style="margin-inline-end:0;cursor:pointer;"></span><div class="gform-text gform-text--color-port gform-typography--size-text-sm gform-typography--weight-regular gform-droplist__item-trigger-text" style="display:none;">Delete</div></button>' )
		} );
	</script>
	<?php
} );
