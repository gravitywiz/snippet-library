<?php
/**
 * Allow :admin modifier in {all_fields} merge tag to affect displayed nested form.
 * Usage: Place this file in your current active theme structure.
 *
 * @var array  $nested_fields    An array of GF_Field objects.
 * @var array  $nested_form      The form object of the nested form.
 * @var array  $nested_field_ids An array of nested field IDs.
 * @var array  $entries          An array of child entries submitted from the current Nested Form field.
 * @var array  $labels           An array of labels used in this template.
 * @var array  $actions          An array of HTML strings used to display field actions.
 * @var array  $args             An array of arguments passed to the template.
 */
?>
<div class="gpnf-nested-entries-container gpnf-entry-view ginput_container">

	<table class="gpnf-nested-entries">

		<thead>
		<tr>
			<?php foreach ( $nested_fields as $nested_field ) : ?>
				<th class="gpnf-field-<?php echo $nested_field['id']; ?>">
					<?php
					if ( rgar( $args, 'modifiers' ) && strpos( $args['modifiers'], 'admin' ) !== false ) {
						$nested_field->set_context_property( 'use_admin_label', true );
					}
					?>
					<?php echo GFCommon::get_label( $nested_field ); ?>
				</th>
			<?php endforeach; ?>
			<th class="gpnf-row-actions">&nbsp;</th>
		</tr>
		</thead>

		<tbody>
		<?php foreach ( $entries as $entry ) : ?>
			<?php $field_values = gp_nested_forms()->get_entry_display_values( $entry, $nested_form, $nested_field_ids ); ?>
			<tr>
				<?php
				foreach ( $nested_fields as $nested_field ) :
					$field_value = rgars( $field_values, "{$nested_field['id']}/label" );
					?>
					<td class="gpnf-field"
						data-heading="<?php echo GFCommon::get_label( $nested_field ); ?>"
						data-value="<?php echo esc_attr( $field_value ); ?>">
						<?php echo $field_value; ?>
					</td>
				<?php endforeach; ?>
				<!-- The whitespace below matters. Using CSS :empty to hide when link is not output. -->
				<td class="gpnf-row-actions"><?php if ( ! empty( $labels['view_entry'] ) ) : ?>
					<a href="<?php echo gp_nested_forms()->get_entry_url( $entry['id'], $nested_form['id'] ); ?>">
						<?php echo $labels['view_entry']; ?>
					</a>
				<?php endif; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>

	</table>

	<div class="gpnf-actions">
		<?php echo implode( ' | ', $actions ); ?>
	</div>

</div>
