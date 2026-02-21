/**
 * Gravity Wiz // Gravity Forms // Conditionally Disable Checkboxes
 * https://gravitywiz.com/
 *
 * Disable checkboxes in one Checkbox field depending on the values checked in another.
 *
 * Instructions:
 *
 * 1. Watch this video:
 *    https://www.loom.com/share/b3ebfdc6b6b2440f917e3b431c615e21
 *
 * 2. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
// Add all checkbox field IDs here
var checkboxGroups = [
	'#input_GFFORMID_1',
	'#input_GFFORMID_2',
	'#input_GFFORMID_3',
	'#input_GFFORMID_4'
];

// Exclusions map: key = value of checked checkbox, value = array of checkbox values to disable
var exclusions = {
	// First Checkbox field exclusions.
	'First Choice A': [ 'First Choice B' ],
	'Second Choice A': [ 'Second Choice B' ],
	'Third Choice A': [ 'Third Choice B', 'Fourth Choice B', 'Fifth Choice B' ],
	// Second Checkbox field exclusions.
	'First Choice B': [ 'First Choice A' ],
	'Second Choice B': [ 'Second Choice A' ],
	'Third Choice B': [ 'Third Choice A' ],
	'Fourth Choice B': [ 'Third Choice A' ],
	'Fifth Choice B': [ 'Third Choice A' ],
	// Third Checkbox field exclusions.
	'First Choice C': [ 'First Choice D', 'Second Choice D' ],
	// Fourth Checkbox field exclusions.
	'First Choice D': [ 'First Choice C' ],
	'Second Choice D': [ 'First Choice C' ]
};

var $groups = $( checkboxGroups.join(',') );
$groups.on('change', 'input[type="checkbox"]', function() {
	updateAllCheckboxes();
});

updateAllCheckboxes();

function updateAllCheckboxes() {

	// Collect all checked values across all groups
	var checkedValues = [];

	$groups.each(function() {
		$(this).find('input:checked:not(.gplc-disabled, .gwlc-disabled, .gpi-disabled)')
			.each(function() {
				checkedValues.push($(this).val());
			});
	});

	// First reset everything
	$groups.find('input[type="checkbox"]:not(.gplc-disabled, .gwlc-disabled, .gpi-disabled)')
		.prop('disabled', false);

	// Apply exclusions globally
	$.each(exclusions, function(key, valuesToDisable) {
		if ($.inArray(key, checkedValues) !== -1) {
			$.each(valuesToDisable, function(index, targetValue) {
				$groups.find('input[value="' + targetValue + '"]')
					.prop('checked', false)
					.prop('disabled', true);
			});
		}
	});
}
