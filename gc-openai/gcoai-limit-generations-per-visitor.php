<?php
/**
 * Gravity Connect // OpenAI // Limit Generations Per Visitor
 * https://gravitywiz.com/documentation/gravity-connect-openai/
 *
 * Enforces a server-side cap on how many times a visitor can trigger an OpenAI request from an
 * OpenAI Stream or OpenAI Image field.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Edit the configuration at the bottom of this snippet.
 */
class GCOAI_Limit_Generations {

	private $args;

	public function __construct( $args = array() ) {
		$this->args = wp_parse_args( $args, array(
			'limit'              => 3,
			'window'             => DAY_IN_SECONDS,
			'identity'           => 'auto',
			'form_id'            => null,
			'field_id'           => null,
			'message'            => 'You have reached the maximum number of responses for this form.',
			'logged_out_message' => 'Please log in to generate a response.',
		) );

		foreach ( array( 'gc_openai_get_prompt', 'gc_openai_generate_image' ) as $action ) {
			add_action( 'wp_ajax_' . $action, array( $this, 'gate' ), 1 );
			add_action( 'wp_ajax_nopriv_' . $action, array( $this, 'gate' ), 1 );
		}
	}

	public function gate() {
		$data = json_decode( \WP_REST_Server::get_raw_data(), true );

		if ( ! is_array( $data ) ) {
			return;
		}

		$form_id  = (int) rgar( $data, 'formId' );
		$field_id = (int) rgar( $data, 'fieldId' );

		if ( ! $form_id || ! $field_id ) {
			return;
		}

		if ( ! $this->is_applicable( $form_id, $field_id ) ) {
			return;
		}

		if ( $this->args['identity'] === 'user' && ! get_current_user_id() ) {
			$this->block( $this->args['logged_out_message'] );
		}

		$identity = $this->get_identity();

		if ( ! $identity ) {
			return;
		}

		$key   = 'gcoai_limit_' . md5( $identity . '|' . $form_id . '|' . $field_id );
		$count = (int) get_transient( $key );

		if ( $count >= (int) $this->args['limit'] ) {
			$this->block( $this->args['message'] );
		}

		set_transient( $key, $count + 1, (int) $this->args['window'] );
	}

	private function is_applicable( $form_id, $field_id ) {
		foreach ( array( 'form_id' => $form_id, 'field_id' => $field_id ) as $arg => $value ) {
			$allowed = $this->args[ $arg ];

			if ( $allowed !== null && ! in_array( $value, array_map( 'intval', (array) $allowed ), true ) ) {
				return false;
			}
		}

		return true;
	}

	private function get_identity() {
		switch ( $this->args['identity'] ) {
			case 'user':
				return 'user:' . get_current_user_id();

			case 'ip':
				return $this->get_ip();

			case 'auto':
			default:
				return get_current_user_id() ? 'user:' . get_current_user_id() : $this->get_ip();
		}
	}

	private function get_ip() {
		$ip = filter_var( rgar( $_SERVER, 'REMOTE_ADDR' ), FILTER_VALIDATE_IP );

		return $ip ? 'ip:' . $ip : '';
	}

	private function block( $message ) {
		// The Image field displays its own generic error rather than this message, but the limit still applies.
		if ( rgget( 'action' ) === 'gc_openai_generate_image' ) {
			wp_send_json_error( $message );
		}

		if ( ! headers_sent() ) {
			header( 'Content-Type: text/event-stream' );
			header( 'Cache-Control: no-cache' );
		}

		echo "event: error\n";
		echo 'data: ' . wp_json_encode( array( 'error' => $message ) ) . "\n\n";

		flush();

		exit;
	}
}

# Configuration

new GCOAI_Limit_Generations( array(
	// Total OpenAI requests allowed per visitor.
	'limit'    => 3,

	// How long before a visitor's allowance resets (e.g. `3 * HOUR_IN_SECONDS` = 3 hours).
	'window'   => DAY_IN_SECONDS,

	// How a visitor is identified:
	//   'auto' - logged-in user ID when available, otherwise IP address
	//   'ip'   - IP address
	//   'user' - logged-in user ID (logged-out visitors are blocked entirely)
	'identity' => 'auto',

	// Shown in the field when the limit is reached.
	'message'  => 'You have reached the maximum number of responses for this form.',

	// Shown to logged-out visitors when 'identity' is 'user'.
	// 'logged_out_message' => 'Please log in to generate a response.',

	// Limit to specific forms/fields. Omit to apply to all of them.
	// 'form_id'  => 123,
	// 'field_id' => 4,
) );
