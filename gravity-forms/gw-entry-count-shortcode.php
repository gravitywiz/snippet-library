<?php
/**
 * Gravity Wiz // Gravity Forms // Entry Count Shortcode
 *
 * Extends the [gravityforms] shortcode, providing a custom action to retrieve the total entry count and
 * also providing the ability to retrieve counts by entry status (i.e. 'trash', 'spam', 'unread', 'starred').
 *
 * @version  1.0
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link	 http://gravitywiz.com/
 */
add_filter( 'gform_shortcode_entry_count', 'gwiz_entry_count_shortcode', 10, 2 );
function gwiz_entry_count_shortcode( $output, $atts ) {

	wp_enqueue_script( 'jquery-ui-progressbar');

	extract( shortcode_atts( array(
		'id' => false,
		'status' => 'total', // accepts 'total', 'unread', 'starred', 'trash', 'spam'
		'format' => false, // should be 'comma', 'decimal'
		'goal' => false
	), $atts ) );

	$valid_statuses = array( 'total', 'unread', 'starred', 'trash', 'spam' );

	if( ! $id || ! in_array( $status, $valid_statuses ) ) {
		return current_user_can( 'update_core' ) ? __( 'Invalid "id" (the form ID) or "status" (i.e. "total", "trash", etc.) parameter passed.' ) : '';
	}

	$counts = GFFormsModel::get_form_counts( $id );
	$output = rgar( $counts, $status );

	if( $format ) {
		$format = $format == 'decimal' ? '.' : ',';
		$output = number_format( $output, 0, false, $format );
	}

	if ( $goal ) {
		$output = '<div class="count-container"><div id="progress-bar"></div><div id="count">' . $output . '</div><div id="goal">' . $goal . '</div></div>';

		?>
		<script type="text/javascript">
		jQuery(function($){
			var count = Number($( '#count').html());
			var max = Number($( '#goal' ).html());
			var progress = $( '#progress-bar' ).progressbar({
				value: count,
				max: max
			});
		});
		</script>
		<style>
		#progress-bar {
			padding: 10px;
			position: relative;
			background-color: rgba(0, 0, 0, 0.1);
			-moz-border-radius: 25px;
			-webkit-border-radius: 25px;
			border-radius: 25px;
			-webkit-box-shadow: inset 0px 0px 1px 1px rgba(0,0,0,0.05);
			-moz-box-shadow: inset 0px 0px 1px 1px rgba(0,0,0,0.05);
			box-shadow: inset 0px 0px 1px 1px rgba(0,0,0,0.05);
		}
		#progress-bar .ui-progressbar-value {
			height: 24px;
			border-top-right-radius: 4px;
			border-bottom-right-radius: 4px;
			border-top-left-radius: 20px;
			border-bottom-left-radius: 20px;
			z-index: 999;
			vertical-align: middle;
			background: repeating-linear-gradient( -45deg, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0) 10px, rgba(0, 0, 0, 0.1) 10px, rgba(0, 0, 0, 0.1) 20px),linear-gradient(to bottom, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.3));
			background-color: rgba(0, 0, 0, 0);
			background-color: #036493;
			color: #FFF;
		}
		#progress-bar::after {
			content: "";
			display: block;
			width: 100%;
			z-index: 990;
			height: 24px;
			margin-top: -24px;
			background-color: rgba(0, 0, 0, 0.1);
			border-top-right-radius: 20px;
			border-bottom-right-radius: 20px;
			border-top-left-radius: 20px;
			border-bottom-left-radius: 20px;
			-webkit-box-shadow: inset 0px 0px 2px 2px rgba(0,0,0,0.05);
			-moz-box-shadow: inset 0px 0px 2px 2px rgba(0,0,0,0.05);
			box-shadow: inset 0px 0px 2px 2px rgba(0,0,0,0.05);
		}
		#count {
			width: 50%;
			float: left;
		}

		#goal {
			width: 50%;
			float: right;
			text-align: right;
		}
		</style>
		<?php
	}

	return $output;
}
