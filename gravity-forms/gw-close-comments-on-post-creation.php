<?php
/**
 * Gravity Wiz // Gravity Forms // Close Comments when Post Created via Gravity Forms
 * http://gravitywiz.com/2012/04/25/close-comments-when-post-created-via-gravity-forms/
 *
 * This simple snippet allows you to close comments on posts created via a Gravity Form.
 *
 * Plugin Name: Close Comments when Post Created via Gravity Forms
 * Plugin URI: http://gravitywiz.com/2012/04/25/close-comments-when-post-created-via-gravity-forms/
 * Description: This simple snippet allows you to close comments on posts created via a Gravity Form.
 * Author: Gravity Wiz
 * Version: 0.1
 * Author URI: http://gravitywiz.com
 */
// update the '6' to the ID of your form
add_filter('gform_post_data_6', 'gform_close_comments');
function gform_close_comments($post_data){

 $post_data['comment_status'] = 'closed';

 return $post_data;
}
