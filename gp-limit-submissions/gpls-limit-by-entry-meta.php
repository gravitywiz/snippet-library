<?php
/**
 * Gravity Perks // Limit Submission // Limit by Entry Meta
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 *
 * This snippet allows you use a Limit Submissions feed to limit by entry meta by setting a filter in the feed name.
 * This is a stop-gap approach until we can add full support for entry meta to core.
 *
 * # Usage Instructions #
 *
 * 1. Add your feed name (e.g. Limit by Passed Quizzes).
 * 2. Append your entry meta filter (e.g. Limit by Passed Quizzes [gquiz_is_pass:1]).
 *    The format for the filter is [meta_key:meta_value].
 * 3. Configure the rest of the feed as normal but leave the default Rules in place (e.g. IP -> Each IP).
 *    You cannot add additional rules.
 */
add_filter( 'gpls_rule_groups', function( $rule_groups ) {

	foreach ( $rule_groups as $rule_group ) {
		if ( preg_match_all( '/\[(.+?):(.+?)\]/', $rule_group->name, $matches, PREG_SET_ORDER ) ) {
			$func = function( $gpls_ruletest ) use ( $matches, &$func ) {
				global $wpdb;
				$gpls_ruletest->join[]  = "INNER JOIN {$wpdb->prefix}gf_entry_meta em ON em.entry_id = e.id";
				$gpls_ruletest->where[] = $wpdb->prepare( "\n( em.meta_key = %s AND em.meta_value = %s )", $matches[0][1], $matches[0][2] );
				remove_action( 'gpls_before_query', $func );
			};
			add_action( 'gpls_before_query', $func );
		}
	}

	return $rule_groups;
} );
