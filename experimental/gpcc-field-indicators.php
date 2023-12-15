<?php
/**
 * Gravity Perks // Copy Cat // Show Source and Target Field Indicators in Form Editor
 * https://gravitywiz.com/documentation/gravity-forms-copy-cat/
 *
 * Display visual source and target field indicators next to field labels in the form editor.
 */
add_filter( 'gform_field_content', function( $content, $field ) {

	if ( ! GFCommon::is_form_editor() || ! class_exists( 'GP_Copy_Cat' ) || ! $field->formId ) {
		return $content;
	}

	$gpcc        = new GP_Copy_Cat( 'fake.php' );
	$gpcc_fields = $gpcc->get_copy_cat_fields( GFAPI::get_form( $field->formId ) );
	if ( empty( $gpcc_fields ) ) {
		return $content;
	}

	if ( ! has_action( 'admin_footer', 'gpcc_field_indicator_styles' ) ) {
		add_filter( 'admin_footer', 'gpcc_field_indicator_styles' );
	}

	$mappings = array();
	foreach ( $gpcc_fields as $_mappings ) {
		$mappings = array_merge( $mappings, $_mappings );
	}

	$spans = array();

	foreach ( $mappings as $mapping ) {
		if ( $field->id == $mapping['source'] ) {
			$spans['source'] = '<span class="gpcc-source gw-field-indicator">GPCC: Source</span>';
		}
		if ( $field->id == $mapping['target'] ) {
			$spans['target'] = '<span class="gpcc-target gw-field-indicator">GPCC: Target</span>';
		}
		if ( $field->id == $mapping['trigger'] ) {
			$spans['trigger'] = '<span class="gpcc-trigger gw-field-indicator">GPCC: Trigger</span>';
		}
	}

	$search  = '<\/label>|<\/legend>';
	$replace = sprintf( '%s\0', implode( '', $spans ) );
	$content = preg_replace( "/$search/", $replace, $content, 1 );

	return $content;
}, 11, 2 );

function gpcc_field_indicator_styles() {
	?>
	<style>
		.gw-field-indicator {
			margin: 0 0 0 0.6875rem;
			background-color: #ecedf8;
			border: 1px solid #d5d7e9;
			border-radius: 40px;
			font-size: 0.6875rem;
			font-weight: 600;
			padding: 0.1125rem 0.4625rem;
			vertical-align: text-top;
			position: relative;
			top: 3px;
		}
		.gw-field-indicator + .gw-field-indicator {
			margin-left: 0.3725rem;
		}
		.gpcc-source, .gpcc-target, .gpcc-trigger {
			color: #274524;
			background-color: #edf8ec;
			border-color: #d7e8d5;
		}
	</style>
	<?php
}
