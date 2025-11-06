<?php
/**
 * Gravity Perks // Limit Dates // Limit Year Dropdown Options
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Limits year dropdown options based on configured min/max dates in GP Limit Dates.
 * Note: min/max rules that reference other date fields are not supported.
 */

class GPLD_Limit_Year_Dropdown {
	protected $form_id;
	protected $field_id;

	public function __construct( $args = array() ) {
		$args = array_merge(
			array(
				'form_id'  => null,
				'field_id' => null,
			),
			$args
		);

		$this->form_id  = $args['form_id'];
		$this->field_id = $args['field_id'];

		add_filter( 'gform_date_min_year', array( $this, 'filter_min_year' ), 10, 3 );
		add_filter( 'gform_date_max_year', array( $this, 'filter_max_year' ), 10, 3 );
	}

	public function filter_min_year( $min_year, $form, $field ) {
		return $this->filter_year( 'min', $min_year, $form, $field );
	}

	public function filter_max_year( $max_year, $form, $field ) {
		return $this->filter_year( 'max', $max_year, $form, $field );
	}

	protected function filter_year( $type, $default_year, $form, $field ) {
		if ( ! $this->is_applicable( $form, $field ) ) {
			return $default_year;
		}

		$options = gp_limit_dates()->get_limit_dates_field_options( $field );
		$date_key = $type . 'Date';
		$mod_key = $type . 'DateMod';

		if ( empty( $options[ $date_key ] ) ) {
			return $default_year;
		}

		$date_value = $options[ $date_key ];
		$modifier = rgar( $options, $mod_key );

		if ( $date_value == '{today}' ) {
			$timestamp = strtotime( 'today midnight' );
		} elseif ( is_numeric( $date_value ) && $date_value > 0 ) {
			return $default_year;
		} else {
			$timestamp = strtotime( $date_value );
		}

		if ( $timestamp && ! empty( $modifier ) ) {
			$timestamp = strtotime( $modifier, $timestamp );
		}

		if ( $timestamp ) {
			$calculated_year = date( 'Y', $timestamp );
			return $type === 'min' ? max( $calculated_year, $default_year ) : min( $calculated_year, $default_year );
		}

		return $default_year;
	}

	protected function is_applicable( $form, $field ) {
		if ( ! function_exists( 'gp_limit_dates' ) || $field->dateType !== 'datedropdown' ) {
			return false;
		}

		$form_id = rgar( $form, 'id' );

		if ( null !== $this->form_id && intval( $this->form_id ) !== intval( $form_id ) ) {
			return false;
		}

		if ( null !== $this->field_id && intval( $this->field_id ) !== intval( $field->id ) ) {
			return false;
		}

		return true;
	}
}

# Configuration

new GPLD_Limit_Year_Dropdown(array(
	'form_id'  => 123,
	'field_id' => 4,
));
