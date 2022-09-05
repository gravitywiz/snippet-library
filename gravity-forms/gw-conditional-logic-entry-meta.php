<?php
/**
 * Gravity Wiz // Gravity Forms // Conditional Logic: Entry Meta
 * https://gravitywiz.com/
 *
 * Supports all registered meta and as well as the "Payment Status" standard meta.
 *
 * Requires Gravity Forms 2.6.2.
 *
 * Plugin Name:  GF Conditional Logic: Entry Meta
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Use the entry meta in conditional logic (e.g. payment status, approval status, etc).
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
class GW_CL_Entry_Meta {

    private static $instance;

    public static function get_instance() {

        if ( ! self::$instance ) {
            self::$instance = new GW_CL_Entry_Meta();
        }

        return self::$instance;
    }

	private function __construct() {

		if ( did_action( 'gform_loaded' ) ) {
			$this->init();
		} else {
			add_action( 'gform_loaded', array( $this, 'init' ) );
		}

	}

	public function init() {

		add_action( 'admin_footer', array( $this, 'output_admin_script' ) );
		add_filter( 'gform_rule_source_value', array( $this, 'set_rule_source_value' ), 10, 5 );

	}

	public function output_admin_script() {
		if ( wp_script_is( 'gform_form_admin' ) && GFForms::get_page() !== 'form_editor' ) :
			?>
			<script>
				const entryOptions = <?php echo json_encode( $this->get_conditional_logic_options() ); ?>;
				gform.addFilter( 'gform_conditional_logic_fields', function( options, form, selectedFieldId ) {
					for ( const property in entryOptions ) {
						if ( entryOptions.hasOwnProperty( property ) ) {
							options.push( {
								label: entryOptions[ property ].label,
								value: entryOptions[ property ].value
							} );
						}
					}
					return options;
				} );
				gform.addFilter( 'gform_conditional_logic_operators', function( operators, objectType, fieldId ) {
					if ( entryOptions.hasOwnProperty( fieldId ) ) {
						operators = entryOptions[ fieldId ].operators;
					}
					return operators;
				} );
				gform.addFilter( 'gform_conditional_logic_values_input', function( str, objectType, ruleIndex, selectedFieldId, selectedValue ) {
					if ( entryOptions.hasOwnProperty( selectedFieldId ) && entryOptions[ selectedFieldId ].choices ) {
						let inputName = objectType + '_rule_value_' + ruleIndex;
						str = GetRuleValuesDropDown( entryOptions[ selectedFieldId ].choices, objectType, ruleIndex, selectedValue, inputName );
					}
					return str;
				} );
			</script>
		<?php
		endif;
	}

	public function get_conditional_logic_options() {

		$form_ids   = 0;
		$entry_meta = GFFormsModel::get_entry_meta( $form_ids );
		$options    = array();

		$choices_by_key = array(
			'is_approved' => array(
				1 => esc_html__( 'Approved', 'gravityview' ),
				2 => esc_html__( 'Disapproved', 'gravityview' ),
				3 => esc_html__( 'Unapproved', 'gravityview' ),
			),
		);

		foreach ( $entry_meta as $key => $meta ) {
			$options[ $key ] = array(
				'label'     => $meta['label'],
				'value'     => $key,
				'operators' => array(
					'is'    => 'is',
					'isnot' => 'isNot',
				),
			);
			$_choices = rgar( $choices_by_key, $key );
			if ( ! empty( $_choices ) ) {
				$choices = array();
				foreach ( $_choices as $value => $text ) {
					$choices[] = compact( 'text', 'value' );
				}
				$options[ $key ]['choices'] = $choices;
			}
		}

		$options['payment_status'] = array(
			'label'     => esc_html__( 'Payment Status', 'gravityforms' ),
			'value'     => 'payment_status',
			'choices'   => $this->get_payment_status_choices(),
			'operators' => array(
				'is'    => 'is',
				'isnot' => 'isNot',
			),
		);

		return $options;
	}

	public function get_payment_status_choices() {
		$choices = array();
		foreach ( GFCommon::get_entry_payment_statuses() as $text => $value ) {
			$choices[] = compact( 'text', 'value' );
		}
		return $choices;
	}

	public function set_rule_source_value( $source_value, $rule, $form, $logic, $entry ) {

		$keys   = array_keys( $this->get_conditional_logic_options() );
		$target = $rule['fieldId'];

		if ( in_array( $target, $keys ) && $entry ) {
			if ( $target === 'payment_status' ) {
				// Some payment add-ons do not update the runtime entry but do update the entry in the database.
				// Fetch the latest from the database.
				$entry = GFAPI::get_entry( $entry['id'] );
			}
			$source_value = rgar( $entry, $rule['fieldId'] );
		}

		return $source_value;
	}

}

function gw_cl_entry_meta() {
    return GW_CL_Entry_Meta::get_instance();
}

gw_cl_entry_meta();
