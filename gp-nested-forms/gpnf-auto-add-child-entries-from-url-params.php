<?php
/**
 * Gravity Perks // Nested Forms // Auto-Add Child Entries from URL Parameters
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Automatically add child entries to a Nested Form field, populated from URL query parameters.
 * Useful for things like "Request a Quote" links on product pages, pre-built order links
 * shared by sales reps, or event registration with pre-registered attendees.
 *
 * Any child form field with the "Allow field to be populated dynamically" setting enabled
 * will be populated from the query parameter matching its parameter name.
 *
 * Example: example.com/?product_id=123&qty=2&message=Hello
 *
 * Multiple child entries can be added with indexed parameters; non-indexed parameters are shared by all entries.
 *
 * Example: example.com/?product_id[0]=123&product_id[1]=456&qty=1
 */
class GPNF_Populate_Child_Entry_By_URL {

	private $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'              => 0,
			'nested_form_field_id' => 0,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		if ( ! function_exists( 'gp_nested_forms' ) ) {
			return;
		}

		add_filter( 'gform_pre_render_' . $this->_args['form_id'], array( $this, 'load_form_script' ) );

		add_action( 'wp_ajax_gpnf_populate_child_entry_by_url', array( $this, 'ajax_add_child_entry' ) );
		add_action( 'wp_ajax_nopriv_gpnf_populate_child_entry_by_url', array( $this, 'ajax_add_child_entry' ) );

	}

	public function load_form_script( $form ) {

		if ( $this->get_populated_entry_sets( rgar( $_SERVER, 'QUERY_STRING' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function get_child_form() {
		$nested_form_field = GFAPI::get_field( $this->_args['form_id'], $this->_args['nested_form_field_id'] );
		return $nested_form_field ? GFAPI::get_form( $nested_form_field->gpnfForm ) : false;
	}

	public function get_populated_entry_sets( $query ) {

		$child_form = $this->get_child_form();
		if ( ! $child_form ) {
			return array();
		}

		parse_str( wp_unslash( ltrim( (string) $query, '?' ) ), $params );

		$shared  = array();
		$indexed = array();

		foreach ( $params as $key => $value ) {
			if ( is_scalar( $value ) ) {
				$shared[ $key ] = $value;
			} elseif ( is_array( $value ) ) {
				foreach ( $value as $index => $indexed_value ) {
					if ( is_scalar( $indexed_value ) ) {
						$indexed[ $index ][ $key ] = $indexed_value;
					}
				}
			}
		}

		ksort( $indexed );

		$param_sets = $indexed ? array_values( $indexed ) : array( array() );
		$entry_sets = array();

		foreach ( $param_sets as $param_set ) {
			$param_set  = array_map( 'sanitize_text_field', array_merge( $shared, $param_set ) );
			$entry_data = $this->get_entry_data( $child_form, $param_set );
			if ( array_filter( $entry_data, 'strlen' ) ) {
				$entry_sets[] = $entry_data;
			}
		}

		return $entry_sets;
	}

	public function get_entry_data( $child_form, $params ) {

		$entry_data = array();

		foreach ( $child_form['fields'] as $field ) {

			if ( ! $field->allowsPrepopulate ) {
				continue;
			}

			$inputs = is_array( $field->inputs ) ? $field->inputs : array(
				array(
					'id'   => $field->id,
					'name' => $field->inputName,
				),
			);
			foreach ( $inputs as $input ) {
				if ( rgar( $input, 'name' ) && isset( $params[ $input['name'] ] ) ) {
					$entry_data[ (string) $input['id'] ] = $params[ $input['name'] ];
				}
			}
		}

		return $entry_data;
	}

	public function output_script() {

		$args = array(
			'formId'  => $this->_args['form_id'],
			'fieldId' => $this->_args['nested_form_field_id'],
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'gpnf_populate_child_entry_by_url' ),
		);
		?>

		<script type="text/javascript">

			( function( args ) {

				gform.addAction( 'gpnf_session_initialized', function( gpnf ) {

					if ( gpnf.formId != args.formId || gpnf.fieldId != args.fieldId ) {
						return;
					}

					jQuery.post( args.ajaxUrl, {
						action: 'gpnf_populate_child_entry_by_url',
						nonce: args.nonce,
						form_id: args.formId,
						field_id: args.fieldId,
						query: window.location.search,
						gpnf_context: gpnf.ajaxContext,
					}, function( response ) {
						if ( response.success && response.data ) {
							response.data.forEach( function( entryArgs ) {
								GPNestedForms.loadEntry( entryArgs );
							} );
						}
					} );

				} );

			} )( <?php echo json_encode( $args ); ?> );

		</script>

		<?php
	}

	public function ajax_add_child_entry() {

		if ( (int) rgpost( 'form_id' ) !== (int) $this->_args['form_id'] || (int) rgpost( 'field_id' ) !== (int) $this->_args['nested_form_field_id'] ) {
			return;
		}

		check_ajax_referer( 'gpnf_populate_child_entry_by_url', 'nonce' );

		$entry_sets = $this->get_populated_entry_sets( rgpost( 'query' ) );
		if ( ! $entry_sets ) {
			wp_send_json_error( 'No matching query parameters.' );
		}

		$child_form = $this->get_child_form();

		$session      = new GPNF_Session( $this->_args['form_id'] );
		$session_hash = $session->get( 'hash' );
		if ( ! $session_hash ) {
			wp_send_json_error( 'No session.' );
		}

		$loaded_entries = array();

		foreach ( $entry_sets as $entry_data ) {

			// Skip if an entry has already been created from these values for this session.
			$population_hash = wp_hash( json_encode( array( $session_hash, $this->_args['nested_form_field_id'], $entry_data ) ) );
			$existing        = GFAPI::count_entries( $child_form['id'], array(
				'field_filters' => array(
					array(
						'key'   => 'gpnf_url_population_hash',
						'value' => $population_hash,
					),
				),
			) );
			if ( $existing ) {
				continue;
			}

			$entry_data['form_id'] = $child_form['id'];

			$child_entry_id = GFAPI::add_entry( $entry_data );
			if ( is_wp_error( $child_entry_id ) ) {
				continue;
			}

			gform_update_meta( $child_entry_id, 'gpnf_url_population_hash', $population_hash );

			$entry = GFAPI::get_entry( $child_entry_id );

			$child_entry = new GPNF_Entry( $entry );
			$child_entry->set_parent_meta( $this->_args['form_id'] );
			$child_entry->set_nested_form_field( $this->_args['nested_form_field_id'] );
			$child_entry->set_expiration();

			$session->add_child_entry( $child_entry->id );

			$loaded_entries[] = array(
				'formId'      => $this->_args['form_id'],
				'fieldId'     => $this->_args['nested_form_field_id'],
				'entryId'     => $child_entry->id,
				'entry'       => $child_entry,
				'fieldValues' => gp_nested_forms()->get_entry_display_values( $entry, $child_form ),
				'mode'        => 'add',
			);
		}

		wp_send_json_success( $loaded_entries );

	}

}

# Configuration

new GPNF_Populate_Child_Entry_By_URL( array(
	'form_id'              => 123, // The parent form ID.
	'nested_form_field_id' => 4,   // The Nested Form field ID on the parent form.
) );
