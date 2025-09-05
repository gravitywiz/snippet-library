<?php

/**
 * Gravity Perks // Submit to Access // Enable for all Posts of Type
 * https://gravitywiz.com/documentation/gravity-forms-submit-to-access/
 *
 * Enables GPSA for all posts of a specific type.
 *
 * @param string $post_type The post type to enable GPSA for.
 */
function gpsa_enable_for_all_posts_of_type( $post_type ) {
	add_filter( 'gpsa_supported_post_types', function( $post_types ) use ( $post_type ) {
		if ( ! in_array( $post_type, $post_types ) ) {
			$post_types[] = $post_type;
		}

		return $post_types;
	});

	add_filter('gpsa_document_settings', function( $settings, $post_id ) use ( $post_type ) {
		$post = get_post( $post_id );
		if ( $post->post_type === $post_type ) {
			/**
			 * @var boolean
			 */
			$settings['gpsa_enabled'] = true;

			/**
			 * @var array<int>
			 */
			$settings['gpsa_required_form_ids'][] = 1;

			/**
			 * optionally override the default message to display while the content is loading
			 * @var string
			 */
			// $settings['gpsa_content_loading_message'] = '';

			/**
			 * optionally redirect to a specific URL where the access form is located
			 * @var string
			 */
			// $settings['gpsa_form_redirect_path'] = '';

			/**
			 * optionally require a unique form submission for every post
			 * @var boolean
			 */
			// $settings['gpsa_require_unique_form_submission'] = false;

			/**
			 * optionally override the default access duration.
			 * @var array{
			 *      type: 'session' | 'never' | 'custom',
			 *      duration: array{
			 *          value: number,
			 *          unit: 'years' | 'months' | 'weeks' | 'days' | 'hours' | 'minutes' | 'seconds',
			 *      }
			 * }
			 */
			// $settings['gpsa_access'] = '';

			/**
			 * optionally override the default requires access message
			 * @var string
			 */
			// $settings['gpsa_requires_access_message'] = '';

			/**
			 * optionally override the default access behavior
			 * @var string 'show_message' | 'redirect'
			 */
			// $settings['gpsa_access_behavior'] = '';
		}

		return $settings;
	}, 10, 2);
}

/**
 * Usage: Enable for all posts of type `drum-machine`.
 * Update this argument to match the `$post_type` of the posts you'd like to target.
*/
gpsa_enable_for_all_posts_of_type( 'drum-machine' );
