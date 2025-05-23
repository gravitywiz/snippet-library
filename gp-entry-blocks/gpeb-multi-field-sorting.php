<?php
/**
 * Gravity Perks // Entry Blocks // Multi Field Sorting
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Mutli Field Sorting for GP Entry Blocks.
 * 
 * Instruction Video: https://www.loom.com/share/b9867d2735d44519bf563961e9b30bd2
 */
class GPEB_Multi_Field_Sorting {

	private $form_id;
	private $primary_sorting_field_id;
	private $secondary_sorting_field_id;
	private $sorting_direction;

	public function __construct( $config = array() ) {
		$this->form_id                    = rgar( $config, 'form_id' );
		$this->primary_sorting_field_id   = rgar( $config, 'primary_sorting_field_id' );
		$this->secondary_sorting_field_id = rgar( $config, 'secondary_sorting_field_id' );
		$this->sorting_direction          = strtoupper( rgar( $config, 'sorting_direction', 'ASC' ) );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( 'gpeb_queryer_entries', array( $this, 'sort_entries' ), 10, 2 );
	}

	private function is_applicable_form( $current_form_id ) {
		return $current_form_id == $this->form_id;
	}

	public function sort_entries( $entries, $gf_queryer ) {
		if ( ! $this->is_applicable_form( $gf_queryer->form_id ) ) {
			return $entries;
		}

		usort( $entries, array( $this, 'sort_callback' ) );

		return $entries;
	}

	private function sort_callback( $a, $b ) {
		$a_primary = rgar( $a, $this->primary_sorting_field_id, '' );
		$b_primary = rgar( $b, $this->primary_sorting_field_id, '' );

		$cmp = strcasecmp( $a_primary, $b_primary );

		if ( $cmp === 0 && $this->secondary_sorting_field_id ) {
			$a_secondary = rgar( $a, $this->secondary_sorting_field_id, '' );
			$b_secondary = rgar( $b, $this->secondary_sorting_field_id, '' );
			$cmp = strcasecmp( $a_secondary, $b_secondary );
		}

		return ( $this->sorting_direction === 'DESC' ) ? -$cmp : $cmp;
	}
}

new GPEB_Multi_Field_Sorting( array(
	'form_id'                    => 933,
	'primary_sorting_field_id'   => '1.6',
	'secondary_sorting_field_id' => '1.3',
	'sorting_direction'          => 'ASC', // or 'DESC'
) );
