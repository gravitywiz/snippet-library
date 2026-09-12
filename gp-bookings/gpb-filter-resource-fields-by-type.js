/**
 * Gravity Perks // Bookings // Filter Resource Fields by Resource Type
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Limits individual Resource fields to configured Resource Types when the
 * connected Service field uses Manual Selection.
 *
 * Instructions
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Set `formId` to the ID of your form.
 *
 * 3. Map each Resource field ID to its desired Resource Type IDs in
 *    `resourceTypesByFieldId`.
 */
window.gform.addFilter(
	'gpb_resource_field_resources',
	function (resources, context) {
		var formId = 123;
		var resourceTypesByFieldId = {
			4: [10, 11], // Resource field ID 4 => Resource Type IDs 10 and 11.
			5: [12], // Resource field ID 5 => Resource Type ID 12.
		};

		if (Number(context.formId) !== formId) {
			return resources;
		}

		var resourceTypeIds = resourceTypesByFieldId[context.fieldId];

		if (!resourceTypeIds) {
			return resources;
		}

		return resources.filter(function (resource) {
			return resourceTypeIds.includes(Number(resource.resourceType));
		});
	}
);
