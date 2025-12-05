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
		var gwRichTextMode;
		jQuery( document ).on( 'gform_load_field_settings', function( event, field ) {
			gwRichTextMode = field.gwRichTextMode || 'tmce';

			var id = 'field_rich_content';
			wp.editor.remove( id );
			jQuery( '#' + id ).val( field.content );
			wp.editor.initialize( id, {
				tinymce: {
					forced_root_block: false,
					height: 250,
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

				// Wait until the TinyMCE editor is initialized before switching mode.
				const waitForEditorToBeReady = (callback, timeout = 5000) => {
					const start = Date.now();
					const interval = setInterval(() => {
						const editor = typeof tinymce !== 'undefined' && tinymce.get(editorId);
						if (editor) {
							clearInterval(interval);
							callback();
						} else if (Date.now() - start > timeout) {
							clearInterval(interval);
						}
					}, 100);
				};

				waitForEditorToBeReady(() => window.switchEditors.go(editorId, gwRichTextMode === 'html' ? 'html' : 'tmce'));

				// Set the content when save.
				window.SetFieldContentProperty = function () {
					var mode = jQuery('#wp-' + editorId + '-wrap').hasClass('html-active') ? 'html' : 'tmce';
					var content = '';

					if (mode === 'html') {
						content = jQuery('#' + editorId).val();
					} else if (tinymce.get(editorId)) {
						content = tinymce.get(editorId).getContent();
					}

					SetFieldProperty('content', content);
				};

				// Update the content.
				jQuery(document).on('change', `#${editorId}`, function () {
					window.SetFieldContentProperty();
				});

				// Switch to visual/text mode.
				jQuery(`#wp-${editorId}-wrap .switch-tmce, #wp-${editorId}-wrap .switch-html`).on('click', function() {
					var mode = jQuery(this).hasClass('switch-tmce') ? 'tmce' : 'html';

					window.switchEditors.go(editorId, mode);

					// Save the current mode to field property.
					SetFieldProperty('gwRichTextMode', mode)
				});
			}
		} );
	</script>

	<?php
} );
