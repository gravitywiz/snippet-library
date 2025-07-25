<?php
/**
 * Gravity Wiz // Gravity Forms // Rich Text HTML Fields
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/fc666b9d3e1f48ed9dc21a3fcadef783
 *
 */

/*
 * @todo
 * 1. Add merge tag selector.
 */

add_action( 'admin_init', function() {
	if ( GFForms::get_page() === 'form_editor' ) {
		wp_enqueue_editor();
		wp_enqueue_media(); // Required for media uploads
	}
} );

add_action( 'gform_field_standard_settings', function( $position ) {
	if ( $position !== 200 ) {
		return;
	}
	?>

	<li class="content_setting rich_content_setting field_setting">
		<label for="field_content" class="section_label">
			<?php esc_html_e( 'Content', 'gravityforms' ); ?>
			<?php gform_tooltip( 'form_field_content' ); ?>
		</label>
		<?php
		$id       = 'field_rich_content';
		$settings = array(
			'tinymce'       => true,
			'textarea_name' => $id,
			'editor_height' => 250,
		);
		wp_editor( '', $id, $settings );
		?>
	</li>

	<style>
		.rich_content_setting label {
			/* Display the label above the editor without overlapping the Visual/Code tabs. */
			display: inline-block !important;
			position: relative;
			z-index: 2;
		}
		#wp-field_rich_content-wrap {
			margin-top: -2.7rem;
		}
		.content_setting:not( .rich_content_setting ) {
			display: none !important;
		}
		.rich_content_setting i.mce-i-image {
			font-size: 20px;
		}
	</style>

	<script>
		jQuery( document ).on( 'gform_load_field_settings', function( event, field ) {
			var id = 'field_rich_content';
			wp.editor.remove( id );
			jQuery( '#' + id ).val( field.content );
			wp.editor.initialize( id, {
				tinymce: {
					setup: function( editor ) {
						editor.on( 'Paste Change input Undo Redo', function () {
							SetFieldProperty( 'content', editor.getContent() );
						} );
					}
				},
				quicktags: true
			} );
		} );
		jQuery( document).on( 'tinymce-editor-setup', function ( event, editor ) {
			var editorId = 'field_rich_content';
			if ( editor.id === editorId ) {
				editor.settings.toolbar1 = 'bold,italic,underline,bullist,numlist,alignleft,aligncenter,alignright,link,image';

				// Handle image insertion from media library
				editor.addButton( 'image', {
					icon: 'image',
					tooltip: 'Insert Image',
					onclick: function() {
						var frame = wp.media({
							title: 'Insert Media',
							button: { text: 'Insert into HTML Field' },
							multiple: false,
							library: { type: 'image' }
						} );

						frame.on('select', function() {
							var selection = frame.state().get('selection').first();
							if (!selection) {
								return;
							}

							var attachment = selection.toJSON();
							var url = attachment.url.replace(/"/g, '&quot;');
							var alt = (attachment.alt || '').replace(/"/g, '&quot;');
							editor.insertContent('<img src="' + url + '" alt="' + alt + '" />');
						} );

						frame.open();
					}
				} );

				// Switch to visual/text mode.
				jQuery(`#wp-${editorId}-wrap .switch-tmce, #wp-${editorId}-wrap .switch-html`).on('click', function() {
					var mode = jQuery(this).hasClass('switch-tmce') ? 'tmce' : 'html';

					window.switchEditors.go(editorId, mode);
				});
			}
		} );
	</script>

	<?php
} );
