/**
 * Gravity Wiz // Gravity Forms // Swap Values in Drop Down & Multiselect Fields
 * https://gravitywiz.com/
 *
 * This snippet will add a swap button for any two Drop Down or Multiselect fields. When clicked the value(s)
 * from each field will be swapped with the value(s) in the other. Both fields must be of the same type and have
 * the same choices available. You may need to tweak the styling/position of the swap button depending on your theme.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * 2. Following the inline instructions to configure for your form.
 */
// Replace "4" and "5" with field IDs for any two Drop Down or Multiselect fields. Field types must match. DUplicate 
gwCreateSwapButton( GFFORMID, 4, 5 );

function gwCreateSwapButton( formId, fieldIdA, fieldIdB ) {

	const swapButton = document.createElement('button');
	swapButton.innerText = '⇄';
	swapButton.type = 'button';
	swapButton.style.position = 'absolute';
	swapButton.style.padding = '0';
	swapButton.style.background = 'transparent';
	swapButton.style.color = '#000';
	swapButton.style.boxShadow = 'none';
	swapButton.style.top = '0';
	swapButton.style.right = '-15px';
	swapButton.style.border = '0';

	// Insert the button between the two dropdowns
	const dropdownA = document.getElementById(`input_${formId}_${fieldIdA}`);
	const dropdownB = document.getElementById(`input_${formId}_${fieldIdB}`);

	dropdownA.parentNode.style.position = 'relative';
	dropdownA.parentNode.appendChild(swapButton);

	// Add event listener to handle the swap functionality
	swapButton.addEventListener('click', function() {
		
		let selectedA;
		let selectedB;
		
		if ( dropdownA.multiple ) {
			
			selectedA = Array.from(dropdownA.selectedOptions).map(option => option.value);
			selectedB = Array.from(dropdownB.selectedOptions).map(option => option.value);
			
			Array.from(dropdownA.options).forEach(option => {
			  option.selected = selectedB.includes(option.value);
			});
			
			Array.from(dropdownB.options).forEach(option => {
			  option.selected = selectedA.includes(option.value);
			});
			
		} else {
			
			selectedA = dropdownA.value;
			selectedB = dropdownB.value;
			
			dropdownA.value = selectedB;
			dropdownB.value = selectedA;
			
		}
			
		dropdownA.dispatchEvent(new Event('change'));
		dropdownB.dispatchEvent(new Event('change'));

	});
}
