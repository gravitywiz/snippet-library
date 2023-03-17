<?php
/**
 * Gravity Perks // Nested Forms // Single Submission Child Form
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gp_template_output_nested-entries', function( $markup, $located_template, $load, $args ) {
	ob_start();
	/**
	 * @var array  $nested_fields      An array of GF_Field objects.
	 * @var array  $nested_form        The form object of the nested form.
	 * @var array  $nested_field_ids   An array of nested field IDs.
	 * @var array  $entries            An array of child entries submitted from the current Nested Form field.
	 * @var array  $labels             An array of labels used in this template.
	 * @var array  $aria_labels        An array of labels used for screen readers.
	 * @var array  $actions            An array of HTML strings used to display field actions.
	 * @var bool   $enable_duplication Can child entries be duplicated?
	 * @var int    $column_count       The number of columns.
	 * @var string $add_button         The markup for the "Add Entry" button.
	 * @var string $add_button_message The markup for situational messages related to the "Add Entry" button.
	 */
	extract( $args );
	?>
	<div class="gpnf-nested-entries-container ginput_container">

		<div data-bind="visible: entries().length, foreach: entries">
			✅ Child form submitted!
			<button class="edit-button" data-bind="click: $parent.editEntry, attr: { 'aria-label': '<?php echo esc_js( $aria_labels['edit_entry'] ); ?>'.format( $index() + 1, f<?php echo $nested_fields[0]['id']; ?>.label ) }"><?php echo $labels['edit_entry']; ?></button>
			<button class="delete-button" data-bind="click: $parent.deleteEntry, attr: { 'aria-label': '<?php echo esc_js( $aria_labels['delete_entry'] ); ?>'.format( $index() + 1, f<?php echo $nested_fields[0]['id']; ?>.label ) }"><?php echo $labels['delete_entry']; ?>
		</div>

		<div data-bind="visible: ! entries().length">
			<?php echo $add_button; ?>
			<?php echo $add_button_message; ?>
		</div>

	</div>
	<?php
	return ob_get_clean();
}, 10, 5 );
