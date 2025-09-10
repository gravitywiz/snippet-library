<?php

/**
 * Gravity Perks // Submit to Access // Enable for all Posts of Type
 * https://gravitywiz.com/documentation/gravity-forms-submit-to-access/
 *
 * Enables GPSA for all posts of a specific type.
 *
 * Instructions:
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 * 2. Customize instantiation of the GPSA_Enable_For_All_Posts_Of_Type
 *    class by passing the `post_type` and `settings` you would like
 *    to use. (See "Configuration" section at the bottom of the snippet
 *    for an example)
 *
 *
 * @phpstan-type AccessExpiration array{
 *      type: 'session' | 'never' | 'custom',
 *      duration: array{
 *          value: int,
 *          unit: 'years' | 'months' | 'weeks' | 'days' | 'hours' | 'minutes' | 'seconds',
 *      }
 * }
 *
 * @phpstan-type AccessBehavior 'show_message' | 'redirect'
 *
 * @phpstan-type GPSADocumentSettings array{
 *      gpsa_access: AccessExpiration,
 *      gpsa_enabled: bool,
 *      gpsa_content_loading_message: string,
 *      gpsa_form_redirect_path: string,
 *      gpsa_require_unique_form_submission: bool,
 *      gpsa_required_form_ids: array<int>,
 *      gpsa_requires_access_message: string,
 *      gpsa_access_behavior: AccessBehavior
 * }
 */
class GPSA_Enable_For_All_Posts_Of_Type {
	/**
	* @var boolean
	*/
	public static $post_type;

	/**
	 * @var GPSADocumentSettings
	 */
	public static $settings;

	/**
	 * @param array{
	 *  post_type: string
	 *  settings: GPSADocumentSettings
	 * } $args
	*/
	public function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'post_type' => 'post',
			'settings'  => array(),
		) );

		self::$post_type = $args['post_type'];
		self::$settings  = wp_parse_args( $args['settings'], array(
			'gpsa_enabled' => true,
		) );

		add_filter( 'gpsa_supported_post_types', array( self::class, 'ensure_supported_post_types' ), 10, 1 );
		add_filter( 'gpsa_document_settings', array( self::class, 'override_document_level_settings' ), 10, 2 );
	}

	public static function ensure_supported_post_types( $post_types ) {
		if ( ! in_array( self::$post_type, $post_types ) ) {
			$post_types[] = self::$post_type;
		}

		return $post_types;
	}

	public static function override_document_level_settings( $settings, $post_id ) {
		$post = get_post( $post_id );
		if ( $post->post_type === self::$post_type ) {
			$settings = array_merge(
				$settings,
				self::$settings
			);
		}

		return $settings;
	}
}

// Configuration:

/**
 * Minimal configuration to enable for all posts of type `drum-machine`.
*/
new GPSA_Enable_For_All_Posts_Of_Type(
	array(
		'post_type' => 'drum-machine', // Update `drum-machine` to match the post type you want to target.
		'settings'  => array(
			'gpsa_required_form_ids' => array( 3 ), // UPDATE `1` with the form id you want to require.
			// optionally add other settings to the array. See the GPSADocumentSettings in the above
			// doc blocks for available options.
		),
	)
);
