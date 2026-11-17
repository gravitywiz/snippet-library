<?php
/**
 * Gravity Wiz // Gravity Forms // Custom Form Meta
 *
 * Adds a "Custom Meta" section to the Form Settings where you can define your own key/value pairs
 * on a per-form basis. Each pair is then available as a merge tag — {form_meta:your_key} — anywhere
 * Gravity Forms processes merge tags (notifications, confirmations, field defaults, HTML fields, etc).
 *
 * Plugin Name:  Gravity Forms — Custom Form Meta
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Manage custom meta on a form and use it as merge tags anywhere merge tags are supported.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 *
 * Usage:
 *
 * 1. Add to snippet to site. See https://gravitywiz.com/documentation/how-do-i-install-a-snippet/.
 * 2. Go to Form Settings and scroll to the "Custom Meta" section.
 * 3. Add a key (e.g. "deadline") and a value (e.g. "December 31, 2026").
 * 4. Use {form_meta:deadline} in your notifications, confirmations, etc.
 *
 */
class GW_Custom_Form_Meta {

	/**
	 * The key under which custom meta is stored in the form object.
	 */
	const META_KEY = 'customMeta';

	private static $instance = null;

	private $current_form = null;

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {

		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		add_filter( 'gform_form_settings_fields', array( $this, 'add_settings_section' ), 10, 2 );
		add_filter( 'gform_pre_form_settings_save', array( $this, 'save_settings' ) );

		add_filter( 'gform_custom_merge_tags', array( $this, 'add_merge_tags' ), 10, 2 );
		add_filter( 'gform_replace_merge_tags', array( $this, 'replace_merge_tags' ), 10, 7 );

		add_filter( 'gform_pre_render', array( $this, 'set_current_form' ), 1 );
		add_filter( 'gform_pre_validation', array( $this, 'set_current_form' ), 1 );
		add_filter( 'gform_pre_submission_filter', array( $this, 'set_current_form' ), 1 );

	}

	/**
	 * Get all custom meta for a form as a flat key => value array.
	 *
	 * @param array|int $form Form object or form ID.
	 *
	 * @return array
	 */
	public static function get_all( $form ) {

		if ( ! is_array( $form ) ) {
			$form = GFAPI::get_form( $form );
		}

		$meta  = rgar( $form, self::META_KEY, array() );
		$pairs = array();

		if ( ! is_array( $meta ) ) {
			return $pairs;
		}

		foreach ( $meta as $pair ) {
			$key = rgar( $pair, 'key' );
			if ( $key !== '' && $key !== null ) {
				$pairs[ $key ] = rgar( $pair, 'value' );
			}
		}

		return $pairs;
	}

	/**
	 * Get a single custom meta value for a form.
	 *
	 * @param array|int $form    Form object or form ID.
	 * @param string    $key     Meta key.
	 * @param mixed     $default Returned when the key is not set.
	 *
	 * @return mixed
	 */
	public static function get( $form, $key, $default = '' ) {
		$pairs = self::get_all( $form );
		return array_key_exists( $key, $pairs ) ? $pairs[ $key ] : $default;
	}

	public function add_settings_section( $fields, $form ) {

		$fields['custom_meta'] = array(
			'title'  => esc_html__( 'Custom Meta', 'gravityforms' ),
			'fields' => array(
				array(
					'name' => 'custom_meta',
					'type' => 'html',
					'html' => function() use ( $form ) {
						return $this->render_setting( $form );
					},
				),
			),
		);

		return $fields;
	}

