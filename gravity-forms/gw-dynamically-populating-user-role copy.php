<?php
/**
* Gravity Wiz // Gravity Forms // Set Post Status by Field Value (Advanced)
* https://gravitywiz.com/set-post-status-by-field-value-advanced/
*/
// update "123" to the ID of your form
add_filter('gform_post_data_123', 'gform_dynamic_post_status', 10, 3);
function gform_dynamic_post_status($post_data, $form, $entry) {

 // update "5" to the ID of your custom post status field
 	if($entry[4]) {
 		switch($entry[4]) {
 		case 'Yes, please review my post.':
 			$post_data['post_status'] = 'pending';
 		break;
 		case 'No, please publish my post.':
 			$post_data['post_status'] = 'publish';
 		break;
 		}
 	}
	return $post_data;
}
