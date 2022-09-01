<?php
/**
 * Gravity Wiz // Gravity Forms // Post Permalink Merge Tag
 * https://gravitywiz.com/include-post-permalink-gravity-forms-confirmation-notification/
 *
 * Instruction Video: https://www.loom.com/share/6eb48c98d7f246cea6af33cedb84a26f
 *
 * If you are automatically publishing user submitted posts, this is helpful for providing
 * a link immediately to the user where they can preview their newly created post.
 *
 * Plugin Name:  Gravity Forms — Post Permalink Merge Tag
 * Plugin URI:   https://gravitywiz.com/include-post-permalink-gravity-forms-confirmation-notification/
 * Description:  Provides a link immediately to preview their newly created post.
 * Author:       Gravity Wiz
 * Version:      0.2
 * Author URI:   https://gravitywiz.com
 */
class GWPostPermalink {

	function __construct() {

		add_filter( 'gform_custom_merge_tags', array( $this, 'add_custom_merge_tag' ), 10, 4 );
		add_filter( 'gform_replace_merge_tags', array( $this, 'replace_merge_tag' ), 10, 3 );

	}

	function add_custom_merge_tag( $merge_tags, $form_id, $fields, $element_id ) {

		if ( ! $this->is_applicable_form( GFAPI::get_form( $form_id ) ) ) {
			return $merge_tags;
		}

		$merge_tags[] = array(
			'label' => 'Post Permalink',
			'tag'   => '{post_permalink}',
		);

		return $merge_tags;
	}

	function replace_merge_tag( $text, $form, $entry ) {

		$custom_merge_tag = '{post_permalink}';
		if ( strpos( $text, $custom_merge_tag ) === false ) {
			return $text;
		}

		$post_id = rgar( $entry, 'post_id' );

		if ( ! $post_id ) {
			$posts = gform_get_meta( $entry['id'], 'gravityformsadvancedpostcreation_post_id' );
			if ( empty( $posts ) ) {
				return str_replace( $custom_merge_tag, '', $text );
			}
			$post    = array_shift( $posts );
			$post_id = $post['post_id'];
		}

		$post_permalink = get_permalink( $post_id );
		$text           = str_replace( $custom_merge_tag, $post_permalink, $text );

		return $text;
	}

	function is_applicable_form( $form ) {

		if ( GFCommon::has_post_field( $form['fields'] ) ) {
			return true;
		}

		if ( is_callable( array( 'GF_Advanced_Post_Creation', 'get_instance' ) ) && ! empty( GF_Advanced_Post_Creation::get_instance()->get_feeds( $form['id'] ) ) ) {
			return true;
		}

		return false;
	}

}

new GWPostPermalink();
