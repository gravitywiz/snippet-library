<?php
/**
* Gravity Wiz // Exclude characters from GPUID
*
* Allows you to exclude characters from unique IDs generated using GPUID.
*
* @version   1.2
* @author    Eihab Ibrahim <eihab@gravitywiz.com>
* @license   GPL-2.0+
* @link      http://gravitywiz.com/calculate-number-of-days-between-two-dates/
* @copyright 2020 Gravity Wiz
*/
class GWExcludeUIDChars {
	/**
	 * GWExcludeUIDChars Constructor
	 *
	 * Expects an $args named array with the following keys=>values:
	 * 'form_id':  ID of the GF form to perform replacements in
	 * 'field_id': GPUID Input ID
	 * 'exclude':  String containing characters to exclude
	 * 'sitewide': [optional] Boolean, set to true to apply to all GPUIDs
	 *
	 * Example usage in functions.php:
	 * new GWExcludeUIDChars( array(
	 *     'form_id'  => 11,
	 *     'field_id' => 1,
	 *     'exclude'  => 'li0o'
	 * ) );
	 */
	public function __construct( $args ) {
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
			'exclude'  => false,
			'sitewide' => false,
		) ) );
		if ( ! $form_id || ! $field_id || ! $exclude ) {
			if ( ! $sitewide || ! $exclude ) {
				return;
			}
		}
		$this->form_id  = $form_id;
		$this->field_id = $field_id;
		$this->sitewide = $sitewide;
		$this->exclude  = str_split( $exclude );

		add_filter( 'gpui_unique_id', array( &$this, 'exclude_action' ), 10, 3 );
	}

	public function exclude_action( $unique, $form_id, $field_id ) {
		if ( ! $this->sitewide && ( intval( $form_id ) !== $this->form_id || intval( $field_id ) !== $this->field_id ) ) {
			return $unique;
		}

		// Setup valid replacements array
		$alphanumeric        = array_merge( range( 'a', 'z' ), range( 0, 9 ) );
		$alphanumeric        = array_values( array_diff( $alphanumeric, $this->exclude ) );
		$alphanumeric_length = count( $alphanumeric );
		$unique              = str_split( $unique );

		for ( $i = 0, $max = count( $unique ); $i < $max; $i++ ) {
			// Replace excluded characters with a random valid substitute
			if ( in_array( $unique[ $i ], $this->exclude, true ) ) {
				$unique[ $i ] = $alphanumeric[ rand( 0, $alphanumeric_length ) ];
			}
		}

		return join( $unique );
	}
}

// Uncomment and customize/duplicate the following lines:
// new GWExcludeUIDChars( array(
//	'form_id'  => 11,
//	'field_id' => 1,
//	'sitewide' => false,
//	'exclude'  => 'li0o'
// ) );
// To apply exclusions site-wide use the following:
// new GWExcludeUIDChars( array(
//	'sitewide' => true,
//	'exclude'  => 'li0o'
// ) );
