<?php
/**
 * Gravity Wiz // Gravity Forms // Submit to Access
 *
 * Require that a form be submitted before a post or page can be accessed.
 *
 * Plugin Name: Gravity Forms Submit to Access
 * Plugin URI:  https://gravitywiz.com/submit-gravity-form-access-content/
 * Description: Require that a form be submitted before a post or page can be accessed.
 * Author:      Gravity Wiz
 * Version:     1.11
 * Author URI:  https://gravitywiz.com
 */
class GW_Submit_Access {

	private static $instance = null;

	private function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'requires_submission_message' => __( 'Oops! You do not have access to this page.' ),
			'bypass_cache'                => false,
			'loading_message'             => '', // set later so we can use GFCommon to get URL to GF spinner,
			'enable_user_meta'            => false,
			'is_persistent'               => true,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public static function get_instance( $args = array() ) {
		if ( self::$instance === null ) {
			self::$instance = new GW_Submit_Access( $args );
		}
		return self::$instance;
	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '2.4.7', '>=' ) ) {
			return;
		}

		// setting later so we can use GFCommon::get_base_url() to get GF's spinner URL
		if ( empty( $this->_args['loading_message'] ) ) {
			$this->_args['loading_message'] = '<span class="gwsa-loading">Loading content... <img src="' . GFCommon::gf_global( false, true )['spinnerUrl'] . '" /></span>';
		}

		add_action( 'wp', array( $this, 'check_global_requirements' ), 5 );
		add_action( 'admin_init', array( $this, 'check_global_requirements' ), 5 );
		add_action( 'wp', array( $this, 'check_for_access_redirect' ) );

		add_action( 'gform_pre_submission', array( $this, 'add_submitted_form' ) );
		add_filter( 'the_content', array( $this, 'maybe_hide_the_content' ) );

		add_action( 'wp_ajax_gwas_get_content', array( $this, 'ajax_get_content' ) );
		add_action( 'wp_ajax_nopriv_gwas_get_content', array( $this, 'ajax_get_content' ) );

		add_shortcode( 'gwsa', array( $this, 'do_gwsa_shortcoee' ) );

	}

	public function check_global_requirements() {

		if ( current_user_can( 'administrator' ) && is_admin() ) {
			return;
		}

		$global_posts = $this->get_global_posts();
		if ( empty( $global_posts ) ) {
			return;
		}

		// if we're already on a global post, don't do anything
		$object = get_queried_object();
		if ( is_a( $object, 'WP_Post' ) && in_array( $object->ID, wp_list_pluck( $global_posts, 'ID' ) ) ) {
			return;
		}

		foreach ( $global_posts as $global_post ) {
			if ( ! $this->has_access( $global_post->ID ) ) {
				wp_redirect( get_permalink( $global_post ) );
				exit;
			}
		}

	}

	public function get_global_posts() {

		$query = array(
			'post_type'  => 'any',
			'meta_query' => array(
				'relation' => 'or',
				array(
					'key'   => 'gwsa_require_submission',
					'value' => 'global',
				),
			),
		);

		if ( is_user_logged_in() ) {
			$query['meta_query'][] = array(
				'key'   => 'gwsa_require_submission',
				'value' => 'global_logged_in',
			);
		}

		$query        = apply_filters( 'gfsa_get_global_posts_query', $query );
		$global_posts = get_posts( $query );

		return $global_posts;
	}

	public function check_for_access_redirect() {
		global $post;

		if ( is_admin() ) {
			return;
		}

		if ( ! $post || $this->has_access( $post->ID ) ) {
			return;
		}

		$url = $this->get_requires_submission_redirect( $post->ID );
		if ( $url ) {
			wp_redirect( $url );
		}

	}

	public function maybe_hide_the_content( $content ) {
		global $post;

		if ( ! $this->requires_access( $post->ID ) ) {
			return $content;
		}

		if ( $this->_args['bypass_cache'] ) {
			$content = $this->cache_bypass_content( $content );
		} elseif ( ! $this->has_access( $post->ID ) ) {
			$content = $this->get_requires_submission_message( $post->ID );
		}

		return $content;
	}

	public function cache_bypass_content( $content ) {
		global $post;

		ob_start();

        // Output the form scripts (including jQuery), otherwise submission may not work.
		$form_ids = $this->get_form_ids( $post->ID );
		$form     = GFAPI::get_form( $form_ids[0] );
		require_once( GFCommon::get_base_path() . '/form_display.php' );
		GFFormDisplay::print_form_scripts( $form, true );
		?>

		<div id="gwsa-content">
			<?php echo $this->_args['loading_message']; ?>
		</div>

		<script type="text/javascript">

			var ajaxUrl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

			( function( $ ) {

				$.post( ajaxUrl, {
					action: 'gwas_get_content',
					post:   <?php echo $post->ID; ?>,
				}, function( response ) {
					$( '#gwsa-content' ).html( response );
				} );

			} )( jQuery );

		</script>

		<?php

		return ob_get_clean();
	}

	public function ajax_get_content() {

		$post_id = rgpost( 'post' );

		if ( $this->has_access( $post_id ) ) {

			$post            = get_post( $post_id );
			$GLOBALS['post'] = get_post( $post_id );
			setup_postdata( $post );

			remove_filter( 'the_content', array( $this, 'maybe_hide_the_content' ) );

			// use the_content() so we get the content exactly as WP would have originally displayed it
			ob_start();
			the_content();
			$content = ob_get_clean();

		} else {

			$content = $this->get_requires_submission_message( $post_id );

		}

		die( $content );
	}

	public function get_requires_submission_message( $post_id ) {

		$requires_submission_message = get_post_meta( $post_id, 'gwsa_requires_submission_message', true );

		if ( ! $requires_submission_message ) {
			$requires_submission_message = $this->_args['requires_submission_message'];
		}

		$contains_form_merge_tag = strpos( $requires_submission_message, '{form}' ) !== false;

		$form_ids = $this->get_form_ids( $post_id );

		if ( ! empty( $form_ids ) ) {

			ob_start();
			$form = GFAPI::get_form( $form_ids[0] );
			require_once( GFCommon::get_base_path() . '/form_display.php' );
			GFFormDisplay::print_form_scripts( $form, true );
			gravity_form( $form_ids[0], false, false, false, array(), $this->_args['bypass_cache'] );
			$form_markup = ob_get_clean();

			$requires_submission_message = $contains_form_merge_tag ? str_replace( '{form}', $form_markup, $requires_submission_message ) : $requires_submission_message . $form_markup;

			// Replace form's action URL.
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$search  = remove_query_arg( 'gf_token' );
				$replace = get_permalink( rgpost( 'post' ) );
				// get_permalink() defaults to whatever protocol the site url is configured for; we need to be sure
				// if the form is being loaded on an https page, that our action url is also https.
				if ( is_ssl() ) {
					$replace = str_replace( 'http://', 'https://', $replace );
				}
				$requires_submission_message = str_replace( $search, $replace, $requires_submission_message );
			}
		}

		return do_shortcode( $requires_submission_message );
	}

	public function get_requires_submission_redirect( $post_id ) {
		return get_post_meta( $post_id, 'gwsa_requires_submission_redirect', true );
	}

	public function has_access( $post_id ) {

		if ( ! $this->requires_access( $post_id ) ) {
			return true;
		}

		$form_ids   = $this->get_form_ids( $post_id );
		$per_page   = $this->requires_submission_per_page( $post_id );
		$has_access = $this->has_submitted_form( $form_ids, $per_page, $post_id );

		/**
		 * Filter whether the current viewer has access to the given post.
		 *
		 * @since 1.10
		 *
		 * @param bool $has_access Whether the current viewer has access.
		 * @param int  $post_id    The ID of the post for which access is being assessed.
		 */
		$has_access = apply_filters( 'gfsa_has_access', $has_access, $post_id );

		return $has_access;
	}

	public function has_submitted_form( $form_ids, $per_page, $post_id ) {

		$submitted_forms = $this->get_submitted_forms();

		// if not form-specific and at least one form is submitted, user has access
		if ( empty( $form_ids ) && ! empty( $submitted_forms ) ) {
			return true;
		}

		if ( ! $per_page ) {

			// has specifically required form been submitted?
			$matching_form_ids = array_intersect( $form_ids, array_keys( $submitted_forms ) );
			if ( ! empty( $matching_form_ids ) ) {
				return true;
			}
		} else {

			foreach ( $form_ids as $form_id ) {
				// If form has never been submitted, access is not granted
				if ( empty( $submitted_forms[ $form_id ] ) ) {
					return false;
				}

				// If current post ID is not in the submitted form's array of post IDs, do not grant access
				if ( ! in_array( $post_id, $submitted_forms[ $form_id ] ) ) {
					return false;
				}
			}

			return true;

		}

		return false;
	}

	public function requires_access( $post_id ) {
		return get_post_meta( $post_id, 'gwsa_require_submission', true ) == true;
	}

	public function requires_submission_per_page( $post_id ) {
		return get_post_meta( $post_id, 'gwsa_require_submission', true ) === 'per_page';
	}

	public function get_submitted_forms() {

		// always check the cookie first; will allow user meta vs cookie to be set per page in the future
		$submitted_forms = (array) json_decode( stripslashes( rgar( $_COOKIE, 'gwsa_submitted_forms' ) ) );

		// if user meta is enabled, merge forms stored there as well
		if ( $this->_args['enable_user_meta'] ) {
			$user_meta_forms = (array) wp_get_current_user()->get( 'gwsa_submitted_forms' );
			$submitted_forms = array_merge_recursive( $submitted_forms, $user_meta_forms );
		}

		return array_filter( $submitted_forms );
	}

	public function add_submitted_form( $form ) {

		$submitted_forms = $this->get_submitted_forms();
		$form_id         = $form['id'];

		if ( ! headers_sent() ) {

			if ( ! isset( $submitted_forms[ $form_id ] ) || ! is_array( $submitted_forms[ $form_id ] ) ) {
				$submitted_forms[ $form_id ] = array();
			}

			$submitted_forms[ $form_id ][] = url_to_postid( GFFormsModel::get_current_page_url() );

			if ( $this->_args['enable_user_meta'] && is_user_logged_in() ) {
				update_user_meta( get_current_user_id(), 'gwsa_submitted_forms', $submitted_forms );
			} else {
				$expiration = $this->_args['is_persistent'] ? strtotime( '+1 year' ) : null;
				setcookie( 'gwsa_submitted_forms', json_encode( $submitted_forms ), $expiration, '/' );
			}
		}

	}

	public function get_form_ids( $post_id ) {
		return array_filter( array_map( 'trim', explode( ',', get_post_meta( $post_id, 'gwsa_form_ids', true ) ) ) );
	}

}

function gw_submit_to_access( $args = array() ) {
	return GW_Submit_Access::get_instance( $args );
}

gw_submit_to_access();
