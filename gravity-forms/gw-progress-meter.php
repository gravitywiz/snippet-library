<?php
/**
 * Gravity Wiz // Gravity Forms // Progress Meter
 *
 * Display a meter indicating your progression towards a set goal based on your Gravity Forms entries.
 *
 * Plugin Name:  GF Progress Meter
 * Plugin URI:   https://gravitywiz.com/gravity-forms-progress-meter/
 * Description:  Display a meter indicating your progression towards a set goal based on your Gravity Forms entries.
 * Author:       Gravity Wiz
 * Version:      1.3
 * Author URI:   https://gravitywiz.com
 *
 * @todo
 *      - Add support for different colors and themes.
 *      - Add support for live updates as new entries are submitted.
 *      - Add support for calculating by true form total rather than captured payments.
 */
class GW_Progress_Meter {

	private static $instance = null;

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {

		add_filter( 'gform_shortcode_meter', array( $this, 'do_meter_shortcode' ), 10, 2 );

	}

	public function do_meter_shortcode( $output, $atts ) {

		$atts = shortcode_atts( array(
			'id'          => false,
			'status'      => 'total', // accepts 'total', 'unread', 'starred', 'trash', 'spam'
			'goal'        => false,
			'start'       => false,
			'field'       => false,
			'count_label' => '%s submissions',
			'goal_label'  => '%s goal',
			'name'        => false, // Accepts a string; used via the `shortcode_atts_gf_progress_meter` filter to conditionally filter the $atts.
		), $atts, 'gf_progress_meter' );

		$count = $this->get_count( $atts );
		if ( $atts['start'] && $count < $atts['start'] ) {
			$count = $atts['start'];
		}

		$goal = $this->get_goal( $count, $atts['goal'] );

		$percent_complete = $count <= 0 ? 0 : round( ( $count / $goal ) * 100 );
		$classes          = array( 'gwpm-container' );

		if ( $percent_complete >= 100 ) {
			$classes[] = 'gwpm-goal-reached';
		}

		if ( $percent_complete > 100 ) {
			$classes[]        = 'gwpm-goal-exceeded';
			$percent_complete = 100;
		}

		$output = '<div class="' . implode( ' ', $classes ) . '">
			<div class="gwpm-meter">
				<div class="gwpm-fill" style="width:' . $percent_complete . '%;"></div>
			</div>
			<div class="gwpm-count">
				' . $this->prepare_label( $atts['count_label'], 'count', $count ) . '
			</div>
			<div class="gwpm-goal">
				' . $this->prepare_label( $atts['goal_label'], 'goal', $goal ) . '
			</div>
		</div>';

		$this->enqueue_styles();

		return $output;
	}

	public function get_count( $atts ) {

		if ( isset( $atts['count'] ) && ! rgblank( $atts['count'] ) ) {
			return $atts['count'];
		}

		if ( $atts['field'] ) {

			global $wpdb;

			if ( in_array( $atts['field'], array( 'payment_amount' ), true ) ) {

				$query = array(
					'select' => "SELECT sum( e.`{$atts['field']}` )",
					'from'   => "FROM {$wpdb->prefix}gf_entry e",
					'join'   => '',
					'where'  => $wpdb->prepare( "
						WHERE e.form_id = %d
						AND e.status = 'active'\n",
						$atts['id']
					),
				);

			} else {

				$query = array(
					'select' => 'SELECT sum( em.meta_value )',
					'from'   => "FROM {$wpdb->prefix}gf_entry_meta em",
					'join'   => "INNER JOIN {$wpdb->prefix}gf_entry e ON e.id = em.entry_id",
					'where'  => $wpdb->prepare( "
						WHERE em.form_id = %d
						AND em.meta_key = %s
						AND e.status = 'active'\n",
						$atts['id'], $atts['field']
					),
				);

				if ( class_exists( 'GF_Partial_Entries' ) ) {
					$query['where'] .= "and em.entry_id NOT IN( SELECT entry_id FROM {$wpdb->prefix}gf_entry_meta WHERE meta_key = 'partial_entry_id' )";
				}
			}

			$sql = implode( "\n", $query );

            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$count = intval( $wpdb->get_var( $sql ) );

		} else {

			$valid_statuses = array( 'total', 'unread', 'starred', 'trash', 'spam' );
			if ( ! $atts['id'] || ! in_array( $atts['status'], $valid_statuses, true ) ) {
				return current_user_can( 'update_core' ) ? __( 'Invalid "id" (the form ID) or "status" (i.e. "total", "trash", etc.) parameter passed.' ) : '';
			}

			$counts = GFFormsModel::get_form_counts( $atts['id'] );
			$count  = rgar( $counts, $atts['status'] );

		}

		return $count;
	}

	public function get_goal( $count, $goal ) {

		if ( strpos( $goal, ';' ) === false ) {
			return $goal;
		}

		$goals = explode( ';', $goal );
		foreach( $goals as $goal ) {
			if ( $count < $goal ) {
				return $goal;
			}
		}

		return end( $goals );
	}

	public function enqueue_styles() {
		if ( ! has_action( 'wp_footer', array( $this, 'output_styles' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_styles' ) );
			add_action( 'gform_footer', array( $this, 'output_styles' ) );
		}
	}

	public function output_styles() {
		?>
		<style>
			.gwpm-container {
				display: flex;
				flex-wrap: wrap;
				margin-bottom: 1.5rem;
			}
			.gwpm-meter {
				width: 100%;
				height: 2rem;
				border-radius: 1rem;
				vertical-align: middle;
				background: #D2D6DC;
				font-size: 1rem;
				line-height: 1rem;
			}
			.gwpm-fill {
				background-color: #1E7AC4;
				height: 100%;
				border-radius: 1rem;
			}
			.gwpm-goal-reached .gwpm-fill {
				background-color: #42C414;
			}
			.gwpm-count, .gwpm-goal {
				line-height: 2;
			}
			.gwpm-count {
				width: 50%;
			}
			.gwpm-goal {
				width: 50%;
				text-align: right;
			}
			.gwpm-count-number, .gwpm-goal-number {
				font-weight: bold;
			}
		</style>
		<?php
	}

	public function prepare_label( $label, $type, $number ) {

		$bits = explode( ' ', $label );

		foreach ( $bits as &$bit ) {
			// In order to support formatted numbers we needed to move away from use the `%d` placeholder. This code
			// automatically updates existing uses of the `%d` to `%s`.
			if ( strpos( $bit, '%d' ) !== false ) {
				$bit = str_replace( '%d', '%s', $bit );
			}
			if ( strpos( $bit, '%s' ) !== false ) {
				$currency      = new RGCurrency( GFCommon::get_currency() );
				$number_format = rgar( $currency, 'thousand_separator' ) === '.' ? 'decimal_comma' : '';
				$bit           = sprintf( '<span class="gwpm-%s-number">' . $bit . '</span>', $type, GFCommon::format_number( $number, $number_format, '', true ) );
			}
		}

		return implode( ' ', $bits );
	}

}

function gw_progress_meter() {
	return GW_Progress_Meter::get_instance();
}

gw_progress_meter();
