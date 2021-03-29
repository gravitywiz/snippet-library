<?php
/**
 * Gravity Wiz // Gravity Forms // Schedule a Post by Date Field (for Multiple Forms)
 *
 * Schedule your Gravity Form generated posts to be published at a future date, specified by the user via GF Date and Time fields.
 *
 * Plugin Name:  GF — Schedule a Post by Date Field (for Multiple Forms)
 * Plugin URI:   https://gravitywiz.com
 * Description:  Schedule your Gravity Form generated posts to be published at a future date, specified by the user via GF Date and Time fields.
 * Author:       Gravity Wiz
 * Version:      1.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gform_post_data', 'gw_schedule_post_by_date_field_multi_form', 10, 3 );
function gw_schedule_post_by_date_field_multi_form( $post_data, $form, $entry ) {

    $config = array();

    // Change '123' to your form ID; change '7' to your Date field ID; change '8' to your Time field ID.
    $config['123'] = array(
        'date' => 7,
        'time' => 8
    );

    // You can add as many forms as you want...
    $config['433'] = array(
        'date' => 7,
        'time' => 8
    );

    ### don't touch the magic below this line ###

    if( in_array( $form['id'], array_keys( $config ) ) ) {
        $date = $config[ $form['id'] ]['date'];
        $time = $config[ $form['id'] ]['time'];
    }

    $date = rgar( $entry, $date );
    $time = rgar( $entry, $time );

    if( empty( $date ) ) {
        return $post_data;
    }

    if( $time ) {
        list( $hour, $min, $am_pm ) = array_pad( preg_split( '/[: ]/', $time ), 3, false );
        if( strtolower( $am_pm ) == 'pm' ) {
            $hour += 12;
        }
    } else {
        $hour = $min = '00';
    }

    $schedule_date = date( 'Y-m-d H:i:s', strtotime( sprintf( '%s %s:%s:00', $date, $hour, $min ) ) );

    $post_data['post_status']   = 'future';
    $post_data['post_date']     = $schedule_date;
    $post_data['post_date_gmt'] = get_gmt_from_date( $schedule_date );
    $post_data['edit_date']     = true;

    return $post_data;
}
