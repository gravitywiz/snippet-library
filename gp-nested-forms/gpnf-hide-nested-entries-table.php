<?php
/**
 * Gravity Perks // Nested Forms // Hides Nested Entries table if empty
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Installation:
 *  1. Place this file in your theme under gp-nested-forms/nested-entries.php. The full path should look something like
 *    wp-content/themes/YOUR-THEME/gp-nested-forms/nested-entries.php
 *  2. Done!
 */
?>
<div class="gpnf-nested-entries-container ginput_container">

	<?php
	/**
	 * Modification 1: Add inline CSS to hide Nested Entries table by default.
	 */
	?>
	<style type="text/css">
		.gpnf-nested-entries {
			display: none;
		}

		.gpnf-nested-entries-has-entries {
			display: table;
		}
	</style>

	<?php
	/**
	 * Modification 2: Add data-bind attribute to the table element.
	 */
	?>
	<table class="gpnf-nested-entries" data-bind="css: { 'gpnf-nested-entries-has-entries': entries().length > 0 }">

		<thead>
		<tr>
			<?php foreach ( $nested_fields as $nested_field ): ?>
				<th class="gpnf-field-<?php echo $nested_field['id']; ?>">
					<?php echo GFCommon::get_label( $nested_field ); ?>
				</th>
			<?php endforeach; ?>
			<th class="gpnf-row-actions">&nbsp;</th>
		</tr>
		</thead>

		<tbody data-bind="visible: entries().length > 0, foreach: entries">
		<tr data-bind="attr: { 'data-entryid': id }">
			<?php foreach ( $nested_fields as $nested_field ): ?>
				<td class="gpnf-field"
				    data-bind="html: f<?php echo $nested_field['id']; ?>.label, attr: { 'data-value': f<?php echo $nested_field['id']; ?>.label }"
				    data-heading="<?php echo GFCommon::get_label( $nested_field ); ?>"
				>&nbsp;</td>
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
	<?php echo $add_button_message; ?>

</div>
