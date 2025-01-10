/**
 * Gravity Wiz // Gravity Forms // Focus Next Field on Enter
 * https://gravitywiz.com/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
$( document ).on( 'keypress', '.gform_wrapper', function (e) {
    var code = e.keyCode || e.which;
    if ( code == 13 && ! $( e.target ).is( 'textarea,input[type="submit"],input[type="button"]' ) ) {
        e.preventDefault();
		$( e.target )
			.parents( '.gfield' )
			.next( '.gfield' )
			.find( 'input, textarea, select' )
			.filter( ':visible' )
			.first()
			.focus();
        return false;
    }
} );
