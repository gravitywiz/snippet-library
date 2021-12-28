<?php
/**
 * Gravity Wiz // Gravity Forms // Display Field IDs Next to Field Labels in the Editor
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/0268993d0b6c429ba50686bd740093bc
 *
 */
add_filter( 'gform_field_content', function( $content, $field ) {

	if ( ! GFCommon::is_form_editor() ) {
		return $content;
	}

	static $_gw_inline_field_id_style;
	if ( ! $_gw_inline_field_id_style ) {
		$content .= '
			<style>
				.gw-inline-field-id {
				    background-color: #ecedf8;
				    border: 1px solid #d5d7e9;
				    border-radius: 40px;
				    font-size: 0.6875rem;
				    font-weight: 600;
				    padding: 0.1125rem 0.4625rem;
				    margin-bottom: 0.5rem;
                    display: inline-block;
                    vertical-align: middle;
				}
			</style>';
		$_gw_inline_field_id_style = true;
	}

	$search  = '<\/label>|<\/legend>';
	$replace = sprintf( '\0 <span class="gw-inline-field-id">ID: %d</span>', $field->id );
	$content = preg_replace( "/$search/", $replace, $content, 1 );

	return $content;
}, 10, 2 );
