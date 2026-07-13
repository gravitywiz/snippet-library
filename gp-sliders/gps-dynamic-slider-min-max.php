<?php
/**
 * Gravity Perks // Sliders // Dynamically Set Slider Min/Max From Field Value
 * https://gravitywiz.com/documentation/gravity-forms-sliders/
 *
 * Set a Slider field's minimum and/or maximum to the value of other fields on the same form.
 *
 * Works with Slider fields in numeric mode and with Number/Quantity fields using the
 * "Enable Slider" setting (leave the Number field's native Range setting empty).
 * Choices-mode sliders are not supported. Any field with a numeric value can be a source.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the configuration at the bottom of the snippet.
 */
class GPS_Dynamic_Slider_Range {

	private $_args;

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'             => false,
			'slider_field_id'     => false,
			'min_source_field_id' => false,
			'max_source_field_id' => false,
		) );

		add_filter( 'gform_pre_render', array( $this, 'apply_dynamic_range' ) );
		add_filter( 'gform_pre_validation', array( $this, 'apply_dynamic_range' ) );

	}

	public function apply_dynamic_range( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		if ( ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		foreach ( $form['fields'] as $field ) {

			// Number/Quantity slider fields may not store sliderMode, so exclude choices mode rather than require numeric.
			if ( (int) $field->id !== (int) $this->_args['slider_field_id'] || $field->sliderMode === 'choices' ) {
				continue;
			}

			$min = $this->get_posted_value( $this->_args['min_source_field_id'] );
			$max = $this->get_posted_value( $this->_args['max_source_field_id'] );

			if ( $min !== null ) {
				$field->sliderMin = $min;
			}

			if ( $max !== null ) {
				$field->sliderMax = max( (float) $field->sliderMin, $max );
			}
		}

		return $form;
	}

	private function get_posted_value( $field_id ) {

		if ( ! $field_id ) {
			return null;
		}

		$value = rgpost( "input_{$field_id}" );

		if ( ! is_numeric( $value ) ) {
			$value = rgpost( "input_{$field_id}_1" );
		}

		return is_numeric( $value ) ? (float) $value : null;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			( function ( $ ) {

				window.GPSDynamicSliderRange = function ( args ) {

					var self = this;

					self.sources = {
						min: document.getElementById( 'field_' + args.formId + '_' + args.minSourceFieldId ),
						max: document.getElementById( 'field_' + args.formId + '_' + args.maxSourceFieldId ),
					};

					self.getSlider = function () {
						var forms = window.gpsSliderFields || {};
						return forms[ args.formId ] && forms[ args.formId ][ args.sliderFieldId ];
					};

					self.getSourceValue = function ( container ) {
						var input = container && container.querySelector( 'input, select, textarea' );
						var value = input ? parseFloat( input.value ) : NaN;

						return isNaN( value ) ? null : value;
					};

					self.rebuildTicks = function ( slider ) {
						var config = slider.config;
						var ticks  = slider.container.querySelector( '.gps-slider__ticks' );

						if ( ! ticks ) {
							return;
						}

						ticks.innerHTML = '';

						config.snaps.forEach( function ( snap ) {
							var tick = document.createElement( 'span' );
							tick.className = 'gps-slider__tick';
							tick.style[ config.orientation === 'vertical' ? 'bottom' : 'left' ] = snap.percent + '%';

							if ( config.showTickLabels ) {
								var label = document.createElement( 'span' );
								label.className   = 'gps-slider__tick-label';
								label.textContent = config.prefix + slider.formatValue( snap.value ) + config.suffix;
								tick.appendChild( label );
							}

							ticks.appendChild( tick );
						} );
					};

					self.applyRange = function () {

						var slider = self.getSlider();
						if ( ! slider || slider.config.mode !== 'numeric' ) {
							return;
						}

						var config = slider.config;
						var step   = parseFloat( config.step );

						if ( ! ( step > 0 ) ) {
							return;
						}

						if ( ! self.defaults ) {
							self.defaults = { min: parseFloat( config.min ), max: parseFloat( config.max ) };
						}

						var minSource = self.getSourceValue( self.sources.min );
						var maxSource = self.getSourceValue( self.sources.max );

						var min = minSource !== null ? minSource : self.defaults.min;
						var max = Math.max( min, maxSource !== null ? maxSource : self.defaults.max );

						var snaps = [];

						for ( var value = min; value <= max + 1e-9; value += step ) {
							value = Math.round( value * 1e10 ) / 1e10;
							snaps.push( { value: value, percent: max > min ? ( ( value - min ) / ( max - min ) ) * 100 : 0 } );
						}

						if ( Math.abs( snaps[ snaps.length - 1 ].percent - 100 ) > 0.001 ) {
							snaps.push( { value: max, percent: 100 } );
						}

						config.min   = min;
						config.max   = max;
						config.snaps = snaps;

						self.rebuildTicks( slider );

						[ 'min', 'max' ].forEach( function ( handle ) {
							if ( handle === 'max' && config.selectionType !== 'range' ) {
								return;
							}

							var value   = Math.min( Math.max( slider.getValue( handle ), min ), max );
							var percent = max > min ? ( ( value - min ) / ( max - min ) ) * 100 : 0;

							slider.setValue( handle, slider.percentToValue( percent ) );
							slider.handles[ handle ].setAttribute( 'aria-valuemin', String( min ) );
							slider.handles[ handle ].setAttribute( 'aria-valuemax', String( max ) );
						} );
					};

					[ self.sources.min, self.sources.max ].forEach( function ( container ) {
						if ( container ) {
							container.addEventListener( 'input', self.applyRange );
							container.addEventListener( 'change', self.applyRange );
						}
					} );

					self.applyRange();
				};

				$( document ).on( 'gform_post_render', function ( event, formId ) {
					if ( formId == <?php echo (int) $this->_args['form_id']; ?> ) {
						// GP Sliders creates its slider instances on this same event; defer a tick so it runs first.
						setTimeout( function () {
							new GPSDynamicSliderRange( <?php echo json_encode( array(
								'formId'           => (int) $this->_args['form_id'],
								'sliderFieldId'    => (int) $this->_args['slider_field_id'],
								'minSourceFieldId' => (int) $this->_args['min_source_field_id'],
								'maxSourceFieldId' => (int) $this->_args['max_source_field_id'],
							) ); ?> );
						} );
					}
				} );

			} )( jQuery );

		</script>

		<?php
	}

	public function is_applicable_form( $form ) {
		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return (int) $form_id === (int) $this->_args['form_id'];
	}

}

# Configuration

new GPS_Dynamic_Slider_Range( array(
	'form_id'             => 123,
	'slider_field_id'     => 4,
	'min_source_field_id' => 2, // (Optional) Omit to keep the slider's configured min.
	'max_source_field_id' => 1, // (Optional) Omit to keep the slider's configured max.
) );
