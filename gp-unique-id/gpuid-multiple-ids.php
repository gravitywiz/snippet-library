<?php
/**
 * Gravity Perks // GP Unique ID // Generate Multiple Unique IDs per Submission (for Gravity Forms)
 *
 * Generate multiple unique IDs. Does not work with sequential IDs.
 *
 * @version   1.2
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/
 */
class GP_Unique_ID_Multiple_IDs {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'         => false,
			'target_field_id' => false,
			'source_field_id' => false,
			'count'           => 0,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) || ! function_exists( 'gp_unique_id' ) ) {
			return;
		}

		add_action( 'gform_entry_post_save', array( $this, 'populate_field_value' ), 10, 2 );
		add_action( 'gpui_check_unique_query', array( $this, 'reinforce_check_unqiue_query' ), 10, 4 );

	}

	function populate_field_value( $entry, $form ) {

		foreach ( $form['fields'] as $field ) {

			if ( $this->is_applicable_field( $field, $form, $entry ) ) {

				$count        = $this->get_count( $entry );
				$source_field = GFFormsModel::get_field( $form, $this->_args['source_field_id'] );
				$value        = array();

				// add source field ID as first of the X IDs
				$value[] = rgar( $entry, $source_field['id'] );

				for ( $i = 1; $i < $count; $i++ ) {
					$value[] = gp_unique_id()->get_unique( $form['id'], $source_field );
				}

				//$value = $this->save_value_to_entry( $entry['id'], $form['id'], $field, implode( "\n", $value ) );

				$entry[ $field['id'] ] = implode( "\n", $value );

				GFAPI::update_entry( $entry );

			}
		}

		return $entry;
	}

	function save_value_to_entry( $entry_id, $form_id, $field, $value ) {
		global $wpdb;

		$result = $wpdb->insert(
			GFFormsModel::get_lead_details_table_name(),
			array(
				'lead_id'      => $entry_id,
				'form_id'      => $form_id,
				'field_number' => $field['id'],
				'value'        => $value,
			),
			array( '%d', '%d', '%d', '%s' )
		);

		return $result ? $value : false;
	}

	function get_count( $entry ) {

		$count = $this->_args['count'];

		if ( is_int( $count ) ) {
			// do nothing
		} elseif ( is_array( $count ) ) {

			if ( isset( $count['choices'] ) ) {
				$value = call_user_func( 'array_shift', explode( '|', rgar( $entry, $count['field_id'] ) ) );
				$count = rgar( $count['choices'], $value );
			} else {
				$count = rgar( $entry, $count['field_id'] );
			}
		} else {
			$count = 0;
		}

		return $count;
	}

	function is_applicable_field( $field, $form, $entry ) {

		$is_form            = $form['id'] == $this->_args['form_id'];
		$is_target_field_id = $field['id'] == $this->_args['target_field_id'];
		$is_visible         = ! GFFormsModel::is_field_hidden( $form, $field, array(), $entry );

		return $is_form && $is_target_field_id && $is_visible;
	}

	function reinforce_check_unqiue_query( $query, $form_id, $field_id, $unique ) {

		$search  = "AND ld.value = '{$unique}'";
		$replace = "AND ld.value LIKE '%{$unique}%'";

		$query = str_replace( $search, $replace, $query );

		return $query;
	}

}

# Configuration

//new GP_Unique_ID_Multiple_IDs( array(
//   'form_id'         => 694,
//   'source_field_id' => 35,
//   'target_field_id' => 36,
//   'count'           => 2
//) );

//new GP_Unique_ID_Multiple_IDs( array(
//	'form_id'         => 753,
//	'source_field_id' => 1,
//	'target_field_id' => 2,
//	'count'           => array(
//		'field_id' => 3,
//	),
//) );

// new GP_Unique_ID_Multiple_IDs( array(
//     'form_id'         => 694,
//     'target_field_id' => 36,
//     'source_field_id' => 35,
//     'count'           => array(
//         'field_id' => 33,
//         'choices'  => array(
//             'Hole in One Sponsor ($25,000)'  => 12,
//             'Albatross Sponsor ($12,500)'    => 8,
//             'Eagle Sponsor ($6,500)'         => 4,
//             'Birdie Sponsor ($4,500)'        => 2,
//             'Par Sponsor ($2,000)'           => 1,
//             'Digital Media Sponsor ($1,000)' => 0,
//             'Chip-In Sponsor ($500)'         => 0
//         )
//     )
// ) );
