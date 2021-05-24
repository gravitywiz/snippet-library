<?php
/**
 * Gravity Perks // GP Populate Anything // Add Support for Aggregate Functions
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Perform  calculations on the values in a field/column and return a single value.
 * To use the snippet you'd use a custom template type and enter the merge tag manually adding the specific property
 * (e.g. column, field) you want to target, like this: {sum:your_property}
 * 
 * Merge Tags
 * {sum:ID} - The SUM merge tag adds up all values in a specific column/field.
 * {avg:ID} - The AVG merge tag adds up all values and then calculates the average.
 * {min:ID} - The MIN merge tag finds the minimum value in a specific column/field. 
 * {max:ID} - The MAX merge tag finds the maximum value in a specific column/field. 
 *
 * Plugin Name:  GP Populate Anything - Add Support for Aggregate Functions
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Perform  calculations on the values in a field/column and return a single value.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
function replace_template_sum_merge_tags($template_value, $field, $template, $populate, $object, $object_type, $objects) {
	$sum_regex = '/{sum:(.+)}/';
    preg_match_all( $sum_regex, $template_value, $matches, PREG_SET_ORDER );
	if ( $matches ) {
    	foreach ( $matches as $match ) {
    		$full_match = $match[0];
    		$merge_tag  = $match[1];
    		$sum = 0;
    		foreach ($objects as $object_index => $object) {
    		    $value = $object_type->get_object_prop_value( $object, $merge_tag );
    		    if (is_numeric($value)) {
    		        $sum += floatval($value);
    		    }
    		}
    		$template_value = str_replace( $full_match, $sum, $template_value );
    	}
	}
	return $template_value;
}
 
function replace_template_avg_merge_tags($template_value, $field, $template, $populate, $object, $object_type, $objects) {
	$avg_regex = '/{avg:(.+)}/';
    preg_match_all( $avg_regex, $template_value, $matches, PREG_SET_ORDER );
	if ( $matches ) {
    	foreach ( $matches as $match ) {
    		$full_match = $match[0];
    		$merge_tag  = $match[1];
    		$sum = 0;
			$count = count($objects);
    		foreach ($objects as $object_index => $object) {
    		    $value = $object_type->get_object_prop_value( $object, $merge_tag );
    		    if (is_numeric($value)) {
    		        $sum += floatval($value);
    		    }
    		}
			$avg = ($count > 0) ? ($sum / $count) : 0;
    		$template_value = str_replace( $full_match, $avg, $template_value );
    	}
	}
	return $template_value;
}
 
function replace_template_min_merge_tags($template_value, $field, $template, $populate, $object, $object_type, $objects) {
	$min_regex = '/{min:(.+)}/';
    preg_match_all( $min_regex, $template_value, $matches, PREG_SET_ORDER );
	if ( $matches ) {
    	foreach ( $matches as $match ) {
    		$full_match = $match[0];
    		$merge_tag  = $match[1];
    		$min = null;
    		foreach ($objects as $object_index => $object) {
    		    $value = $object_type->get_object_prop_value( $object, $merge_tag );
    		    if (is_numeric($value)) {
    		        $value = floatval($value);
					if (is_null($min) || ($value < $min)) {
						$min = $value;
					}
    		    }
    		}
			if (is_null($min)) {
				$min = " - ";
			}
    		$template_value = str_replace( $full_match, $min, $template_value );
    	}
	}
	return $template_value;
}
 
function replace_template_max_merge_tags($template_value, $field, $template, $populate, $object, $object_type, $objects) {
	$max_regex = '/{max:(.+)}/';
    preg_match_all( $max_regex, $template_value, $matches, PREG_SET_ORDER );
	if ( $matches ) {
    	foreach ( $matches as $match ) {
    		$full_match = $match[0];
    		$merge_tag  = $match[1];
    		$max = null;
    		foreach ($objects as $object_index => $object) {
    		    $value = $object_type->get_object_prop_value( $object, $merge_tag );
    		    if (is_numeric($value)) {
    		        $value = floatval($value);
					if (is_null($max) || ($value > $max)) {
						$max = $value;
					}
    		    }
    		}
			if (is_null($max)) {
				$max = " - ";
			}
    		$template_value = str_replace( $full_match, $max, $template_value );
    	}
	}
	return $template_value;
}
 
add_filter( 'gppa_process_template', 'replace_template_sum_merge_tags', 2, 7 );
add_filter( 'gppa_process_template', 'replace_template_avg_merge_tags', 2, 7 );
add_filter( 'gppa_process_template', 'replace_template_min_merge_tags', 2, 7 );
add_filter( 'gppa_process_template', 'replace_template_max_merge_tags', 2, 7 );
 
 
