<?php
/**
 * Gravity Wiz // Gravity Forms // Conditional Logic: Entry Meta
 * https://gravitywiz.com/
 *
 * Supports all registered meta and as well as the "Payment Status" standard meta.
 * Handles enabling conditional logic evaluation on send for GP Notification Scheduler when notification
 * contains a conditioanl rule for "payment_status".
 *
 * Requires Gravity Forms 2.6.2.
 *
 * Plugin Name:  GF Conditional Logic: Entry Meta
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Use the entry meta in conditional logic (e.g. payment status, approval status, etc).
 * Author:       Gravity Wiz
 * Version:      0.2
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
		add_filter( 'gpns_evaluate_conditional_logic_on_send', array( $this, 'gpns_should_evaluate_conditional_logic_on_send' ), 10, 4 );

	}

	public function output_admin_script() {
		if ( wp_script_is( 'gform_form_admin' ) && GFForms::get_page() !== 'form_editor' ) :
			?>
			<script>
				const entryOptions = <?php echo json_encode( $this->get_conditional_logic_options() ); ?>;
				gform.addFilter( 'gform_conditional_logic_fields', function( options, form, selectedFieldId ) {
					for ( const property in entryOptions ) {
						// Entry meta are already added in Notifications and Confirmations conditional logic but not in feeds.
						// Let's just make sure that none of our entry meta options have been previously added.
						if ( entryOptions.hasOwnProperty( property ) && ! options.find( opt => opt.value === entryOptions[ property ].value ) ) {
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

		$form_ids = 0;

		// Scope entry meta to the current form for GP Email Users.
		if ( is_admin() && rgget( 'page' ) === 'gp-email-users' ) {
			$form_ids = rgpost( '_gform_setting_form' );
			if ( ! $form_ids ) {
				$draft    = gp_email_users()->get_draft();
				$form_ids = $draft['form'];
			}
		}

		$options = array();

		if ( ! empty( $form_ids ) ) {
			$post_submission_conditional_logic_field_types = array(
				'uid' => array(
					'operators' => array(
						'is'          => 'is',
						'isnot'       => 'isNot',
						'>'           => 'greaterThan',
						'<'           => 'lessThan',
						'contains'    => 'contains',
						'starts_with' => 'startsWith',
						'ends_with'   => 'endsWith',
					),
				),
			);
			$form = GFAPI::get_form( is_array( $form_ids ) ? $form_ids[0] : $form_ids );
			if ( $form ) {
				$fields = GFAPI::get_fields_by_type( $form, array_keys( $post_submission_conditional_logic_field_types ) );
				foreach ( $fields as $field ) {
					$options[ $field->id ] = array(
						'label'     => $field->label,
						'value'     => $field->id,
						'operators' => rgars( $post_submission_conditional_logic_field_types, $field->type . '/operators', array() ),
					);
				}
			}
		}

		$entry_meta = GFFormsModel::get_entry_meta( $form_ids );

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
			$_choices        = rgar( $choices_by_key, $key );
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
			if ( in_array( $target, $this->get_runtime_entry_meta_keys(), true ) ) {
				// Some payment add-ons do not update the runtime entry but do update the entry in the database.
				// Fetch the latest from the database.
				$entry = GFAPI::get_entry( $entry['id'] );
			}
			$source_value = rgar( $entry, $rule['fieldId'] );
		}

		return $source_value;
	}

	public function gpns_should_evaluate_conditional_logic_on_send( $eval_on_send, $form, $entry, $notifications ) {

		foreach ( $notifications as $notification ) {
			$_notification = gp_notification_schedule()->get_notification( $form, $notification['nid'] );
			foreach ( rgars( $_notification, 'notification_conditional_logic_object/rules' ) as $rule ) {
				if ( in_array( $rule['fieldId'], $this->get_runtime_entry_meta_keys(), true ) ) {
					return true;
				}
			}
		}

		return $eval_on_send;
	}

	/**
	 * Get the keys for any entry meta that may be updated during a form submission.
	 *
	 * Since these are updated mid-submission, conditional logic in some contexts (like notifications triggered by a feed
	 * action) will be using a stale entry. Identifying these entry meta keys allows us to ensure special functionality
	 * to support them.
	 *
	 * @return mixed|void
	 */
	public function get_runtime_entry_meta_keys() {
		return apply_filters( 'gwclem_runtime_entry_meta_keys', array( 'payment_status' ) );
	}

}

function gw_cl_entry_meta() {
	return GW_CL_Entry_Meta::get_instance();
}

gw_cl_entry_meta();
