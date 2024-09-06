<?php
/**
 * Gravity Wiz // Gravity Forms // Limit Submissions Per Time Period (by IP, User, Role, Form URL, or Field Value)
 *
 * Limit the number of times a form can be submitted per a specific time period. You modify this limit to apply to
 * the visitor's IP address, the user's ID, the user's role, a specific form URL, or the value of a specific field.
 * These "limiters" can be combined to create more complex limitations.
 *
 * @version 3.0
 * @author  David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link    http://gravitywiz.com/better-limit-submission-per-time-period-by-user-or-ip/
 */
class GW_Submission_Limit {

	var $_args;
	var $_notification_event;

	private static $forms_with_individual_settings = array();
	private $_args                                 = array();

	public function __construct( $args ) {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		$this->_args = wp_parse_args( $args, array(
			'form_id'              => false,
			'form_ids'             => array(),
			'limit'                => 1,
			'limit_by'             => 'ip', // 'ip', 'user_id', 'role', 'embed_url', 'field_value'
			'time_period'          => 60 * 60 * 24, // integer in seconds or 'day', 'month', 'year' to limit to current day, month, or year respectively
			'limit_message'        => __( 'Sorry, you have reached the submission limit for this form.' ),
			'apply_limit_per_form' => true,
			'enable_notifications' => false,
		) );

		if ( ! is_array( $this->_args['limit_by'] ) ) {
			$this->_args['limit_by'] = array( $this->_args['limit_by'] );
		}

		if ( empty( $this->_args['form_ids'] ) ) {
			if ( $this->_args['form_id'] === false ) {
				$this->_args['form_ids'] = false;
			} elseif ( ! is_array( $this->_args['form_id'] ) ) {
				$this->_args['form_ids'] = array( $this->_args['form_id'] );
			} else {
				$this->_args['form_ids'] = $this->_args['form_id'];
			}
		}

		if ( $this->_args['form_ids'] ) {
			foreach ( $this->_args['form_ids'] as $form_id ) {
				self::$forms_with_individual_settings[] = $form_id;
			}
		}

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'pre_render' ) );
		add_filter( 'gform_validation', array( $this, 'validate' ) );

		if ( $this->_args['enable_notifications'] ) {

			$this->enable_notifications();

			add_action( 'gform_after_submission', array( $this, 'maybe_send_limit_reached_notifications' ), 10, 2 );

		}

	}

	public function pre_render( $form ) {

		if ( ! $this->is_applicable_form( $form ) || ! $this->is_limit_reached( $form['id'] ) ) {
			return $form;
		}

		$submission_info = rgar( GFFormDisplay::$submission, $form['id'] );

		// if no submission, hide form
		// if submission and not valid, hide form
		// unless 'field_value' limiter is applied
		if ( ( ! $submission_info || ! rgar( $submission_info, 'is_valid' ) ) && ! $this->is_limited_by_field_value() ) {
			add_filter( 'gform_get_form_filter_' . $form['id'], array( $this, 'get_limit_message' ), 10, 2 );
		}

		return $form;

	}

	public function get_limit_message() {
		ob_start();
		?>
		<div class="limit-message">
			<?php echo do_shortcode( $this->_args['limit_message'] ); ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public function validate( $validation_result ) {

		if ( ! $this->is_applicable_form( $validation_result['form'] ) || ! $this->is_limit_reached( $validation_result['form']['id'] ) ) {
			return $validation_result;
		}

		$validation_result['is_valid'] = false;

		if ( $this->is_limited_by_field_value() ) {
			$field_ids = array_map( 'intval', $this->get_limit_field_ids() );
			foreach ( $validation_result['form']['fields'] as &$field ) {
				if ( in_array( $field['id'], $field_ids ) ) {
					$field['failed_validation']  = true;
					$field['validation_message'] = do_shortcode( $this->_args['limit_message'] );
				}
			}
		}

		return $validation_result;
	}

	public function is_limit_reached( $form_id ) {
		return $this->get_entry_count( $form_id ) >= $this->get_limit();
	}

	public function get_entry_count( $form_id ) {
		global $wpdb;

		$where = array();
		$join  = array();

		$where[] = 'e.status = "active"';

		foreach ( $this->_args['limit_by'] as $limiter ) {
			switch ( $limiter ) {
				case 'role': // user ID is required when limiting by role
				case 'user_id':
					$where[] = $wpdb->prepare( 'e.created_by = %s', get_current_user_id() );
					break;
				case 'embed_url':
					$where[] = $wpdb->prepare( 'e.source_url = %s', GFFormsModel::get_current_page_url() );
					break;
				case 'field_value':
					$values = $this->get_limit_field_values( $form_id, $this->get_limit_field_ids() );

					// if there is no value submitted for any of our fields, limit is never reached
					if ( empty( $values ) ) {
						return false;
					}

					foreach ( $values as $field_id => $value ) {
						$table_slug = sprintf( 'em%s', str_replace( '.', '_', $field_id ) );
						$join[]     = "INNER JOIN {$wpdb->prefix}gf_entry_meta {$table_slug} ON {$table_slug}.entry_id = e.id";
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						$where[] = $wpdb->prepare( "\n( ( {$table_slug}.meta_key BETWEEN %s AND %s ) AND {$table_slug}.meta_value = %s )", doubleval( $field_id ) - 0.001, doubleval( $field_id ) + 0.001, $value );
					}

					break;
				default:
					$where[] = $wpdb->prepare( 'ip = %s', GFFormsModel::get_ip() );
			}
		}

		if ( $this->_args['apply_limit_per_form'] || ( ! $this->is_global( $form_id ) && count( $this->_args['form_ids'] ) <= 1 ) ) {
			$where[] = $wpdb->prepare( 'e.form_id = %d', $form_id );
		} else {
			$where[] = $wpdb->prepare( 'e.form_id IN( %s )', implode( ', ', $this->_args['form_ids'] ) );
		}

		$time_period     = $this->_args['time_period'];
		$time_period_sql = false;

		if ( $time_period === false ) {
			// no time period
		} elseif ( intval( $time_period ) > 0 ) {
			$time_period_sql = $wpdb->prepare( 'date_created BETWEEN DATE_SUB(utc_timestamp(), INTERVAL %d SECOND) AND utc_timestamp()', $this->_args['time_period'] );
		} else {

			$gmt_offset  = get_option( 'gmt_offset' );
			$date_func   = $gmt_offset < 0 ? 'DATE_SUB' : 'DATE_ADD';
			$hour_offset = abs( $gmt_offset );

			$date_created_sql  = sprintf( '%s( date_created, INTERVAL %d HOUR )', $date_func, $hour_offset );
			$utc_timestamp_sql = sprintf( '%s( utc_timestamp(), INTERVAL %d HOUR )', $date_func, $hour_offset );

			switch ( $time_period ) {
				case 'per_day':
				case 'day':
					$time_period_sql = "DATE( $date_created_sql ) = DATE( $utc_timestamp_sql )";
					break;
				case 'per_week':
				case 'week':
					$time_period_sql  = "WEEK( $date_created_sql ) = WEEK( $utc_timestamp_sql )";
					$time_period_sql .= "AND YEAR( $date_created_sql ) = YEAR( $utc_timestamp_sql )";
					break;
				case 'per_month':
				case 'month':
					$time_period_sql  = "MONTH( $date_created_sql ) = MONTH( $utc_timestamp_sql )";
					$time_period_sql .= "AND YEAR( $date_created_sql ) = YEAR( $utc_timestamp_sql )";
					break;
				case 'per_year':
				case 'year':
					$time_period_sql = "YEAR( $date_created_sql ) = YEAR( $utc_timestamp_sql )";
					break;
			}
		}

		if ( $time_period_sql ) {
			$where[] = $time_period_sql;
		}

		$where = implode( ' AND ', $where );
		$join  = implode( "\n", $join );

		$sql = "SELECT count( e.id )
                FROM {$wpdb->prefix}gf_entry e
                $join
                WHERE $where";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$entry_count = $wpdb->get_var( $sql );

		return $entry_count;
	}

	public function is_limited_by_field_value() {
		return in_array( 'field_value', $this->_args['limit_by'] );
	}

	public function get_limit_field_ids() {

		$limit = $this->_args['limit'];

		if ( is_array( $limit ) ) {
			$field_ids = array_keys( $this->_args['limit'] );
			$field_ids = array( array_shift( $field_ids ) );
		} else {
			$field_ids = $this->_args['fields'];
		}

		return $field_ids;
	}

	public function get_limit_field_values( $form_id, $field_ids ) {

		$form   = GFAPI::get_form( $form_id );
		$values = array();

		foreach ( $field_ids as $field_id ) {

			$field = GFFormsModel::get_field( $form, $field_id );
			if ( ! $field ) {
				continue;
			}

			$input_name = 'input_' . str_replace( '.', '_', $field_id );
			$value      = GFFormsModel::prepare_value( $form, $field, rgpost( $input_name ), $input_name, null );

			if ( ! rgblank( $value ) ) {
				$values[ "$field_id" ] = $value;
			}
		}

		return $values;
	}

	public function get_limit() {

		$limit = $this->_args['limit'];

		if ( $this->is_limited_by_field_value() ) {
			$limit = is_array( $limit ) ? array_shift( $limit ) : intval( $limit );
		} elseif ( in_array( 'role', $this->_args['limit_by'] ) ) {
			$limit = rgar( $limit, $this->get_user_role() );
		}

		return intval( $limit );
	}

	public function get_user_role() {

		$user = wp_get_current_user();
		$role = reset( $user->roles );

		return $role;
	}

	public function enable_notifications() {

		if ( ! class_exists( 'GW_Notification_Event' ) ) {

			_doing_it_wrong( 'GW_Inventory::$enable_notifications', __( 'Inventory notifications require the \'GW_Notification_Event\' class.' ), '1.0' );

		} else {

			$event_slug = implode( array_filter( array( 'gw_submission_limit_limit_reached', $this->_args['form_id'] ) ) );
			$event_name = GFForms::get_page() == 'notification_edit' ? __( 'Submission limit reached' ) : __( 'Event name is only populated on Notification Edit view; saves a DB call to get the form on every ' );

			$this->_notification_event = new GW_Notification_Event( array(
				'form_id'    => $this->_args['form_id'],
				'event_name' => $event_name,
				'event_slug' => $event_slug,
				//'trigger'    => array( $this, 'notification_event_listener' )
			) );

		}

	}

	public function maybe_send_limit_reached_notifications( $entry, $form ) {

		if ( $this->is_applicable_form( $form ) && $this->is_limit_reached( $form['id'] ) ) {
			$this->send_limit_reached_notifications( $form, $entry );
		}

	}

	public function send_limit_reached_notifications( $form, $entry ) {

		$this->_notification_event->send_notifications( $this->_notification_event->get_event_slug(), $form, $entry, true );

	}

	public function is_applicable_form( $form ) {

		$form_id          = isset( $form['id'] ) ? $form['id'] : $form;
		$is_specific_form = ! $this->is_global( $form_id ) ? in_array( $form_id, $this->_args['form_ids'] ) : false;

		return $this->is_global( $form_id ) || $is_specific_form;
	}

	public function is_global( $form ) {
		$form_id = isset( $form['id'] ) ? $form['id'] : $form;
		return empty( $this->_args['form_ids'] ) && ! in_array( $form_id, self::$forms_with_individual_settings );
	}

}

class GWSubmissionLimit extends GW_Submission_Limit { }

# Configuration

# Basic Usage
new GW_Submission_Limit( array(
	'form_id'       => 86,
	'limit'         => 2,
	'limit_message' => 'Aha! You have been limited.',
) );