	public function render_setting( $form ) {

		// Favor posted values so nothing is lost if the settings page re-renders after a failed save.
		$meta = $this->get_posted_meta();
		if ( $meta === null ) {
			$meta = rgar( $form, self::META_KEY, array() );
		}

		if ( ! is_array( $meta ) || empty( $meta ) ) {
			$meta = array( array( 'key' => '', 'value' => '' ) );
		}

		ob_start();
		?>

		<p class="gform-settings-description">
			<?php esc_html_e( 'Define custom meta for this form. Each key is available as a merge tag, e.g.', 'gravityforms' ); ?>
			<code>{form_meta:your_key}</code>
		</p>

		<table class="gwcfm-table" id="gwcfm-table">
			<thead>
				<tr>
					<th style="text-align:left;width:30%;"><?php esc_html_e( 'Key', 'gravityforms' ); ?></th>
					<th style="text-align:left;"><?php esc_html_e( 'Value', 'gravityforms' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( array_values( $meta ) as $index => $pair ) : ?>
					<tr class="gwcfm-row">
						<td>
							<input type="text" class="gwcfm-key" name="custom_meta[<?php echo (int) $index; ?>][key]"
								value="<?php echo esc_attr( rgar( $pair, 'key' ) ); ?>" placeholder="<?php esc_attr_e( 'key', 'gravityforms' ); ?>" />
						</td>
						<td>
							<input type="text" name="custom_meta[<?php echo (int) $index; ?>][value]"
								value="<?php echo esc_attr( rgar( $pair, 'value' ) ); ?>" placeholder="<?php esc_attr_e( 'value', 'gravityforms' ); ?>" style="width:100%;" />
						</td>
						<td>
							<button type="button" class="button gwcfm-remove" aria-label="<?php esc_attr_e( 'Remove', 'gravityforms' ); ?>">&times;</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p><button type="button" class="button" id="gwcfm-add"><?php esc_html_e( 'Add Meta', 'gravityforms' ); ?></button></p>

		<style type="text/css">
			#gwcfm-table { width: 100%; max-width: 40rem; border-collapse: collapse; }
			#gwcfm-table th, #gwcfm-table td { padding: 0 0.5rem 0.5rem 0; vertical-align: top; }
			#gwcfm-table .gwcfm-key { width: 100%; }
		</style>

		<script type="text/javascript">
			( function() {

				var table = document.getElementById( 'gwcfm-table' ),
					body  = table.querySelector( 'tbody' ),
					add   = document.getElementById( 'gwcfm-add' );

				function reindex() {
					body.querySelectorAll( '.gwcfm-row' ).forEach( function( row, index ) {
						row.querySelectorAll( 'input' ).forEach( function( input ) {
							input.name = input.name.replace( /custom_meta\[\d+\]/, 'custom_meta[' + index + ']' );
						} );
					} );
				}

				add.addEventListener( 'click', function() {
					var row = body.querySelector( '.gwcfm-row' ).cloneNode( true );
					row.querySelectorAll( 'input' ).forEach( function( input ) {
						input.value = '';
					} );
					body.appendChild( row );
					reindex();
					row.querySelector( 'input' ).focus();
				} );

				body.addEventListener( 'click', function( event ) {
					if ( ! event.target.classList.contains( 'gwcfm-remove' ) ) {
						return;
					}
					if ( body.querySelectorAll( '.gwcfm-row' ).length === 1 ) {
						body.querySelectorAll( 'input' ).forEach( function( input ) {
							input.value = '';
						} );
						return;
					}
					event.target.closest( '.gwcfm-row' ).remove();
					reindex();
				} );

			} )();
		</script>

		<?php
		return ob_get_clean();
	}

	public function save_settings( $form ) {

		$meta = $this->get_posted_meta();

		if ( $meta !== null ) {
			$form[ self::META_KEY ] = $meta;
		}

		return $form;
	}

	/**
	 * Read, sanitize and normalize the posted meta. Returns null when nothing was posted (i.e. we
	 * are saving a different settings subview and should leave the stored meta alone).
	 *
	 * @return array|null
	 */
	private function get_posted_meta() {

		$posted = rgpost( 'custom_meta' );

		if ( ! is_array( $posted ) ) {
			return null;
		}

		$meta = array();

		foreach ( $posted as $pair ) {

			$key = $this->sanitize_key( rgar( $pair, 'key' ) );

			if ( $key === '' ) {
				continue;
			}

			$meta[] = array(
				'key'   => $key,
				'value' => wp_kses_post( rgar( $pair, 'value' ) ),
			);

		}

		return $meta;
	}

	/**
	 * Keys are restricted to characters that are safe inside a merge tag.
	 */
	private function sanitize_key( $key ) {
		return preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $key );
	}

	public function add_merge_tags( $merge_tags, $form_id ) {

		foreach ( self::get_all( $form_id ) as $key => $value ) {
			$merge_tags[] = array(
				'label' => sprintf( esc_html__( 'Form Meta: %s', 'gravityforms' ), $key ),
				'tag'   => sprintf( '{form_meta:%s}', $key ),
			);
		}

		return $merge_tags;
	}

	public function set_current_form( $form ) {
		$this->current_form = $form;
		return $form;
	}

	public function replace_merge_tags( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

		if ( ! is_string( $text ) || strpos( $text, '{form_meta:' ) === false ) {
			return $text;
		}

		if ( empty( $form ) ) {
			$form = $this->current_form;
		}

		if ( empty( $form ) ) {
			return $text;
		}

		if ( ! preg_match_all( '/{form_meta:([a-zA-Z0-9_-]+)}/', $text, $matches, PREG_SET_ORDER ) ) {
			return $text;
		}

		$meta = self::get_all( $form );

		foreach ( $matches as $match ) {

			list( $tag, $key ) = $match;

			$value = array_key_exists( $key, $meta ) ? $meta[ $key ] : '';
			$value = GFCommon::format_variable_value( $value, $url_encode, $esc_html, $format );

			$text = str_replace( $tag, $value, $text );

		}

		return $text;
	}

}

# Configuration

GW_Custom_Form_Meta::get_instance();
