<?php
/**
 * Gravity Perks // Limit Submissions // Collective Limits for Roles
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 *
 * By default, role-based limits are applied per user. This means each user of the specified
 * role can submit the form up to the specified limit.
 * 
 * Use this snippet to change this behavior so that the limit is applied collectively to all
 * users of the specified role.
 */
 add_filter( 'gpls_apply_role_limit_per_user', '__return_false' );
