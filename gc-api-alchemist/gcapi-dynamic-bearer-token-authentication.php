<?php
/**
 * Gravity Wiz // API Alchemist // Dynamic Bearer Token Authentication Handler
 *
 * Add support for dynamic bearer token authentication to GC API Alchemist.
 *
 * Many APIs use token-based authentication where you authenticate once to receive
 * a token, then use that token for subsequent requests until it expires.
 */

use GC_API_Alchemist\Authentication\Auth_Handler;
use GC_API_Alchemist\Connection_Profiles\Connection_Profile;

class GCAPI_Dynamic_Bearer_Auth_Handler extends Auth_Handler {

	/**
	 * Get the unique authentication type identifier
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'dynamic_bearer';
	}

	/**
	 * Get the human-readable label for this auth type
	 *
	 * @return string
	 */
	public function get_label(): string {
		return 'Dynamic Bearer Token';
	}

	/**
	 * Get configuration fields for this auth type
	 *
	 * @return array
	 */
	public function get_config_fields(): ?array {
		return array(
			'token_endpoint'    => array(
				'type'        => 'text',
				'label'       => 'Token Endpoint URL',
				'description' => 'The API endpoint that generates authentication tokens',
				'required'    => true,
			),
			'username'          => array(
				'type'        => 'text',
				'label'       => 'Credential Value',
				'description' => 'Your API token, username, client ID, or key (field name configured below)',
				'required'    => true,
			),
			'password'          => array(
				'type'        => 'password',
				'label'       => 'Credential Secret',
				'description' => 'Your API secret, password, or client secret (field name configured below)',
				'required'    => true,
			),
			'username_field'    => array(
				'type'        => 'text',
				'label'       => 'Token Field Name',
				'description' => 'What the API calls this value in requests. Examples: username, api_token, token, client_id, api_key, consumer_key, app_id',
				'default'     => 'username',
			),
			'password_field'    => array(
				'type'        => 'text',
				'label'       => 'Secret Field Name',
				'description' => 'What the API calls this value in requests. Examples: password, api_secret, secret, client_secret, consumer_secret, app_secret',
				'default'     => 'password',
			),
			'token_body_format' => array(
				'type'        => 'select',
				'label'       => 'Token Request Body Format',
				'description' => 'How to send credentials to the token endpoint',
				'default'     => 'json',
				'options'     => array(
					'json' => 'JSON (application/json)',
					'form' => 'Form (application/x-www-form-urlencoded)',
				),
			),
			'send_basic_auth'   => array(
				'type'        => 'select',
				'label'       => 'Send Basic Authorization Header',
				'description' => 'Include Authorization: Basic header with the credentials',
				'default'     => 'yes',
				'options'     => array(
					'yes' => 'Yes',
					'no'  => 'No',
				),
			),
			'token_ttl'         => array(
				'type'        => 'number',
				'label'       => 'Token Lifetime (seconds)',
				'description' => 'How long tokens are valid (default: 3600).',
				'default'     => HOUR_IN_SECONDS,
			),
		);
	}

	/**
	 * Validate authentication configuration
	 *
	 * @param array $config Configuration values
	 * @return bool
	 */
	public function validate_config( array $config ): bool {
		return ! empty( $config['token_endpoint'] )
			&& ! empty( $config['username'] )
			&& ! empty( $config['password'] );
	}

	/**
	 * Apply authentication to request
	 *
	 * Adds the bearer token to the Authorization header.
	 *
	 * @param Connection_Profile $profile The connection profile
	 * @param array              $guzzle_options Guzzle request options (passed by reference)
	 * @return void
	 * @throws Exception If authentication fails
	 */
	public function apply_authentication( Connection_Profile $profile, array &$guzzle_options ): void {
		$token = $this->get_stored_token( $profile );

		if ( empty( $token ) ) {
			throw new Exception( 'Failed to obtain bearer token' );
		}

		// Add token to Authorization header
		$guzzle_options['headers']['Authorization'] = 'Bearer ' . $token;
	}

	/**
	 * Check if this handler supports token refresh
	 *
	 * @return bool
	 */
	protected function supports_token_refresh(): bool {
		return true;
	}

	/**
	 * Get stored authentication token
	 *
	 * Returns cached token if available and not expired, otherwise authenticates.
	 *
	 * @param Connection_Profile $profile The connection profile
	 * @return string|null
	 */
	protected function get_stored_token( Connection_Profile $profile ): ?string {
		// Check for cached token
		$cached_token = $profile->get_auth_config_value( 'session_token' );
		$expires_at   = $profile->get_auth_config_value( 'expires_at' );

		if ( ! empty( $cached_token ) && ! empty( $expires_at ) && time() < (int) $expires_at ) {
			return $cached_token;
		}

		// Get new token
		return $this->authenticate( $profile );
	}

	/**
	 * Store authentication token with expiration
	 *
	 * @param Connection_Profile $profile The connection profile
	 * @param string             $token The token to store
	 * @param int|null           $expires_at Unix timestamp when token expires
	 * @return void
	 */
	protected function set_stored_token( Connection_Profile $profile, string $token, ?int $expires_at ): void {
		$profile->set_auth_config_value( 'session_token', $token );
		$profile->set_auth_config_value( 'expires_at', $expires_at !== null ? (int) $expires_at : null );
	}

	/**
	 * Get token expiration timestamp
	 *
	 * @param Connection_Profile $profile The connection profile
	 * @return int|null
	 */
	protected function get_token_expiration( Connection_Profile $profile ): ?int {
		$expires_at = $profile->get_auth_config_value( 'expires_at' );
		if ( empty( $expires_at ) ) {
			return null;
		}

		return (int) $expires_at;
	}

