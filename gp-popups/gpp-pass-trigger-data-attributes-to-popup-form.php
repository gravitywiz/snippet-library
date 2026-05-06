<?php
/**
 * Gravity Perks // Popups // Pass Trigger Data Attributes to Popup Form
 * https://gravitywiz.com/documentation/gravity-forms-popups/
 *
 * Pass `data-*` attributes from popup trigger buttons/links into a popup form using
 * Gravity Forms dynamic population.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the configuration at the bottom of the snippet:
 *    - Add the popup feed IDs you want to support.
 *    - For each feed, map the trigger element's `data-*` attribute suffix to the popup
 *      form's dynamic population parameter name.
 */
class GW_Popup_Trigger_Data {

	private $_args = array();

	public function __construct( $args = array() ) {
		$this->_args = wp_parse_args( $args, array(
			'popup_feeds' => array(),
		) );

		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_script' ), 20 );
	}

	public function maybe_enqueue_script() {
		if ( empty( $this->_args['popup_feeds'] ) ) {
			return;
		}

		if ( ! wp_script_is( 'gp_popups_frontend', 'enqueued' ) ) {
			return;
		}

		wp_localize_script( 'gp_popups_frontend', 'gpPopupsTriggerData', array(
			'feedDataMaps' => $this->_args['popup_feeds'],
		) );

		wp_add_inline_script( 'gp_popups_frontend', $this->get_inline_script(), 'after' );
	}

	private function get_inline_script() {
		return <<<'JS'
(function() {
	var feedDataMaps = gpPopupsTriggerData.feedDataMaps;
	var lastTriggerValuesByFeedId = {};
	var feedSelectors = {};

	window.gpPopupsConfig.forEach( function( popupConfig ) {
		feedSelectors[ String( popupConfig.feedId ) ] = popupConfig.trigger.selector;
	} );

	document.addEventListener(
		'click',
		function( event ) {
			Object.keys( feedDataMaps ).forEach( function( feedId ) {
				var selector = feedSelectors[ feedId ];
				if ( ! selector ) {
					return;
				}

				var trigger = event.target.closest( selector );
				if ( ! trigger ) {
					return;
				}

				var values = {};
				var dataMap = feedDataMaps[ feedId ];

				Object.keys( dataMap ).forEach( function( dataAttr ) {
					var value = trigger.getAttribute( 'data-' + dataAttr );
					if ( value !== null && value !== '' ) {
						values[ dataMap[ dataAttr ] ] = value;
					}
				} );

				lastTriggerValuesByFeedId[ feedId ] = values;
			} );
		},
		true
	);

	document.addEventListener( 'gp_popup_closed', function( event ) {
		var feedId = event && event.detail ? String( event.detail.feedId || '' ) : '';
		if ( feedId ) {
			delete lastTriggerValuesByFeedId[ feedId ];
		}
	} );

	window.gform.addFilter( 'gpp_popup_config', function( config ) {
		var triggerValues = lastTriggerValuesByFeedId[ String( config.feedId ) ];
		if ( ! triggerValues || ! Object.keys( triggerValues ).length ) {
			return config;
		}

		var url = new URL( config.iframeUrl, window.location.href );

		Object.keys( triggerValues ).forEach( function( paramName ) {
			url.searchParams.set( paramName, triggerValues[ paramName ] );
		} );

		config.iframeUrl = url.toString();

		return config;
	} );
} )();
JS;
	}

}

# Configuration

new GW_Popup_Trigger_Data( array(
	// Feed ID => array( 'data attribute suffix' => 'population parameter' )
	'popup_feeds' => array(
		123 => array(
			'your-data-suffix' => 'your_population_parameter',
			// Add more mappings as needed.
		),
	),
) );
