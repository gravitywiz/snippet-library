/**
 * Gravity Connect // OpenAI // Stream to Paragraph Field
 * https://gravitywiz.com/documentation/gravity-connect-openai/
 *
 * Stream OpenAI responses from the OpenAI Stream field to a Paragraph (or Single Line Text)
 * field instead. Useful when wanting to provide a starting point for users while allowing them
 * to edit the final result.
 *
 * Instruction Video: https://www.loom.com/share/f793319da7e449a8b01e5a8c077e24c7
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * 2. Update the variables to match your own field IDs.
 */
// Add one or more mappings for your Stream Field, Prompt Field, and Response Field.
const streamFieldMappings = [
	{
		streamFieldId: 3,      // Stream Field.
		promptFieldId: 1,      // Prompt Field.
		responseFieldId: 4     // Paragraph/Text Field where response content should go.
	},
	{
		streamFieldId: 6,
		promptFieldId: 5,
		responseFieldId: 7
	},
	// Add more mappings here (as needed).
];

streamFieldMappings.forEach(({ streamFieldId, promptFieldId, responseFieldId }) => {
	const $streamFieldInput = $(`#input_GFFORMID_${streamFieldId}`);
	const $streamButton = $streamFieldInput.closest('.gfield').find('.gcoai-trigger');

	// When the stream field changes (OpenAI output comes in)
	$streamFieldInput.on('change', function () {
		const $responseInput = $(`#input_GFFORMID_${responseFieldId}`);
		const value = this.value;
		const tiny = window.tinyMCE && tinyMCE.get($responseInput.attr('id'));

		if (tiny) {
			const html = $streamFieldInput.closest('.gfield').find('.gcoai-output').html();
			tiny.setContent(html);
		} else {
			$responseInput.val(value);
		}
	});

	// Add a secondary button that re-triggers OpenAI generation
	const $newButton = $streamButton
		.clone()
		.attr('style', 'margin-top: var(--gf-label-space-primary, 8px);')
		.on('click', function () {
			$streamButton.trigger('click');
		})
		.insertAfter($(`#input_GFFORMID_${responseFieldId}`));

	const $wpEditor = $newButton.parents('.wp-editor-container');
	if ($wpEditor.length) {
		$newButton.insertAfter($wpEditor);
	}

	// Optional: auto-trigger generation on prompt field blur
	$(`#input_GFFORMID_${promptFieldId}`).on('blur', function () {
		$streamButton.trigger('click');
	});
});
