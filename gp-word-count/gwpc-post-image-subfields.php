<?php
/**
 * Gravity Wiz // Gravity Forms // Word Count for Post Image Subfields (Caption, Description, Alt, Title)
 *
 * Instruction Video: https://www.loom.com/share/005eda834a6348f9a874a5e6cdd85461
 *
 * Set min/max word counts for any Post Image subfield (caption, description, alt, title) per field.
 * Supports multiple subfields per field.
 *
 * @version   1.0.0
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      https://gravitywiz.com/
 */
class GW_Post_Image_Word_Count {

	private static $_instance = null;
	private $_configs = array();

	public static function get_instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct( $config = array() ) {

		$this->_configs[] = array_merge( array(
			'form_id'  => false,
			'field_id' => false,
			'limits'   => array()
		), $config );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		if ( ! $this->is_applicable() ) {
			return;
		}

		add_action( 'gform_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
		add_action( 'gform_register_init_scripts', array( $this, 'enqueue_textarea_counter' ), 20 );
		add_filter( 'gform_validation', array( $this, 'validate_word_count' ) );

	}

	public function is_applicable() {
		return ! empty( $this->_configs );
	}

	// Map subfield keys to their input suffixes and labels.
	public function get_subfields() {
		return array(
			'caption'     => array( 'suffix' => '4', 'label' => __( 'Caption', 'gravityforms' ) ),
			'description' => array( 'suffix' => '7', 'label' => __( 'Description', 'gravityforms' ) ),
			'alt'         => array( 'suffix' => '2', 'label' => __( 'Alt Text', 'gravityforms' ) ),
			'title'       => array( 'suffix' => '1', 'label' => __( 'Title', 'gravityforms' ) ),
		);
	}

	public function enqueue_scripts( $form ) {
		// Check if any Post Image field is configured for word count.
		foreach ( $this->_configs as $config ) {
			if ( ( ! $config['form_id'] || $form['id'] == $config['form_id'] ) && $config['field_id'] ) {
				foreach ( $form['fields'] as $field ) {
					if ( $field->id == $config['field_id'] && $field->type === 'post_image' ) {
						// Force enqueue the GP Word Count JS.
						wp_enqueue_script( 'gp-word-count', plugins_url( 'scripts/jquery.textareaCounter.js', 'gwwordcount/gwwordcount.php' ), array( 'jquery' ), null, true );
						return;
					}
				}
			}
		}
	}

	public function enqueue_textarea_counter( $form ) {

		foreach ( $this->_configs as $config ) {
			if ( ! $this->is_config_applicable_to_form( $config, $form ) ) {
				continue;
			}

			$field = GFAPI::get_field( $form, $config['field_id'] );
			if ( ! $field || $field->type !== 'post_image' || empty( $config['limits'] ) ) {
				continue;
			}

			$form_id  = $form['id'];
			$field_id = $field->id;

			foreach ( $config['limits'] as $subfield => $limits ) {
				$subfields = $this->get_subfields();
				if ( empty( $subfields[ $subfield ] ) ) {
					continue;
				}

				$suffix = $subfields[ $subfield ]['suffix'];
				$min    = isset( $limits['min'] ) ? intval( $limits['min'] ) : 0;
				$max    = isset( $limits['max'] ) ? intval( $limits['max'] ) : 0;

				$args = array(
					'formId'                 => $form_id,
					'limit'                   => $max,
					'min'                     => $min,
					'truncate'                => true,
					'defaultLabel'            => sprintf( __( 'Max: %s words', 'gp-word-count' ), '{limit}' ),
					'defaultLabelSingular'    => sprintf( __( 'Max: %s word', 'gp-word-count' ), '{limit}' ),
					'counterLabel'            => sprintf( __( '%s words left', 'gp-word-count' ), '{remaining}' ),
					'counterLabelSingular'    => sprintf( __( '%s word left', 'gp-word-count' ), '{remaining}' ),
					'limitReachedLabel'       => '<span class="gwwc-max-reached" style="font-weight:bold;">' . sprintf( __( '%s words left', 'gp-word-count' ), '{remaining}' ) . '</span>',
					'limitExceededLabel'      => '<span class="gwwc-max-exceeded" style="font-weight:bold;color:#c0392b;">' . sprintf( __( 'Limit exceeded!', 'gp-word-count' ), '{remaining}' ) . '</span>',
					'minCounterLabel'         => sprintf( __( '%s more words required', 'gp-word-count' ), '{remaining}' ),
					'minCounterLabelSingular' => sprintf( __( '%s more word required', 'gp-word-count' ), '{remaining}' ),
					'minReachedLabel'         => '<span class="gwwc-min-reached" style="font-weight:bold;color:#27ae60">' . __( 'Minimum word count met.', 'gp-word-count' ) . '</span>',
					'minDefaultLabel'         => sprintf( __( 'Min: %s words', 'gp-word-count' ), '{min}' ),
					'minDefaultLabelSingular' => sprintf( __( 'Min: %s word', 'gp-word-count' ), '{min}' ),
				);

				$args_json = json_encode( $args );
				$input_id  = "input_{$form_id}_{$field_id}_{$suffix}";
				$script    = "jQuery('#{$input_id}').textareaCounter({$args_json});";

				GFFormDisplay::add_init_script( $form_id, "gwwc_post_image_{$subfield}_wc_{$field_id}", GFFormDisplay::ON_PAGE_RENDER, $script );
			}
		}

	}

	public function validate_word_count( $result ) {

		$form = $result['form'];

		foreach ( $this->_configs as $config ) {
			if ( ! $this->is_config_applicable_to_form( $config, $form ) ) {
				continue;
			}

			foreach ( $form['fields'] as &$field ) {
				if ( $field->id != $config['field_id'] || $field->type !== 'post_image' || empty( $config['limits'] ) ) {
					continue;
				}

				foreach ( $config['limits'] as $subfield => $limits ) {
					$subfields = $this->get_subfields();
					if ( empty( $subfields[ $subfield ] ) ) {
						continue;
					}

					$suffix     = $subfields[ $subfield ]['suffix'];
					$label      = $subfields[ $subfield ]['label'];
					$input_name = "input_{$field->id}_{$suffix}";
					$value      = rgpost( $input_name );
					$word_count = preg_match_all( '/\S+/', trim( $value ) );

					$min = isset( $limits['min'] ) ? intval( $limits['min'] ) : 0;
					$max = isset( $limits['max'] ) ? intval( $limits['max'] ) : 0;

					if ( $min && $word_count < $min ) {
						$field->failed_validation = true;
						$field->validation_message = sprintf(
							_n( '%s must be at least %s word.', '%s must be at least %s words.', $min, 'gp-word-count' ),
							$label, $min
						);
						$result['is_valid'] = false;
					}

					if ( $max && $word_count > $max ) {
						$field->failed_validation = true;
						$field->validation_message = sprintf(
							_n( '%s may only be %s word.', '%s may only be %s words.', $max, 'gp-word-count' ),
							$label, $max
						);
						$result['is_valid'] = false;
					}
				}
			}
		}

		$result['form'] = $form;
		return $result;

	}

	private function is_config_applicable_to_form( $config, $form ) {
		return ( ! $config['form_id'] || $form['id'] == $config['form_id'] ) && $config['field_id'];
	}

}

// CONFIGURATION: Set your field IDs and subfield limits here.
new GW_Post_Image_Word_Count( array(
	'form_id'  => 1, // Form ID (optional, can be false to apply to all forms)
	'field_id' => 3, // Post Image field ID
	'limits'   => array(
		'caption'	 => array( 'min' => 3, 'max' => 10 ),
		'description' => array( 'min' => 2, 'max' => 20 ),
		// 'alt'	  => array( 'min' => 1, 'max' => 5 ),
		// 'title'	=> array( 'min' => 1, 'max' => 5 ),
	),
) );

// Add more configurations as needed...
// new GW_Post_Image_Word_Count( array(
//	 'form_id'  => 2,
//	 'field_id' => 8,
//	 'limits'   => array(
//		 'caption' => array( 'min' => 5, 'max' => 15 ),
//	 ),
// ) );
