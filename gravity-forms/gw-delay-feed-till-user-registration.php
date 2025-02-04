<?php
/**
 * Gravity Wiz // Gravity Forms // Delay a feed until User Registration is Complete.
 *
 * Instruction Video: https://www.loom.com/share/176456848c614ab6a3792ca1f9ed9c14
 *
 * Delay a specified feed until user registration is complete.
 * This is useful when you have a feed that needs to be processed after user registration is complete.
 */
class GW_Delayed_Feed_Processing_Till_User_Registration {

	private $_args = array();

	public function __construct( $args = array() ) {
		// Ensure feed_slug is always passed; form_id is optional
		if ( empty( $args['feed_slug'] ) ) {
			wp_die( 'Feed slug is required.' );
		}

		$this->_args = wp_parse_args( $args, array(
			'form_id'   => false,
			'feed_slug' => $args['feed_slug'],
		) );

		add_filter( 'gform_is_delayed_pre_process_feed', array( $this, 'maybe_delay_feed' ), 10, 4 );
		add_action( 'gform_user_registered', array( $this, 'process_feed' ), 10, 4 );
		add_filter( 'gform_is_feed_asynchronous', array( $this, 'make_feed_async' ), 10, 4 );

	}

	// Delays the feed until user registration is complete.
	public function maybe_delay_feed( $is_delayed, $form, $entry, $slug ) {

		if ( $slug == $this->_args['feed_slug'] && function_exists( 'gf_user_registration' ) ) {
			$user_id = gf_user_registration()->get_user_by_entry_id( rgar( $entry, 'id' ), true );

			// Delay feed if user is not registered (user_id is blank).
			return rgblank( $user_id );
		}

		return $is_delayed;
	}

	// Processes the feed after the user is registered
	public function process_feed( $user_id, $feed, $entry, $user_pass ) {

		// Feed slug is converted to function name.
		$feed_slug = str_replace( '-', '_', $this->_args['feed_slug'] );
		if ( function_exists( $feed_slug ) ) {
			$feed_class = call_user_func( $feed_slug );

			// Process the feed for the specified form or for all forms if no form_id is passed.
			if ( $feed_class && ( empty( $this->_args['form_id'] ) || rgar( $feed, 'form_id' ) == $this->_args['form_id'] ) ) {
				$feed_class->maybe_process_feed( $entry, $form );
			}
		}
	}

	// Ensure the feed is async, so it can be trigerred when our conditions are met (User Registration).
	public function make_feed_async( $is_asynchronous, $feed, $entry, $form ) {
		if ( rgar( $feed, 'addon_slug' ) == $this->_args['feed_slug'] && $feed['form_id'] == $this->_args['form_id'] ) {
			return false;
		}
		return $is_asynchronous;
	}
}

# Configuration: Pass feed_slug (mandatory) and form_id (optional)
new GW_Delayed_Feed_Processing_Till_User_Registration( array(
	'feed_slug' => 'gc-notion',  // Specify the feed_slug (mandatory)
	'form_id'   => 6,  // Optional: Specify the form_id (or apply to all forms).
) );
