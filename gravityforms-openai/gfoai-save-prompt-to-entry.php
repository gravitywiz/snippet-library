<?php
/**
 * Gravity Wiz // Gravity Forms OpenAI // Save Prompt to Entry
 *
 * Sometimes, a prompt could have a lot of conditionals, so you can't simply copy it using a
 * Live Merge Tag. This snippet grabs the final prompt that's ultimately sent to OpenAI and
 * stores it along with the entry.
 *
 * Instructions:
 *  1. Install per https://gravitywiz.com/how-do-i-install-a-snippet/
 */

class GFOAI_Save_Prompt_To_Entry {

	private $_args;
	private $_message;

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
			'feed_id'  => false,
		) );

		$this->_message = '';

		add_filter( 'gf_openai_request_body', array( $this, 'save_request_body' ), 10, 3 );
		add_action( 'gform_post_process_feed', array( $this, 'process_feed' ), 10, 2 );
	}

	public function save_request_body( $body, $endpoint, $feed ) {
		if ( ! $this->_args['field_id'] || ( $this->_args['form_id'] && $feed['form_id'] != $this->_args['form_id'] ) ) {
			return $body;
		}

		$this->_message = $body['messages'][0]['content'];
		return $body;
	}

	public function process_feed( $_feed, $entry ) {
		if ( $_feed['id'] == $this->_args['feed_id'] ) {
			$entry[ $this->_args['feed_id'] ] = $this->_message;
			GFAPI::update_entry( $entry );
		}
	}
}

// Update to the targetted form id, field id of the hidden field on form where we store the prompt, and Open AI feed id (in sequence).
new GFOAI_Save_Prompt_To_Entry( array(
	'form_id'  => 321,
	'field_id' => 3,
	'feed_id'  => 113,
) );
