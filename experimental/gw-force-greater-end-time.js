/**
 * Gravity Wiz // Gravity Forms // Force Greater End Time.
 * https://gravitywiz.com/
 *
 * Force the user to enter an end time greater than the start time.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 * 2. Configure based on the inline instructions.
 */

// Update "3" to the Start Date Field ID.
var start_date_id = 3;
// Update "4" to the End Date Field ID.
var end_date_id = 4;
// Update "5" to the Start Time Field ID.
var start_time_field_id = 5;
// Update "6" to the End Date Field ID.
var end_time_field_id = 6;

jQuery( '#field_' + GFFORMID + '_' + start_time_field_id ).change( function(){

    var start_date = jQuery('#input_'+ GFFORMID + '_' + start_date_id ).val();
    var end_date = jQuery('#input_'+ GFFORMID + '_' + end_date_id ).val();
    if ( start_date != end_date)
    {
       return false;
    }
    var start_time = get_timestamp( start_time_field_id );
	if ( start_time == false ){
		return false;
	}
    set_end_time( start_time );
});


jQuery( '#field_' + GFFORMID + '_' + end_time_field_id ).change(function(){

    var start_timestamp = get_timestamp( start_time_field_id );
    var end_timestamp = get_timestamp( end_time_field_id );
    if ( start_timestamp == false || end_timestamp == false ){
        return false;
    }
     var difference = end_timestamp  - start_timestamp;
     if ( difference < 3600001 ){
           set_end_time( start_timestamp );
     }
});

function get_timestamp ( time_field_id ){
    var inputs  =jQuery( '#field_' + GFFORMID + '_' + time_field_id ).find('input, select' );

    var hour        = inputs.eq( 0 ).val(),
        min         = inputs.eq( 1 ).val(),
        ampm        = inputs.eq( 2 ).val(),
        datetime    =  new Date();

   if ( inputs.eq( 0 ).val() =='' ||  inputs.eq( 1 ).val() == '' ){
         return false
   }

   if ( inputs.eq( 2 ).length ) {
	   if ( ampm.toLowerCase() === 'pm' ){
		   datetime.setHours( parseInt( hour ) + ( hour === '12' ? 0 : 12 ) );
	   }else if ( ampm.toLowerCase() === 'am'){
		     datetime.setHours( parseInt( hour ) - ( hour === '12' ? 12 : 0 ) );
	   }
	   else{
		   datetime.setHours( parseInt( hour ) );
	   }
   } else {
       datetime.setHours( parseInt( hour ) );
   }

   datetime.setMinutes( min );
   var  time_stamp = datetime.getTime();
   return time_stamp;
}

function set_end_time ( start_timestamp ){

    var end_date_time = new Date(start_timestamp);
    var end_inputs  =jQuery( '#field_' + GFFORMID + '_' + end_time_field_id).find('input, select' );

	var hours   = isNaN( end_date_time.getHours() ) ? '' : end_date_time.getHours() +1,
		minutes = isNaN( end_date_time.getMinutes() )  ? '' : end_date_time.getMinutes(),
		hasAMPM = end_inputs.length === 3,
		isPM    = false;

        if ( hasAMPM ) {
            if ( hours === 0 ) {
                hours = 12;
            } else if ( hours > 12 ) {
                hours -= 12;
                isPM   = true;
            } else if ( hours == 12 ) {
                // for 12 PM, the PM display should update
                isPM = true;
            }

        }

    end_inputs.eq( 0 ).val( ( '0' + hours ).slice( -2 ) );
    end_inputs.eq( 1 ).val( ( '0' + minutes ).slice( -2 ) );

    if ( hasAMPM ) {
        if ( isPM ) {
            end_inputs.eq( 2 ).find( 'option:last' ).prop( 'selected', true );
        } else {
            end_inputs.eq( 2 ).find( 'option:first' ).prop( 'selected', true );
        }
    }
}
