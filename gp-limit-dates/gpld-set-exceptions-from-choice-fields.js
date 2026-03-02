/**
 * Gravity Perks // Limit Dates // Set Exceptions from Choice Field
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Use dates from a choice-based field as exceptions in a GP Limit Dates-enabled Date field.
 * Works with static choices, dynamically populated choices via GP Populate Anything.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Add a choice-based field with date values as choices (e.g. 2025-03-15).
 *
 * 3. Update the configuration variables below.
 */
var dateFieldId   = 3; // Update "3" to your Date field ID.
var sourceFieldId = 5; // Update "5" to your Choice field ID with date values.
var exceptionMode = 'disable'; // 'disable' = block these dates, 'enable' = only allow these dates, 'default' = invert availability.
var selectedOnly  = false; // Set to true to only use selected/checked choices as exceptions.
var dateFormat    = 'yyyy-mm-dd'; // Update to your date format (e.g. 'mm/dd/yyyy', 'dd.mm.yyyy', 'yyyy-mm-dd')

if ( ! window._gwGpldEfcInit ) {
	window._gwGpldEfcInit = true;

	gform.addFilter( 'gpld_datepicker_data', function( data, formId ) {
		if ( parseInt( formId, 10 ) !== GFFORMID || ! data || ! data[ dateFieldId ] ) {
			return data;
		}
		gwApplyExceptions( data[ dateFieldId ] );
		return data;
	} );

	jQuery( document ).on( 'gppa_updated_batch_fields', function( e, formId, updatedFieldIds ) {
		if ( parseInt( formId, 10 ) === GFFORMID && jQuery.inArray( String( sourceFieldId ), updatedFieldIds ) !== -1 ) {
			gwRefreshDatepicker();
		}
	} );

	jQuery( document ).on( 'change', '#field_' + GFFORMID + '_' + sourceFieldId + ' select, #field_' + GFFORMID + '_' + sourceFieldId + ' input', gwRefreshDatepicker );
}

gwRefreshDatepicker();

function gwApplyExceptions( fieldData ) {
	fieldData.exceptions    = gwGetChoiceDates();
	fieldData.exceptionMode = exceptionMode;
	fieldData.disableAll    = exceptionMode === 'enable';
}

function gwRefreshDatepicker() {
	var $input = jQuery( '#input_' + GFFORMID + '_' + dateFieldId );
	if ( $input.length && $input.hasClass( 'hasDatepicker' ) ) {
		$input.removeClass( 'hasDatepicker' );
		gformInitSingleDatepicker( $input );
	}

	var data    = window[ 'GPLimitDatesData' + GFFORMID ];
	var $inline = jQuery( '#datepicker_' + GFFORMID + '_' + dateFieldId );
	if ( data && data[ dateFieldId ] && $inline.length && $inline.hasClass( 'hasDatepicker' ) ) {
		gwApplyExceptions( data[ dateFieldId ] );
		$inline.datepicker( 'option', 'beforeShowDay', function( date ) {
			return GPLimitDates.isDateShown( date, data, dateFieldId );
		} );
	}
}

function gwGetChoiceDates() {
	var $field = jQuery( '#field_' + GFFORMID + '_' + sourceFieldId );
	var sel    = selectedOnly
		? 'select option:selected, input:checked'
		: 'select option, input[type="radio"], input[type="checkbox"]';
	var dates = [];

	$field.find( sel ).each( function() {
		var mdy = gwToMdy( jQuery( this ).val() );
		if ( mdy && dates.indexOf( mdy ) === -1 ) {
			dates.push( mdy );
		}
	} );

	return dates;
}

function gwToMdy( value ) {
	var valStr = String( value || '' ).trim();
	var fmt = String( typeof dateFormat !== 'undefined' ? dateFormat : 'yyyy-mm-dd' ).toLowerCase();

	var sep = fmt.replace( /[dmy]/g, '' )[0];
	if ( ! sep ) return null;

	var tokens = fmt.split( sep );
	var pos = {};
	for ( var i = 0; i < 3; i++ ) {
		pos[ tokens[i] ] = i;
	}

	var parts = valStr.match( /\d+/g );
	if ( ! parts || parts.length < 3 ) return null;

	var d = parts[ pos.dd ], m = parts[ pos.mm ], y = parts[ pos.yyyy ];
	
	if ( ! d || ! m || ! y || String( y ).length !== 4 ) return null;

	var p = function( n ) { return ( '0' + n ).slice( -2 ); };
	return p( m ) + '/' + p( d ) + '/' + y;
}
