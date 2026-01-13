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
	public function get_config_fields(): array {
		return [
			'token_endpoint' => [
				'type'        => 'text',
				'label'       => 'Token Endpoint URL',
				'description' => 'The API endpoint that generates authentication tokens',
				'required'    => true,
			],
			'username' => [
				'type'        => 'text',
				'label'       => 'Username',
				'description' => 'API username or client ID',
				'required'    => true,
			],
			'password' => [
				'type'        => 'password',
				'label'       => 'Password',
				'description' => 'API password or client secret',
				'required'    => true,
			],
		];
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

		if ( ! empty( $cached_token ) && ! empty( $expires_at ) && time() < $expires_at ) {
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
		$profile->set_auth_config_value( 'expires_at', $expires_at );
	}

	/**
	 * Get token expiration timestamp
	 *
	 * @param Connection_Profile $profile The connection profile
	 * @return int|null
	 */
	protected function get_token_expiration( Connection_Profile $profile ): ?int {
		return $profile->get_auth_config_value( 'expires_at' );
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
	 * Lifecycle hook: Called to refresh authentication tokens
	 *
	 * @param Connection_Profile $profile The connection profile
	 * @return bool
	 */
	public function on_token_refresh( Connection_Profile $profile ): bool {
		try {
			$token = $this->authenticate( $profile );

			if ( empty( $token ) ) {
				return false;
			}

			// Store token with 1-hour expiration (adjust as needed)
			$this->set_stored_token( $profile, $token, time() + HOUR_IN_SECONDS );

			return true;
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
	protected function authenticate( Connection_Profile $profile ): ?string {
		$token_endpoint = $profile->get_auth_config_value( 'token_endpoint' );
		$username       = $profile->get_auth_config_value( 'username' );
		$password       = $profile->get_auth_config_value( 'password' );

		if ( empty( $token_endpoint ) || empty( $username ) || empty( $password ) ) {
			throw new Exception( 'Token endpoint, username, and password are required' );
		}

		// Make authentication request
		$response = wp_remote_post( $token_endpoint, [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
			],
			'body'    => wp_json_encode( [
				'username' => $username,
				'password' => $password,
			] ),
			'timeout' => 30,
		] );

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Token request failed: ' . $response->get_error_message() );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$data        = json_decode( $body, true );

		// Check for successful authentication (HTTP 200)
		if ( $status_code !== 200 ) {
			$error_message = $data['messages'][0]['message'] ?? 'Unknown error';
			throw new Exception( 'Authentication failed: ' . $error_message );
		}

		// Extract token from response
		$token = $data['response']['token'] ?? $data['access_token'] ?? $data['token'] ?? null;

		if ( empty( $token ) ) {
			throw new Exception( 'No token returned from authentication' );
		}

		// Store token with expiration
		$this->set_stored_token( $profile, $token, time() + HOUR_IN_SECONDS );

		return $token;
	}
}

// Register the handler
add_action( 'plugins_loaded', function() {
	if ( ! function_exists( 'gcapi_register_auth_handler' ) ) {
		return;
	}

	gcapi_register_auth_handler( new GCAPI_Dynamic_Bearer_Auth_Handler() );
} );
