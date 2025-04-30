<?php
/**
 * Gravity Wiz // Gravity Forms // All Fields Template: Compare Template
 * https://gravitywiz.com/gravity-forms-all-fields-template/
 *
 * Adds a "compare" template to the All Fields Template snippet that displays each field's label,
 * previous value, and current value in a side-by-side CSS grid layout.
 *
 * Instructions:
 *
 * 1. Ensure you have All Fields Template installed.
 *    https://gravitywiz.com/gravity-forms-all-fields-template/
 *
 * 2. Install this snippet. No code configuration required.
 *    https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 *
 * 3. Enable the `compare` template on any `{all_fields}` merge tag. Be sure to include the `updated` modifier as well.
 *    `{all_fields:updated,template[compare]}`
 */
add_filter( 'gwaft_template_output', function( $content, $slug, $name, $data, $suffixes ) {
	if ( ! in_array( 'compare', $suffixes ) ) {
		return $content;
	}
	$original_entry = gw_all_fields_template()->get_original_entry();
	?>
	<div class="gwaft-compare-grid">
		<?php foreach ( $data['items'] as $item ):
			$raw_field_value = GFFormsModel::get_lead_field_value( $original_entry, $item['field'] );
			$previous_value  = GFCommon::get_lead_field_display( $item['field'], $raw_field_value, rgar( $original_entry, 'currency' ), true, 'html', 'email' );
			?>
			<div class="gwaft-compare-label"><?php echo $item['label']; ?></div>
			<div class="gwaft-compare-prev"><?php echo $previous_value; ?></div>
			<div class="gwaft-compare-current"><?php echo $item['value']; ?></div>
		<?php endforeach; ?>
	</div>
	<?php
	?>
	<style>
	.gwaft-compare-grid {
		display: grid;
		grid-template-columns: 1fr 1fr 1fr;
		margin-top: 1rem;
		padding: 0.5rem;
	}
	.gwaft-compare-grid > div {
		padding: 0.5rem;
		border-bottom: 1px solid #ddd;
	}
	.gwaft-compare-label {
		font-weight: bold;
	}
	</style>
	<?php
	return ob_get_clean();
}, 10, 5 );
