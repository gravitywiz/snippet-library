/**
 * Gravity Perks // Populate Anything // Slow Down Keyup Listener
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Currently all events are triggered on a 250ms debounce. Use this snippet to slow down keyup triggers
 * so users have more time to type before Populate Anything triggers a population request.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
var forceReloadTimeouts = {};
gform.addFilter( 'gppa_should_trigger_change', function( triggerChange, formId, inputId, $el, event ) {
	console.log( event.type );
	if ( formId != GFFORMID ) {
		return triggerChange;
	}
	if ( typeof forceReloadTimeouts[ inputId ] ) {
		clearTimeout( forceReloadTimeouts[ inputId ] );
	}
	if ( event.type === 'keyup' ) {
		triggerChange = false;
		forceReloadTimeouts[ inputId ] = setTimeout( function() {
			console.log( 'force reloading...' );
			$el.trigger( 'forceReload.gppa' );
		}, 500 );
	}
	return triggerChange;
} )
