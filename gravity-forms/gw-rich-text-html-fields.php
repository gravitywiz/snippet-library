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

		function gwRichTextHTMLFields() {
			function isReady() {
				return document.readyState === 'complete';
			}

			async function waitForDOMReady() {
				console.log('running wait function')
				return new Promise((resolve) => {
					const interval = setInterval(() => {
						if (isReady()) {
							console.log('detected ready')
							clearInterval(interval)	;
							resolve();
						}
					}, 50)
				});
			}

			jQuery( document ).on( 'gform_load_field_settings', async function( event, field ) {
				if (field.type !== 'html') {
					return;
				}

				// Set textarea value BEFORE initializing the editor so it has content to load
				jQuery( '#' + id ).val( field.content );

				console.log('gform_load_field_settings', { tineymce: window.tinymce, event, field, content: field.content, type: field.type });

				if (!isReady()) {
					console.log('waiting for dom to be ready')
					await waitForDOMReady();
				}

				console.log('continue init')

				var id = 'field_rich_content';
				wp.editor.remove( id );


				wp.editor.initialize( id, {
					tinymce: {
						height: 250,
						setup: function( editor ) {

							editor.on( 'Paste Change input Undo Redo', function () {
								SetFieldProperty( 'content', editor.getContent() );
							} );

							editor.settings.toolbar1 = 'bold,italic,underline,bullist,numlist,alignleft,aligncenter,alignright,link,image';

							editor.on('init', function () {
								editor.setContent(field.content);
				    		    console.log('WP TinyMCE editor ready 1:', editor.id, editor.getContent());
				    		});

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
							jQuery(`#wp-${id}-wrap .switch-tmce, #wp-${id}-wrap .switch-html`).on('click', function() {
								var mode = jQuery(this).hasClass('switch-tmce') ? 'tmce' : 'html';

								window.switchEditors.go(id, mode);
							});
						}
					},
					quicktags: true
				} );
			} );
		}

		gwRichTextHTMLFields();
	</script>

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

	<?php
} );
