/**
 * Experimental Snippet 🧪
 * By default, GPRF scopes it's replacement to a static container. If the user is including multiple instances of the same form 
 * on the same page (which GF does not support by default), all instances of the form will be submitted but only the submitted
 * instance will be reloaded. Use this snippet to reload all forms of the same ID.
 */
gform.addFilter( 'gprf_replacing_elem', function( $replacingElem, formId ) { 
    return jQuery( '#gform_confirmation_wrapper_' + formId + ', .gform_confirmation_message_' + formId + ', #gform_wrapper_' + formId );
} );
