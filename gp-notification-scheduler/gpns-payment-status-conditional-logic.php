<?php
/**
 * Gravity Perks // Notification Scheduler // Use Payment Status in Conditional Logic
 * https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 *
 * Video Overview: https://www.loom.com/share/0e6bb92cef62495f9e71de92314fb68d
 *
 * Requires Gravity Forms 2.6.2.
 *
 * Plugin Name:  GP Notification Scheduler – Use Payment Status in Conditional Logic
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-notification-scheduler/
 * Description:  Use the entry's payment status in notification conditional logic.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_action( 'admin_footer', function() {
	if ( wp_script_is( 'gform_form_admin' ) && GFForms::get_page() !== 'form_editor' ) :
		$payment_status_choices = array();
		foreach ( GFCommon::get_entry_payment_statuses() as $text => $value ) {
			$payment_status_choices[] = compact( 'text', 'value' );
		}
		$options = array(
			'payment_status' => array(
				'label'     => esc_html__( 'Payment Status', 'gravityforms' ),
				'value'     => 'payment_status',
				'choices'   => $payment_status_choices,
				'operators' => array(
					'is'    => 'is',
					'isnot' => 'isNot',
				),
			),
		);
		?>
		<script>
			const entryOptions = <?php echo json_encode( $options ); ?>;
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
				if ( fieldId === 'payment_status' ) {
					operators = entryOptions.payment_status.operators;
				}
				return operators;
			} );
			gform.addFilter( 'gform_conditional_logic_values_input', function( str, objectType, ruleIndex, selectedFieldId, selectedValue ) {
				if ( selectedFieldId === 'payment_status' ) {
					let inputName = objectType + '_rule_value_' + ruleIndex;
					str = GetRuleValuesDropDown( entryOptions.payment_status.choices, objectType, ruleIndex, selectedValue, inputName );
				}

				return str;
			} );
		</script>
		<?php
	endif;
} );

add_filter( 'gform_rule_source_value', function( $source_value, $rule, $form, $logic, $entry ) {
	if ( $rule['fieldId'] === 'payment_status' && $entry ) {
		// Some payment add-ons do not update the runtime entry but do update the entry in the database. Fetch the
		// latest from the database.
		$entry        = GFAPI::get_entry( $entry['id'] );
		$source_value = rgar( $entry, 'payment_status' );
	}
	return $source_value;
}, 10, 5 );

// @todo Currently, whether to evaluate conditional is applied is not per notification. This might pose issues in some configurations.
add_filter( 'gpns_evaluate_conditional_logic_on_send', function( $eval_on_send, $form, $entry, $notifications ) {

	foreach ( $notifications as $notification ) {
		$_notification = gp_notification_schedule()->get_notification( $form, $notification['nid'] );
		foreach ( rgars( $_notification, 'notification_conditional_logic_object/rules' ) as $rule ) {
			if ( $rule['fieldId'] === 'payment_status' ) {
				return true;
			}
		}
	}

	return $eval_on_send;
}, 10, 4 );
