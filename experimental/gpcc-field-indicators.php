/**
 * Gravity Perks // Copy Cat // Show Source and Target Field Indicators in Form Editor
 * https://gravitywiz.com/documentation/gravity-forms-copy-cat/
 * 
 * Display visual source and target field indicators next to field labels in the form editor.
 */
add_filter( 'gform_admin_pre_render', function( $form ) {

	if ( ! class_exists( 'GP_Copy_Cat' ) ) {
		return $form;
	}

	$gpcc = new GP_Copy_Cat();
	$gpcc_fields = $gpcc->get_copy_cat_fields( $form );
	if ( empty( $gpcc_fields ) ) {
		return $form;
	}

	add_filter( 'admin_footer', function() {
		?>
		<style>
			.gpcc-source .gform-field-label:after {
				content: 'GPCC: Source';
				color: #274524;
				margin: 0 0.5rem;
				background-color: #edf8ec;
				border: 1px solid #d7e8d5;
				border-radius: 40px;
				float: right;
				font-size: 0.6875rem;
				font-weight: 600;
				padding: 0.1125rem 0.4625rem;
			}

			.gpcc-target .gform-field-label:after {
				content: 'GPCC: Target';
				color: #274524;
				margin: 0 0.5rem;
				background-color: #edf8ec;
				border: 1px solid #d7e8d5;
				border-radius: 40px;
				float: right;
				font-size: 0.6875rem;
				font-weight: 600;
				padding: 0.1125rem 0.4625rem;
			}
		</style>
		<?php
	} );

	$mappings = array();
	foreach ( $gpcc_fields as $_mappings ) {
		$mappings = array_merge( $_mappings );
	}

	foreach( $form['fields'] as &$field ) {
		foreach( $mappings as $mapping ) {
			if ( $field->id == $mapping['source'] ) {
				$field->cssClass .= ' gpcc-source';
			}
			if ( $field->id == $mapping['target'] ) {
				$field->cssClass .= ' gpcc-target';
			}
		}
	}

	return $form;
} );