	/**
	 * Clear stored authentication token
	 *
	 * @param Connection_Profile $profile The connection profile
	 * @return void
	 */
	protected function clear_stored_token( Connection_Profile $profile ): void {
		$profile->set_auth_config_value( 'session_token', null );
		$profile->set_auth_config_value( 'expires_at', null );
	}

	/**
	 * Get the configured token lifetime in seconds.
	 *
	 * @param Connection_Profile $profile The connection profile.
	 * @return int
	 */
	protected function get_token_ttl( Connection_Profile $profile ): int {
		$token_ttl = (int) $profile->get_auth_config_value( 'token_ttl', HOUR_IN_SECONDS );
		if ( $token_ttl <= 0 ) {
			$token_ttl = HOUR_IN_SECONDS;
		}

		return $token_ttl;
	}

	/**
	 * Lifecycle hook: Called to refresh authentication tokens
	 *
	 * @param Connection_Profile $profile The connection profile
	 * @return bool
	 */
	public function on_token_refresh( Connection_Profile $profile ): bool {
		try {
			$token = $this->authenticate( $profile );

			return ! empty( $token );
		} catch ( Exception $e ) {
			gc_api_alchemist()->log_error( 'Dynamic bearer token refresh failed: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Authenticate with API and get a bearer token
	 *
	 * @param Connection_Profile $profile The connection profile
	 * @return string|null The bearer token or null on failure
	 * @throws Exception If authentication fails
	 */
	protected function authenticate( Connection_Profile $profile ) {
		$token_endpoint    = $profile->get_auth_config_value( 'token_endpoint' );
		$username          = $profile->get_auth_config_value( 'username' );
		$password          = $profile->get_auth_config_value( 'password' );
		$username_field    = $profile->get_auth_config_value( 'username_field', 'username' );
		$password_field    = $profile->get_auth_config_value( 'password_field', 'password' );
		$token_body_format = $profile->get_auth_config_value( 'token_body_format', 'json' );
		$send_basic_auth   = $profile->get_auth_config_value( 'send_basic_auth', 'yes' );

		if ( empty( $token_endpoint ) || empty( $username ) || empty( $password ) ) {
			throw new Exception( 'Token endpoint, username, and password are required' );
		}

		$token_endpoint = $this->resolve_token_endpoint_url( $profile, $token_endpoint );

		if ( empty( $username_field ) ) {
			$username_field = 'username';
		}

		if ( empty( $password_field ) ) {
			$password_field = 'password';
		}

		$payload = array(
			$username_field => $username,
			$password_field => $password,
		);

		$headers = array(
			'Accept' => 'application/json',
		);

		if ( $send_basic_auth !== 'no' ) {
			$headers['Authorization'] = 'Basic ' . base64_encode( $username . ':' . $password );
		}

		if ( $token_body_format === 'form' ) {
			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
			$body                    = $payload;
		} else {
			$headers['Content-Type'] = 'application/json';
			$body                    = wp_json_encode( $payload );
			if ( $body === false ) {
				throw new Exception( 'Failed to encode token request payload: ' . json_last_error_msg() );
			}
		}

		// Make authentication request
		$response = wp_remote_post( $token_endpoint, array(
			'headers' => $headers,
			'body'    => $body,
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Token request failed: ' . $response->get_error_message() );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$data        = json_decode( $body, true );

		// Check for successful authentication (HTTP 2xx)
		if ( $status_code < 200 || $status_code >= 300 ) {
			$error_message = '';
			if ( is_array( $data ) ) {
				$error_message = $data['messages'][0]['message']
					?? $data['message']
					?? $data['error']
					?? $data['detail']
					?? '';
			}

			if ( empty( $error_message ) ) {
				$error_message = trim( wp_strip_all_tags( (string) $body ) );
			}

			if ( empty( $error_message ) ) {
				$error_message = 'Unknown error';
			}

			throw new Exception( sprintf( 'Authentication failed (HTTP %d): %s', $status_code, $error_message ) );
		}

		// Extract token from response (JSON or raw string)
		if ( is_array( $data ) ) {
			$token = $data['response']['token'] ?? $data['access_token'] ?? $data['token'] ?? null;
		} elseif ( is_string( $data ) && $data !== '' ) {
			$token = $data;
		} else {
			$token = trim( (string) $body );
			$token = trim( $token, "\" \t\n\r\0\x0B" );
		}

		if ( empty( $token ) ) {
			throw new Exception( 'No token returned from authentication' );
		}

		// Store token with expiration
		$this->set_stored_token( $profile, $token, time() + $this->get_token_ttl( $profile ) );

		return $token;
	}

	/**
	 * Resolve the token endpoint URL against the profile base URL when relative.
	 *
	 * @param Connection_Profile $profile The connection profile.
	 * @param string             $token_endpoint The configured token endpoint.
	 * @return string
	 */
	protected function resolve_token_endpoint_url( Connection_Profile $profile, string $token_endpoint ): string {
		$token_endpoint = trim( $token_endpoint );
		if ( empty( $token_endpoint ) ) {
			return $token_endpoint;
		}

		$parsed = wp_parse_url( $token_endpoint );
		if ( ! empty( $parsed['scheme'] ) && ! empty( $parsed['host'] ) ) {
			return $token_endpoint;
		}

		$base_url = $profile->get_base_url();
		if ( empty( $base_url ) ) {
			throw new Exception( 'Token endpoint must be a full URL when no base URL is configured.' );
		}

		return $profile->build_url( $token_endpoint );
	}
}

// Register the handler
add_action( 'plugins_loaded', function() {
	if ( ! function_exists( 'gcapi_register_auth_handler' ) ) {
		return;
	}

	gcapi_register_auth_handler( new GCAPI_Dynamic_Bearer_Auth_Handler() );
} );
