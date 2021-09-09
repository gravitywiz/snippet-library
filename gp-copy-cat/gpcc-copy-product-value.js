/**
* Gravity Perks // GP Copy Cat // Copy Product Value Without the Price
* https://gravitywiz.com/documentation/gravity-forms-copy-cat/
*
* Copy the value of a choice based product field without the price.
*
* Instructions:
* 1. Add an HTML field to your form.
* 2. Copy and paste the entire content of this snippet into the "Content" field setting.
* 3. Update the target field ID within the snippet.
*/
<script type="text/javascript">

gform.addFilter( 'gppc_copied_value', function( value, $elem, data ) {
  // Update "1" to the ID of the field being copied to.
	if( data.target == 1 && value ) {
		value = value.split( '|' )[0];
	}
	return value;
} );
 
</script>
