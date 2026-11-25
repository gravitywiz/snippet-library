<?php
/**
 * GP Nested Forms // WP Fusion // Delay child feeds until the parent's payment is confirmed
 *
 * WP Fusion processes child entry feeds from the parent form's gform_after_submission
 * (WPF_Gravity_Forms::process_nested_forms()), before the payment outcome is known. Its payment-status
 * gate cannot help: it reads the child entry's payment_status, and WP Fusion only offers that setting
 * on feeds belonging to a form that has a payment feed - which a child form never does, so the setting
 * is absent from the child feed and the gate is skipped entirely.
 *
 * This snippet moves WP Fusion's child feed processing from submission time to payment completion, so
 * child entries follow the parent's payment. Forms without a payment feed are untouched.
 */
add_action( 'gform_after_submission', function ( $entry, $form ) {

	$integration = gpnf_wpf_integration();

	if ( ! $integration || ! gp_nested_forms()->has_nested_form_field( $form ) || ! gpnf_wpf_form_has_payment_feed( $form ) ) {
		return;
	}

	// Let the payment resolve first; gform_post_payment_action below runs the child feeds.
	remove_action( 'gform_after_submission', array( $integration, 'process_nested_forms' ), 10 );

}, 9, 2 );

// Restore the hook so other forms in this request are unaffected.
add_action( 'gform_after_submission', function () {

	$integration = gpnf_wpf_integration();

	if ( $integration && ! has_action( 'gform_after_submission', array( $integration, 'process_nested_forms' ) ) ) {
		add_action( 'gform_after_submission', array( $integration, 'process_nested_forms' ), 10, 2 );
	}

}, 11 );

/**
 * Once the parent payment completes, run the child feeds. Priority 11 keeps WP Fusion's own order:
 * the parent's post-payment feeds (priority 10) first, then the children.
 */
add_action( 'gform_post_payment_action', function ( $entry, $action ) {

	if ( ! in_array( rgar( $action, 'type' ), array( 'complete_payment', 'create_subscription' ), true ) ) {
		return;
	}

	$integration = gpnf_wpf_integration();
	if ( ! $integration ) {
		return;
	}

	$form = GFAPI::get_form( $entry['form_id'] );
	if ( ! $form || ! gp_nested_forms()->has_nested_form_field( $form ) ) {
		return;
	}

	$integration->process_nested_forms( $entry, $form );

}, 11, 2 );

/**
 * WP Fusion's Gravity Forms integration, or false.
 */
function gpnf_wpf_integration() {

	if ( ! function_exists( 'wp_fusion' ) || ! function_exists( 'gp_nested_forms' ) ) {
		return false;
	}

	$wp_fusion = wp_fusion();

	if ( ! property_exists( $wp_fusion, 'integrations' ) || ! property_exists( $wp_fusion->integrations, 'gravity-forms' ) ) {
		return false;
	}

	return $wp_fusion->integrations->{'gravity-forms'};
}

/**
 * Whether a payment add-on has a feed on this form.
 *
 * WP Fusion's own form_has_payments() caches its result in a single property for the whole request,
 * regardless of form ID, so it is not safe to call here.
 */
function gpnf_wpf_form_has_payment_feed( $form ) {

	foreach ( GFAddon::get_registered_addons() as $addon ) {
		$addon = call_user_func( array( $addon, 'get_instance' ) );
		if ( $addon instanceof GFPaymentAddOn && $addon->has_feed( $form['id'] ) ) {
			return true;
		}
	}

	return false;
}
