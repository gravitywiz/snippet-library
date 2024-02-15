<?php
/**
 * Gravity Perks // Nested Forms // Display Child Entries as Columns
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * By default, child entries are each output on their own row with the selected Summary Fields as a table header. This
 * snippet outputs each child entry as a column with the selected Summary Fields as a header column.
 *
 * [Screenshot](https://share.cleanshot.com/f9Qxqf5P)
 *
 * Requires the Display Table Format for All Fields snippet and only works if the `:gpnf_table` modifier is set on the
 * {all_fields} merge tag or Nested Form field merge tag.
 * 
 * [Display Table Format for All Fields](https://github.com/gravitywiz/snippet-library/blob/master/gp-nested-forms/gpnf-display-child-entries-table-format.php)
 */
add_filter( 'gp_template_output_nested-entries-detail-simple', function( $markup, $located_template, $load, $args ) {
	/**
	 * @var \GF_Field $field            The current Nested Form field.
	 * @var array     $nested_fields    An array of GF_Field objects.
	 * @var array     $nested_form      The form object of the nested form.
	 * @var array     $nested_field_ids An array of nested field IDs.
	 * @var array     $entries          An array of entry objects.
	 * @var string    $modifiers        The modifiers applied to the merge tag.
	 */
	extract( $args );
	if ( strpos( $modifiers, 'gpnf_table' ) ) {
		return $markup;
	}
	ob_start();
	?>

	<div class="gpnf-nested-entries-container-<?php echo $field->formId; ?>-<?php echo $field->id; ?> gpnf-nested-entries-container gpnf-entry-view ginput_container">
		<table class="gpnf-nested-entries gpnf-nested-entries-simple">

			<?php foreach ( $nested_fields as $nested_field ) : ?>

				<tbody>
				<tr>
					<th class="gpnf-field-<?php echo $nested_field['id']; ?>">
						<?php echo GFCommon::get_label( $nested_field ); ?>
					</th>
					<?php foreach ( $entries as $entry ) : ?>
						<?php $field_values = gp_nested_forms()->get_entry_display_values( $entry, $nested_form, $nested_field_ids );
							$field_value = rgars( $field_values, "{$nested_field['id']}/label" );
							?>
							<td class="gpnf-field"
							    data-heading="<?php echo GFCommon::get_label( $nested_field ); ?>"
							    data-value="<?php echo esc_attr( $field_value ); ?>">
								<?php echo $field_value; ?>
							</td>
					<?php endforeach; ?>
				</tr>
				</tbody>

			<?php endforeach; ?>

		</table>

	</div>

	<?php
	return ob_get_clean();
}, 10, 4 );
