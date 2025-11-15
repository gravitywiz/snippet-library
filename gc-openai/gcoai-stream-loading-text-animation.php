<?php
/**
 * Gravity Connect // OpenAI // Stream Loading Text Animation
 *
 * Adds a customizable shimmer animation and rotating spinner icon to the Stream field's 
 * loading placeholders. Replaces the static "Loading..." text with animated text and/or 
 * a rotating spinner icon.
 */
class GCOAI_Loading_Animation {

	private $args;

	public function __construct( $args = array() ) {
		$this->args = wp_parse_args( $args, array(
			'text'             => 'Thinking...',
			'base_color'       => '#000',
			'shimmer_color'    => '#fff',
			'shimmer_duration' => '2.2s',
			'show_shimmer'     => true,
			'show_spinner'     => false,
			'spinner_size'     => '24',
			'form_id'          => null,
			'field_id'         => null,
		) );

		add_filter( 'gform_gcoai_field_loading_text', array( $this, 'filter_loading_text' ), 10, 3 );
		add_action( 'gform_register_init_scripts', array( $this, 'register_init_script' ), 10, 2 );
	}

	public function register_init_script( $form, $is_ajax ) {
		if ( empty( $form['id'] ) ) {
			return;
		}
		
		// If form_id is specified, only run scripts on those forms
		if ( $this->args['form_id'] !== null ) {
			$form_ids = is_array( $this->args['form_id'] ) ? $this->args['form_id'] : array( $this->args['form_id'] );
			if ( ! in_array( $form['id'], $form_ids ) ) {
				return;
			}
		}

		$markup = $this->get_shimmer_markup();
		$css    = $this->get_styles_css();
		
		?>
		<script type="text/javascript">
		(function($) {
			var shimmerMarkup = <?php echo wp_json_encode( $markup ); ?>;
			var shimmerStyles = <?php echo wp_json_encode( $css ); ?>;

			function addStylesToPage() {
				if ( ! $('style.gw-gcoai-shimmer-style').length ) {
					$('<style>')
						.addClass('gw-gcoai-shimmer-style')
						.text(shimmerStyles)
						.appendTo('head');
				}
			}

			function applyShimmerToPlaceholders($container) {
				var $searchContext = $container && $container.length ? $container : $(document);
				$searchContext.find('.gcoai-output .gcoai-placeholder').html(shimmerMarkup);
			}

			if ( window.gform && typeof window.gform.addFilter === 'function' ) {
				window.gform.addFilter('gcoai_stream_loading_placeholder', function(current, instance) {
					return shimmerMarkup;
				});
			}

			$(function() {
				addStylesToPage();
				applyShimmerToPlaceholders();
			});

			// Re-apply after Generate/Regenerate clicks
			$(document).on('click', '.gcoai-trigger, .gcoai-regenerate', function() {
				setTimeout(function() {
					applyShimmerToPlaceholders();
				}, 50);
			});

			// Re-apply after AJAX completes
			$(document).ajaxComplete(function() {
				applyShimmerToPlaceholders();
			});
		})(jQuery);
		</script>
		<?php
	}

	public function get_shimmer_markup() {
		$spinner = '';
		
		if ( $this->args['show_spinner'] ) {
			$spinner = sprintf(
				'<svg class="shimmer-spinner" xmlns="http://www.w3.org/2000/svg" width="%s" height="%s" stroke="%s" viewBox="0 0 24 24">
					<g class="spinner-rotate">
						<circle cx="12" cy="12" r="9.5" fill="none" stroke-width="1.5"/>
					</g>
				</svg>',
				esc_attr( $this->args['spinner_size'] ),
				esc_attr( $this->args['spinner_size'] ),
				esc_attr( $this->args['base_color'] )
			);
		}

		$text_class = $this->args['show_shimmer'] ? 'shimmer' : 'shimmer-text';
		
		return sprintf(
			'<span class="shimmer-wrapper">%s<span class="%s">%s</span></span>',
			$spinner,
			$text_class,
			esc_html( $this->args['text'] )
		);
	}

	public function filter_loading_text( $placeholder, $field, $form = null ) {
		if ( ! class_exists( '\\GC_OpenAI\\Fields\\Stream' ) || ! $field instanceof \GC_OpenAI\Fields\Stream ) {
			return $placeholder;
		}
		
		// If form_id is specified, only apply to those forms
		if ( $this->args['form_id'] !== null ) {
			$form_ids = is_array( $this->args['form_id'] ) ? $this->args['form_id'] : array( $this->args['form_id'] );
			if ( $form && ! in_array( rgar( $form, 'id' ), $form_ids ) ) {
				return $placeholder;
			}
		}
		
		// If field_id is specified, only apply to those fields
		if ( $this->args['field_id'] !== null ) {
			$field_ids = is_array( $this->args['field_id'] ) ? $this->args['field_id'] : array( $this->args['field_id'] );
			if ( ! in_array( rgar( $field, 'id' ), $field_ids ) ) {
				return $placeholder;
			}
		}
		
		return $this->get_shimmer_markup();
	}

	private function get_styles_css() {
		$base    = esc_attr( $this->args['base_color'] );
		$shimmer = esc_attr( $this->args['shimmer_color'] );
		$dur     = esc_attr( $this->args['shimmer_duration'] );
		
		return 
			".shimmer-wrapper { display: inline-flex; align-items: center; gap: 8px; } " .
			".shimmer-spinner { flex-shrink: 0; } " .
			".spinner-rotate { transform-origin: center; animation: spinner-rotation 2s linear infinite; } " .
			".spinner-rotate circle { stroke-linecap: round; animation: spinner-stroke 1.5s ease-in-out infinite; } " .
			"@keyframes spinner-rotation { 100% { transform: rotate(360deg); } } " .
			"@keyframes spinner-stroke { 0% { stroke-dasharray: 0 150; stroke-dashoffset: 0; } 47.5% { stroke-dasharray: 42 150; stroke-dashoffset: -16; } 95%, 100% { stroke-dasharray: 42 150; stroke-dashoffset: -59; } } " .
			".shimmer-text { display: inline-block; color: {$base}; line-height: 1.2; } " .
			".shimmer { display: inline-block; color: {$base}; line-height: 1.2; background: {$base} linear-gradient(to left, {$base}, {$shimmer} 50%, {$base}); background-position: -4rem top; background-repeat: no-repeat; background-size: 4rem 100%; -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; animation: shimmer {$dur} infinite; } " .
			"@keyframes shimmer { 0% { background-position: -4rem top; } 70%, 100% { background-position: 12.5rem top; } }";
	}
}

# Configuration

new GCOAI_Loading_Animation( array(
	'text'             => 'Thinking...',
	'base_color'       => '#292929',
	'shimmer_color'    => '#fff',
	'shimmer_duration' => '2.2s',
	'show_shimmer'     => true,
	'show_spinner'     => true,
	'spinner_size'     => '16',
	// 'form_id'       => 123, // Uncomment and set to target specific form(s): 123 or array( 18, 22, 35 )
	// 'field_id'      => 4,  // Uncomment and set to target specific field(s): 5 or array( 5, 7, 12 )
) );
