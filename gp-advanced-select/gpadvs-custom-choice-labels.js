/**
 * Gravity Perks // Advanced Select // Custom Choice Labels
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 * 
 * Instruction Video: https://www.loom.com/share/0af2758c28ba4c4fa98dc2f7950744a9
 */
gform.addFilter( 'gpadvs_settings', function( settings, gpadvs ) {
	settings.render = {
		option: function(data, escape) {
			return '<div>' + getLabel( data.text ) + '</div>';
		},

		item: function(data, escape) {
			return '<div>' + getLabel( data.text ) + '</div>';
		},
	}
	return settings;
} );

function getLabel( text ) {
	var bits = text.split( '|' );
	return `<h4>${bits[0]}</h4><p>${bits[1]}</p>`;
}
