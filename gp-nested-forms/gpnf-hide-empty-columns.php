<?php
/**
 * Gravity Perks // Nested Forms // Hide Empty Columns in Summary Table
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Video Instructions:
 *
 * https://www.loom.com/share/84e6d9c5c4334fd79d41e68942fae0d5
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
	if ( ! has_action( 'wp_footer', 'gpnf_hec_output_script' ) ) {
		add_action( 'wp_footer', 'gpnf_hec_output_script' );
		add_action( 'gform_preview_footer', 'gpnf_hec_output_script' );
	}
	?>
	<div class="gpnf-nested-entries-container ginput_container">
		<table class="gpnf-nested-entries">

			<thead>
			<tr>
				<?php foreach ( $nested_fields as $nested_field ) : ?>
					<th class="gpnf-field-<?php echo $nested_field['id']; ?>" data-bind="visible: window.gpnfHecColumnHasValue( '<?php echo $nested_field['id']; ?>', entries() );">
						<?php echo GFCommon::get_label( $nested_field ); ?>
					</th>
				<?php endforeach; ?>
				<th class="gpnf-row-actions">&nbsp;</th>
			</tr>
			</thead>

			<tbody data-bind="visible: entries().length, foreach: entries">
			<tr data-bind="attr: { 'data-entryid': id }">
				<?php foreach ( $nested_fields as $nested_field ) : ?>
					<td class="gpnf-field" data-bind="html: f<?php echo $nested_field['id']; ?>.label,visible: window.gpnfHecColumnHasValue( '<?php echo $nested_field['id']; ?>', $parent.entries() );">&nbsp;</td>
				<?php endforeach; ?>
				<td class="gpnf-row-actions">
					<ul>
						<li class="edit"><a href="#" data-bind="click: $parent.editEntry"><?php echo $labels['edit_entry']; ?></a></li>
						<li class="delete"><a href="#" data-bind="click: $parent.deleteEntry"><?php echo $labels['delete_entry']; ?></a></li>
					</ul>
				</td>
			</tr>
			</tbody>

			<tbody data-bind="visible: entries().length <= 0">
			<tr class="gpnf-no-entries" data-bind="visible: entries().length <= 0">
				<td colspan="<?php echo $column_count; ?>">
					<?php echo $labels['no_entries']; ?>
				</td>
			</tr>
			</tbody>

		</table>

		<?php echo $add_button; ?>

	</div>
	<?php
	return ob_get_clean();
}, 10, 5 );

add_filter( 'gp_template_output_nested-entries-detail-simple', function( $markup, $located_template, $load, $args ) {
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
	<?php
	/**
	 * @var \GF_Field $field            The current Nested Form field.
	 * @var array     $nested_fields    An array of GF_Field objects.
	 * @var array     $nested_form      The form object of the nested form.
	 * @var array     $nested_field_ids An array of nested field IDs.
	 * @var string    $actions          Generated HTML for displaying related entries link.
	 */
	?>
	<div class="gpnf-nested-entries-container-<?php echo $field->formId; ?>-<?php echo $field->id; ?> gpnf-nested-entries-container gpnf-entry-view ginput_container">

		<table class="gpnf-nested-entries gpnf-nested-entries-simple">

			<thead>
			<tr>
				<?php
				foreach ( $nested_fields as $nested_field ) :
					if ( ! gpnf_hec_column_has_value( $nested_field->id, $entries ) ) :
						continue;
					endif;
					?>
					<th class="gpnf-field-<?php echo $nested_field['id']; ?>">
						<?php echo GFCommon::get_label( $nested_field ); ?>
					</th>
				<?php endforeach; ?>
			</tr>
			</thead>

			<tbody>
			<?php foreach ( $entries as $entry ) : ?>
				<?php $field_values = gp_nested_forms()->get_entry_display_values( $entry, $nested_form, $nested_field_ids ); ?>
				<tr>
					<?php
					foreach ( $nested_fields as $nested_field ) :
						if ( ! gpnf_hec_column_has_value( $nested_field->id, $entries ) ) :
							continue;
						endif;
						$field_value = rgars( $field_values, "{$nested_field['id']}/label" );
						?>
						<td class="gpnf-field"
							data-heading="<?php echo GFCommon::get_label( $nested_field ); ?>"
							data-value="<?php echo esc_attr( $field_value ); ?>">
							<?php echo $field_value; ?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>

		</table>

	</div>

	<?php
	return ob_get_clean();
}, 10, 5 );

function gpnf_hec_output_script() {
	?>
	<script>
		function gpnfHecColumnHasValue( fieldId, entries ) {
			var hasValue = false;
			jQuery.each( entries, function( i, entry ) {
				if( ( entry[ fieldId ] && entry[ fieldId ].label ) || ( entry[ `f${fieldId}` ] && entry[ `f${fieldId}` ].label ) ) {
					hasValue = true;
				}
			} );
			return hasValue;
		}
	</script>
	<?php
}

function gpnf_hec_column_has_value( $field_id, $entries ) {
	foreach ( $entries as $entry ) {
		if ( rgar( $entry, $field_id ) ) {
			return true;
		}
	}
	return false;
}
