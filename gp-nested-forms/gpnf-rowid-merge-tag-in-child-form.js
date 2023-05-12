/**
 * Gravity Wiz // GP Nested Forms // Provide {RowID} Merge Tag in Child Form
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Replaces {RowID} merge tag in the child form with the Row ID while that form is displayed within nested forms. 
 * Parent element must have a "rowid-merge" CSS class added.
 * 
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * 2. Add a {RowID} merge tag to the child form, and a "rowid-merge" CSS class to the element that contains it (e.g. the
 *    HTML block in the Gutenberg block editor). 
 */

document.querySelector(".gpnf-add-entry").addEventListener('click', () => {
	setTimeout(swapRowIdMergeTag, 200);
});
document.querySelector("table.gpnf-nested-entries").addEventListener('click', (event) => {
	if(event.target.classList.contains('edit-button')) {
		setTimeout(swapRowIdMergeTag, 200);
	}
});

function swapRowIdMergeTag(event) {
	let entrySet = document.querySelector("[data-bind='value: entryIds']").value.split(',');
	let entryIdElement = document.querySelector("input[name='gpnf_entry_id']");
	let entryId = false;
	if (typeof(entryIdElement) != 'undefined' && entryIdElement != null) {
		entryId = entryIdElement.value;
	}
	
	let rowId = 0;
	if(entryId != false) {
		rowId = entrySet.indexOf(entryId) + 1;
	} else {
		if(entrySet[0] == "") {
			rowId = 1;
		} else {
			rowId = entrySet.length + 1;
		}
	}
	
	let rowIdElement = document.querySelector('.rowid-merge');
	if (typeof(rowIdElement) == 'undefined' || rowIdElement == null) {
		// console.log("No .rowid-merge element found.")
		return;
	}
	rowIdElement.innerHTML = rowIdElement.innerHTML.replace("{RowID}", rowId);
}
