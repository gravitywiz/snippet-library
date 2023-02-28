<?php
/**
 * Gravity Perks // Nested Forms // Hide Nested Entries Table if Empty
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

		<table class="gpnf-nested-entries" data-bind="visible: entries().length">

			<thead>
			<tr>
				<?php foreach ( $nested_fields as $nested_field ) : ?>
					<th class="gpnf-field-<?php echo $nested_field['id']; ?>">
						<?php echo GFCommon::get_label( $nested_field ); ?>
					</th>
				<?php endforeach; ?>
				<th class="gpnf-row-actions"><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'gp-nested-forms' ); ?></span></th>
			</tr>
			</thead>

			<tbody data-bind="visible: entries().length, foreach: entries">
			<tr data-bind="attr: { 'data-entryid': id }">
				<?php foreach ( $nested_fields as $nested_field ) : ?>
					<td class="gpnf-field"
						data-bind="html: f<?php echo $nested_field['id']; ?>.label, attr: { 'data-value': f<?php echo $nested_field['id']; ?>.label }"
						data-heading="<?php echo GFCommon::get_label( $nested_field ); ?>"
					>&nbsp;</td>
				<?php endforeach; ?>
				<td class="gpnf-row-actions" style="display: none;" data-bind="visible: true">
					<ul>
						<li class="edit"><button class="edit-button" data-bind="click: $parent.editEntry, attr: { 'aria-label': '<?php echo esc_js( $aria_labels['edit_entry'] ); ?>'.format( $index() + 1, f<?php echo $nested_fields[0]['id']; ?>.label ) }"><?php echo $labels['edit_entry']; ?></button></li>
						<?php if ( $enable_duplication ) : ?>
							<li class="duplicate" data-bind="visible: ! $parent.isMaxed()"><button href="#" data-bind="click: $parent.duplicateEntry, attr: { 'aria-label': '<?php echo esc_js( $aria_labels['duplicate_entry'] ); ?>'.format( $index() + 1, f<?php echo $nested_fields[0]['id']; ?>.label ) }"><?php echo $labels['duplicate_entry']; ?></button></li>
						<?php endif; ?>
						<li class="delete"><button class="delete-button" data-bind="click: $parent.deleteEntry, attr: { 'aria-label': '<?php echo esc_js( $aria_labels['delete_entry'] ); ?>'.format( $index() + 1, f<?php echo $nested_fields[0]['id']; ?>.label ) }"><?php echo $labels['delete_entry']; ?></button></li>
					</ul>
				</td>
			</tr>
			</tbody>

		</table>

		<?php echo $add_button; ?>
		<?php echo $add_button_message; ?>

	</div>
	<?php
	return ob_get_clean();
}, 10, 5 );
