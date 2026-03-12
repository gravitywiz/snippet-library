<?php
/**
 * GPLD Soft Min/Max Warning
 *
 * Converts GP Limit Dates hard min/max restrictions into soft warnings.
 */
class GPLD_Soft_Min_Max_Warning {

	private $form_ids = array();

	public function __construct( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'form_id' => array(),
		) );

		$this->form_ids = (array) $args['form_id'];

		add_filter( 'gform_field_validation', array( $this, 'soften_validation' ), 20, 4 );
		add_filter( 'gform_confirmation', array( $this, 'add_confirmation_warning' ), 10, 4 );
		add_action( 'gform_enqueue_scripts', array( $this, 'enqueue_script' ), 10, 2 );

	}

	private function is_applicable_form( $form ) {

		if ( empty( $this->form_ids ) ) {
			return true;
		}

		return in_array( rgar( $form, 'id' ), $this->form_ids );
	}

	public function soften_validation( $result, $value, $form, $field ) {

		if ( ! $this->is_applicable_form( $form ) ||  ! function_exists( 'gp_limit_dates' ) || $field->get_input_type() !== 'date' || ! gp_limit_dates()->has_limit_dates_enabled( $field ) ) {
			return $result;
		}

		if ( $result['is_valid'] === false ) {

			$result['is_valid'] = true;

			if ( ! isset( $GLOBALS['gpld_soft_warnings'] ) ) {
				$GLOBALS['gpld_soft_warnings'] = array();
			}

			$GLOBALS['gpld_soft_warnings'][] = sprintf(
				'Warning: The date in "%s" is outside the limit dates range.',
				rgar( $field, 'label' )
			);

		}

		return $result;
	}

	public function add_confirmation_warning( $confirmation, $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $confirmation;
		}

		if ( empty( $GLOBALS['gpld_soft_warnings'] ) ) {
			return $confirmation;
		}

		$warnings = '<div class="gform_validation_message gform_warning"><ul>';

		foreach ( $GLOBALS['gpld_soft_warnings'] as $warning ) {
			$warnings .= '<li>' . esc_html( $warning ) . '</li>';
		}

		$warnings .= '</ul></div>';

		if ( is_array( $confirmation ) ) {
			$confirmation['message'] = $warnings . $confirmation['message'];
			return $confirmation;
		}

		return $warnings . $confirmation;
	}

	public function enqueue_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		add_action( 'wp_footer', array( $this, 'print_script' ) );
	}

	public function print_script() {
		?>

		<style>
		.gpld-date-out-of-range {
			border-color: #dc3545 !important;
		}
		.gpld-date-warning {
			color: #dc3545;
			font-size: 13px;
			margin-top: 4px;
		}
		</style>

		<script>
		(function($){

			var softMinMax = function( $input ) {

				try {

					var minDate = $input.datepicker('option','minDate');
					var maxDate = $input.datepicker('option','maxDate');

					$input.datepicker('option','minDate',null);
					$input.datepicker('option','maxDate',null);

					var normalize = function(d){
						return d ? new Date(d.getFullYear(), d.getMonth(), d.getDate()) : null;
					};

					minDate = normalize(minDate);
					maxDate = normalize(maxDate);

					var $warning = $input.next('.gpld-date-warning');

					if ( ! $warning.length ) {
						$warning = $('<div class="gpld-date-warning">Warning: you are selecting a date beyond bounds.</div>').hide();
						$input.after($warning);
					}

					var validate = function(){

						var selectedDate = normalize($input.datepicker('getDate'));

						$input.removeClass('gpld-date-out-of-range');

						if ( ! selectedDate ) {
							$warning.hide();
							return;
						}

						var outOfRange =
							( minDate && selectedDate < minDate ) ||
							( maxDate && selectedDate > maxDate );

						if ( outOfRange ) {
							$input.addClass('gpld-date-out-of-range');
							$warning.show();
						} else {
							$warning.hide();
						}

					};

					$input.on('change', validate);
					$input.datepicker('option','onSelect', validate);

				} catch(e) {}

			};

			gform.addAction('gpld_after_set_min_date', softMinMax);
			gform.addAction('gpld_after_set_max_date', softMinMax);

		})(jQuery);
		</script>

		<?php
	}

}

# Configuration
new GPLD_Soft_Min_Max_Warning( array(
	'form_id' => 119
) );
