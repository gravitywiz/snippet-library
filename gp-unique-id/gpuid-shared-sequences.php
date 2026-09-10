<?php
/**
 * Gravity Perks // Unique ID // Shared Sequences
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * Share sequences between sequential Unique ID fields; works with fields on different forms as well.
 * Supports setting a starting number for the shared sequence and resetting the sequence via the field
 * setting's "reset" link or by bumping `version`.
 *
 * To use the snippet, replace the numbers in the array below to match your form IDs and field IDs.
 * Fields are listed as 'FORMID.FIELDID'.
 * For example, if Form A has an ID of 123 and its Unique ID field's ID is 1 and Form B has an ID of
 * 456 and its Unique ID field's ID is 2, then you would set up the group like so:
 *
 * ```php
 * $groups = array(
 *     array(
 *         'slug'            => 'shared-sequence-1',
 *         'fields'          => array( '123.1', '456.2' ),
 *         'starting_number' => 20,
 *         'version'         => 1,
 *     )
 * );
 * ```
 */
class GPUI_Shared_Sequences {

	protected static $sequences = array(
		array(
			'slug'            => 'shared-sequence-1',
			'fields'          => array( '58.3', '57.3' ),
			'starting_number' => 20,
			'version'         => 1,
		),
	);

	public static function init() {
		add_filter( 'gpui_unique_id_attributes', array( __CLASS__, 'unique_id_attributes' ), 10, 3 );
		add_filter( 'gpui_sequential_unique_id_pre_insert', array( __CLASS__, 'pre_insert' ), 10, 5 );
		add_action( 'wp_ajax_gpui_reset_starting_number', array( __CLASS__, 'ajax_reset' ), 1 );
	}

	protected static function get_sequence( $form_id, $field_id ) {
		foreach ( self::$sequences as $sequence ) {
			foreach ( (array) rgar( $sequence, 'fields' ) as $field ) {
				list( $_form_id, $_field_id ) = array_pad( explode( '.', $field ), 2, null );
				if ( (int) $_form_id === (int) $form_id && (float) $_field_id == (float) $field_id ) {
					return $sequence;
				}
			}
		}

		return false;
	}

	protected static function get_slug( $sequence ) {
		$slug = sprintf( '%s-v%d', rgar( $sequence, 'slug' ), max( 1, (int) rgar( $sequence, 'version' ) ) );

		if ( strlen( $slug ) > 60 ) {
			$slug = substr( $slug, 0, 27 ) . '-' . md5( $slug );
		}

		return $slug;
	}

	protected static function get_starting_number( $sequence ) {
		return max( 1, (int) rgar( $sequence, 'starting_number' ) );
	}

	public static function unique_id_attributes( $atts, $form_id, $field_id ) {

		if ( rgar( $atts, 'type' ) !== 'sequential' ) {
			return $atts;
		}

		$sequence = self::get_sequence( $form_id, $field_id );
		if ( ! $sequence ) {
			return $atts;
		}

		$atts['starting_number'] = self::get_starting_number( $sequence );
		$atts['slug']            = array(
			'form_id'  => 0,
			'field_id' => 0,
			'slug'     => self::get_slug( $sequence ),
		);

		return $atts;
	}

	public static function pre_insert( $uid, $form_id, $field_id, $starting_number ) {
		global $wpdb;

		$sequence = self::get_sequence( $form_id, $field_id );
		if ( ! $sequence ) {
			return $uid;
		}

		$slug            = self::get_slug( $sequence );
		$starting_number = self::get_starting_number( $sequence );

		$wpdb->query(
			$wpdb->prepare(
				'INSERT INTO ' . $wpdb->prefix . 'gpui_sequence ( form_id, field_id, current, slug )
				 VALUES ( 0, 0, ( @next := %d ), %s )
				 ON DUPLICATE KEY UPDATE current = ( @next := IF( current + 1 < %d, %d, current + 1 ) )',
				$starting_number,
				$slug,
				$starting_number,
				$starting_number
			)
		);

		return (int) $wpdb->get_var( 'SELECT @next' );
	}

	public static function ajax_reset() {
		global $wpdb;

		$form_id  = rgpost( 'form_id' );
		$field_id = rgpost( 'field_id' );

		if ( ! wp_verify_nonce( rgpost( 'gpui_reset_starting_number' ), 'gpui_reset_starting_number' ) ) {
			return;
		}

		$sequence = self::get_sequence( $form_id, $field_id );
		if ( ! $sequence ) {
			return;
		}

		if ( ! GFCommon::current_user_can_any( 'gravityforms_edit_forms' ) ) {
			return;
		}

		$slug    = self::get_slug( $sequence );
		$current = self::get_starting_number( $sequence ) - 1;

		$updated = $wpdb->query(
			$wpdb->prepare(
				'INSERT INTO ' . $wpdb->prefix . 'gpui_sequence ( form_id, field_id, current, slug )
				 VALUES ( 0, 0, %d, %s )
				 ON DUPLICATE KEY UPDATE current = %d',
				$current,
				$slug,
				$current
			)
		);

		die( json_encode( array(
			'success' => $updated !== false,
			'message' => $updated !== false
				? sprintf( __( 'Reset to %d!', 'gp-unique-id' ), $current + 1 )
				: __( 'Error resetting.', 'gp-unique-id' ),
		) ) );
	}

}

add_action( 'init', array( 'GPUI_Shared_Sequences', 'init' ) );
