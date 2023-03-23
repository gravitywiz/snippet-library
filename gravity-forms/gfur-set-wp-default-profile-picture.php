<?php
/**
 * Gravity Forms // User Registration // Setting WordPress Default Profile Picture
 * https://gravitywiz.com/
 *
 * Video: https://www.loom.com/share/e36fe58e7b8740248d287e9492c0ee7a
 */
add_filter( 'pre_get_avatar', function( $avatar, $id_or_email, $args ) {
  // Update "profile_picture" to whatever custom field (e.g. user meta) you will be saving the profile picture to.
	$profile_picture = get_user_meta( $id_or_email, 'profile_picture', true );
	if ( $profile_picture ) {
		$avatar = sprintf( '<img alt="" src="%s" class="avatar avatar-64 photo" height="%d" width="%d" loading="%s" decoding="%s">', $profile_picture, $args['width'], $args['height'], $args['loading'], $args['decoding'] );
		add_filter( 'user_profile_picture_description', '__return_false' );
	}
	return $avatar;
}, 10, 3 );
