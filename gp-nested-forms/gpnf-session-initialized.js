/**
 * Gravity Perks // Nested Forms // Populate session hash into a field
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
gform.addAction( 'gpnf_session_initialized', function() {

    // Update 123 to your form ID.
    var formId = 123;
    // Update 4 to the ID of the field you wish to populate.
    var fieldId = 4;

    var gpnfCookie;
    var cookieArr = document.cookie.split( ';' );
    for( var i = 0; i &lt; cookieArr.length; i++ ) {
        var cookiePair = cookieArr[ i ].split( &#039;=&#039; );
            if ( name == cookiePair[0].trim() ) {
                gpnfCookie = $.parseJSON( decodeURIComponent( cookiePair[1] ) );
            }
        }
    }

    $( &#039;#input_&#039; + formId + &#039;_&#039; + fieldId ).val( gpnfCookie.hash );

} );
