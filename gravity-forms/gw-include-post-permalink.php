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
 * Version:      1.1
 * Author URI:   https://gravitywiz.com
 */
class GWPostPermalink {

	function __construct() {
		add_filter( 'gform_is_feed_asynchronous', array( $this, 'disable_async' ), 10, 4 );
		add_filter( 'gform_custom_merge_tags', array( $this, 'add_custom_merge_tag' ), 10, 4 );
		add_filter( 'gform_replace_merge_tags', array( $this, 'replace_merge_tag' ), 10, 3 );

	}

	function disable_async ( $is_async, $feed, $entry, $form ) {
		if ( rgar( $feed, 'addon_slug' ) === 'gravityformsadvancedpostcreation' ) {
			return false;
		}
		return $is_async;
	}

	function add_custom_merge_tag( $merge_tags, $form_id, $fields, $element_id ) {

		if ( ! $this->is_post_generating_form( $form_id, $fields ) ) {
			return $merge_tags;
		}

		$merge_tags[] = array(
			'label' => 'Post Permalink',
			'tag'   => '{post_permalink}',
		);

		return $merge_tags;
	}

	function replace_merge_tag( $text, $form, $entry ) {

		if ( ! preg_match_all( '/{post_permalink(:.+)?}/', $text, $matches ) ) {
			return $text;
		}

		foreach ( $matches as $match ) {

			$custom_merge_tag = $match[0];

			$post_id = $this->get_post_id_by_entry( $entry );
			if ( ! $post_id ) {
				return $text;
			}

			$post_permalink = get_permalink( $post_id );
			$text           = str_replace( $custom_merge_tag, $post_permalink, $text );

		}

		return $text;
	}

	function get_post_id_by_entry( $entry ) {
		$post_id = rgar( $entry, 'post_id' );
		if ( ! $post_id && function_exists( 'gf_advancedpostcreation' ) ) {
			$entry_post_ids = gform_get_meta( $entry['id'], gf_advancedpostcreation()->get_slug() . '_post_id' );
			if ( ! empty( $entry_post_ids ) ) {
				$post_id = $entry_post_ids[0]['post_id'];
			} else {
				$posts = get_posts( array(
					'post_type'  => 'any',
					'meta_key'   => '_' . gf_advancedpostcreation()->get_slug() . '_entry_id',
					'meta_value' => $entry['id'],
				) );
				if ( ! empty( $posts ) ) {
					$post_id = $posts[0]->ID;
				}
			}
		}
		return $post_id;
	}

	function is_post_generating_form( $form_id, $fields ) {

		if ( GFCommon::has_post_field( $fields ) ) {
			return true;
		}

		if ( function_exists( 'gf_advancedpostcreation' ) && gf_advancedpostcreation()->has_feed( $form_id ) ) {
			return true;
		}

		return false;
	}

}

new GWPostPermalink();
