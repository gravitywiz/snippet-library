<?php
/**
 * Gravity Wiz // Gravity Forms // Live Preview
 * https://gravitywiz.com/gravity-forms-live-preview/
 *
 * Preview your Gravity forms on the frontend of your website. Adds a "Live Preview" link to the Gravity Forms toolbar .
 *
 * Plugin Name:  Gravity Forms // Live Preview
 * Plugin URI:   https://gravitywiz.com/gravity-forms-live-preview/
 * Description:  Preview your Gravity Forms on the frontend of your website
 * Author:       Gravity Wiz
 * Version:      1.0.1
 * Author URI:   https://gravitywiz.com
 */
class GWLivePreview {

	var $post_type    = 'gw_live_preview';
	var $preview_post = null;

	private $_args = array();

	function __construct( $args = array() ) {

		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		$this->_args = wp_parse_args( $args, array(
			'id'          => 0,
			'title'       => true,
			'description' => true,
			'ajax'        => false,
		) );

		add_action( 'admin_footer', array( $this, 'display_preview_link' ) );

		add_action( 'init', array( $this, 'register_preview_post_type' ) );
		add_action( 'parse_query', array( $this, 'maybe_load_preview_functionality' ) );

	}

	# ADMIN FUNCTIONS

	function display_preview_link() {

		if ( ! $this->is_applicable_admin_page() ) {
			return;
		}

		$form_id = rgget( 'id' );
		$url     = get_post_type_archive_link( $this->post_type ) . '?id=' . $form_id;

		?>

		<script type="text/javascript">
			(function($){
				$(  '<li class="gf_form_toolbar_preview"><a style="position:relative" id="gw-live-preview" href="<?php echo $url; ?>" target="_blank">' +
					'<i class="fa fa-eye" style="position: absolute; text-shadow: 0px 0px 5px rgb(255, 255, 255); z-index: 99; line-height: 7px; left: 0px font-size: 9px; top: 20px; background-color: rgb(243, 243, 243);"></i>' +
					'<i class="fa fa-file-o" style="margin-left: 5px; line-height: 12px; font-size: 18px; position: relative; top: 2px;"></i>' +
					'<span style="padding-left:4px;"><?php _e( 'Live Preview' ); ?></span>' +
					'</a></li>' )
					.insertAfter( 'li.gf_form_toolbar_preview' );
			})(jQuery);
		</script>

		<?php
	}

	function is_applicable_admin_page() {
		return in_array( rgget( 'page' ), array( 'gf_edit_forms', 'gf_entries' ) ) && rgget( 'id' );
	}

	# FRONTEND FUNCTIONS

	function register_preview_post_type() {

		$args = array(
			'label'              => __( 'Form Preview' ),
			'description'        => __( 'A post type created for previewing Gravity Forms forms on the frontend.' ),
			'public'             => false,
			'publicly_queryable' => true,
			'has_archive'        => true,
			'can_export'         => false,
			'supports'           => false,
			'rewrite'            => array(
				'slug'  => 'gravity-forms-preview',
				'feeds' => false,
				'pages' => false,
			),
		);

		register_post_type( $this->post_type, $args );

	}

	function maybe_load_preview_functionality( $wp_query ) {

		if ( ! $wp_query->is_main_query() || ! $this->is_live_preview() ) {
			return;
		}

		$this->live_preview_hooks();

		$wp_query->query_vars['p'] = $this->get_preview_post( 'ID' );

		add_action( 'wp', array( $this, 'populate_post_content_for_gf_scripts_styles' ), 9 );

	}

	public function populate_post_content_for_gf_scripts_styles() {
		global $wp_query;

		foreach ( $wp_query->posts as &$post ) {
			$post->post_content = $this->get_shortcode();
		}

	}

	function get_preview_post( $prop = false ) {

		$preview_posts = get_posts( array( 'post_type' => $this->post_type ) );
		$preview_post  = false;

		// if there are no preview posts, create one
		if ( empty( $preview_posts ) ) {
			$post_id      = wp_insert_post( array(
				'post_type'   => $this->post_type,
				'post_title'  => __( 'Form Preview', 'gravityforms' ),
				'post_status' => 'publish',
			) );
			$preview_post = get_post( $post_id );
		} else {
			// otherwise, use the first preview post (there should only be one)
			$preview_post = $preview_posts[0];
		}

		if ( ! $preview_post ) {
			return false;
		} elseif ( $prop ) {
			return $preview_post->$prop;
		} else {
			return $preview_post;
		}

	}

	function live_preview_hooks() {

		add_filter( 'template_include', array( $this, 'load_preview_template' ), 11 );
		add_filter( 'the_content', array( $this, 'modify_preview_post_content' ) );

	}

	function load_preview_template( $template ) {

		$page_template = get_page_template();
		if ( $page_template ) {
			return $page_template;
		}

		$single_template = get_single_template();
		if ( $single_template ) {
			return $single_template;
		}

		return $template;
	}

	function modify_preview_post_content( $content ) {
		return $this->get_shortcode();
	}

	function get_shortcode( $args = array() ) {

		if ( ! is_user_logged_in() ) {
			return '<p>' . __( 'You need to log in to preview forms.' ) . '</p>' . wp_login_form( array( 'echo' => false ) );
		}

		if ( ! GFCommon::current_user_can_any( 'gravityforms_preview_forms' ) ) {
			return __( 'Oops! It doesn\'t look like you have the necessary permission to preview this form.' );
		}

		if ( empty( $args ) ) {
			$args = $this->get_shortcode_parameters_from_query_string();
		}

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( wp_parse_args( $args, $this->_args ) );

		$title       = $this->is_true_value( $title ) ? 'true' : 'false';
		$description = $this->is_true_value( $description ) ? 'true' : 'false';
		$ajax        = $this->is_true_value( $ajax ) ? 'true' : 'false';

		return "[gravityform id='$id' title='$title' description='$description' ajax='$ajax']";
	}

	function get_shortcode_parameters_from_query_string() {
		return array_filter( array(
			'id'          => rgget( 'id' ),
			'title'       => rgget( 'title' ),
			'description' => rgget( 'description' ),
			'ajax'        => rgget( 'ajax' ),
		) );
	}

	function is_true_value( $value ) {
		return $value === true || intval( $value ) === 1 || strtolower( $value ) === 'true';
	}

	function is_live_preview() {
		return is_post_type_archive( $this->post_type );
	}

}

# Configuration

new GWLivePreview();

# Advanced Congfiguration

// new GWLivePreview( array(
// 'title' => false,
// 'description' => false,
// 'ajax' => true
//) );
