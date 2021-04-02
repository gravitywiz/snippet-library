<?php
/**
 * Gravity Perks // Limit Choices // Field Groups
 * http://gravitywiz.com/documentation/gp-limit-choices/
 *
 * Specify a group of fields to create a unique choice to be limited.
 *
 * Plugin Name: GP Limit Choices - Field Groups
 * Plugin URI:  http://gravitywiz.com/documentation/gp-limit-choices/
 * Description: Specify a group of fields that should create a unique choice to be limited.
 * Author:      Gravity Wiz
 * Version:     1.5
 * Author URI:  http://gravitywiz.com
 */
class GP_Limit_Choices_Field_Group {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_ids' => array(),
		) );

		$this->_args['hash'] = hash_hmac( 'sha256', serialize( $this->_args ), 'gplc_field_group' );

		add_filter( 'gwlc_choice_counts_query', array( $this, 'limit_by_field_group' ), 10, 2 );

		add_action( 'wp_ajax_gplcfg_refresh_field', array( $this, 'ajax_refresh' ) );
		add_action( 'wp_ajax_nopriv_gplcfg_refresh_field', array( $this, 'ajax_refresh' ) );

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

		if( isset( $_POST['action'] ) && $_POST['action'] == 'gplcfg_refresh_field' ) {
			remove_action( 'wp', array( 'GFForms', 'maybe_process_form' ), 9 );
			remove_action( 'admin_init', array( 'GFForms', 'maybe_process_form' ), 9 );
		}

	}

	public function limit_by_field_group( $query, $field ) {
		global $wpdb;

		$field_ids = $this->_args['field_ids'];

		if( ! $this->is_applicable_form( $field->formId ) || ! in_array( $field->id, $field_ids ) ) {
			return $query;
		}

		unset( $field_ids[ array_search( $field->id, $field_ids ) ] );
		$field_ids = array_values( $field_ids );

		$form   = GFAPI::get_form( $field->formId );
		$join   = $where = array();
		$select = $from = '';
		$_alias = null;

		foreach( $field_ids as $index => $field_id ) {

			$field  = GFFormsModel::get_field( $form, $field_id );
			$alias  = sprintf( 'fgem%d', $index + 1 );

			if( $index == 0 ) {
				$_alias  = $alias;
				$select  = "SELECT DISTINCT {$alias}.entry_id";
				$from    = "FROM {$wpdb->prefix}gf_entry_meta {$alias}";
			} else {
				$join[]  = "INNER JOIN {$wpdb->prefix}gf_entry_meta {$alias} ON {$_alias}.entry_id = {$alias}.entry_id";
			}

			$value   = $field->get_value_save_entry( GFFormsModel::get_field_value( $field ), $form, null, null, null );
			$where[] = $wpdb->prepare( "( {$alias}.form_id = %d AND {$alias}.meta_key = %s AND {$alias}.meta_value = %s )", $field->formId, $field_id, $value );

		}

		$field_group_query = array(
			'select' => $select,
			'from'   => $from,
			'join'   => implode( ' ', $join ),
			'where'  => sprintf( 'WHERE %s', implode( "\nAND ", $where ) )
		);

		$query['where'] .= sprintf( ' AND e.id IN( %s )', implode( "\n", $field_group_query ) );

		return $query;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		$func = array( 'GP_Limit_Choices_Field_Group', 'output_script' );
		if( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', $func ) ) {
			add_action( 'wp_footer', $func );
			add_action( 'gform_preview_footer', $func );
		}

		return $form;
	}

	public function add_init_script( $form ) {

		if( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$target_field_id = $this->get_target_field_id( $form, $this->_args['field_ids'] );

		$args = array(
			'formId'          => $this->_args['form_id'],
			'targetFieldId'   => $target_field_id,
			'triggerFieldIds' => $this->get_trigger_field_ids( $form, $this->_args['field_ids'] ),
			'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
			'hash'            => $this->_args['hash'],
		);

		$script = 'new GPLCFieldGroup( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gplc_field_group', $this->_args['form_id'], $target_field_id ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function get_target_field_id( $form, $field_ids ) {
		foreach( $field_ids as $field_id ) {
			$field = GFAPI::get_field( $form, $field_id );
			if ( gp_limit_choices()->is_applicable_field( $field ) ) {
				return $field_id;
			}
		}
		return false;
	}

	public function get_trigger_field_ids( $form, $field_ids ) {
		$target_field_id = $this->get_target_field_id( $form, $field_ids );
		return array_values( array_filter( $field_ids, function( $field_id ) use ( $target_field_id ) {
			return $field_id != $target_field_id;
		} ) );
	}

	public function ajax_refresh() {

		// Object can be instantiated multiple times. Only listen for this specific configuration's hash.
		if ( rgpost( 'hash' ) !== $this->_args['hash'] ) {
			/**
			 * Return out if the hash doesn't match. If we exit here, other ajax_refresh() calls in other instances
			 * won't have the chance to run.
			 */
			return;
		}

		$entry = GFFormsModel::get_current_lead();
		if( ! $entry ) {
			wp_send_json_error();
		}

		$form = gf_apply_filters( array( 'gform_pre_render', $entry['form_id'] ), GFAPI::get_form( $entry['form_id'] ), false, array() );
		$field = GFFormsModel::get_field( $form, $this->get_target_field_id( $form, $this->_args['field_ids'] ) );

		if( $field->get_input_type() == 'html' ) {
			$content = GWPreviewConfirmation::preview_replace_variables( $field->content, $form );
		} else {
			$value = rgpost( 'input_' . $field->id );
			$content = $field->get_field_content( $value, true, $form );
			$content = str_replace( '{FIELD}', $field->get_field_input( $form, $value, $entry ), $content );
		}

		wp_send_json_success( $content );
	}

	public static function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				window.GPLCFieldGroup = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( prop in args ) {
						if( args.hasOwnProperty( prop ) )
							self[prop] = args[prop];
					}

					self.init = function() {

						self.$form        = $( '#gform_wrapper_{0}'.format( self.formId ) );
						self.$targetField = $( '#field_{0}_{1}'.format( self.formId, self.targetFieldId ) );

						gform.addAction( 'gform_input_change', function( elem, formId, fieldId ) {
							if ( $.inArray( parseInt( fieldId ), self.triggerFieldIds ) !== -1 ) {
								self.refresh();
							}
						} );

						self.refresh();

					};

					self.refresh = function() {

						if( ! self.$targetField.is( ':visible' ) ) {
							return;
						}

						var data = {
							action: 'gplcfg_refresh_field',
							hash:   self.hash
						};

						self.$form.find( 'input, select, textarea' ).each( function() {
							if ( this.type === 'radio' ) {
								if ( this.checked ) {
									data[ $( this ).attr( 'name' ) ] = $( this ).val();
								}
							} else {
								data[ $( this ).attr( 'name' ) ] = $( this ).val();
							}
						} );

						// Prevent AJAX-enabled forms from intercepting our AJAX request.
						delete data['gform_ajax'];

						$.post( self.ajaxUrl, data, function( response ) {
							if( response.success ) {
								self.$targetField.html( response.data );
							}
						} );

					};

					self.init();

				}

			} )( jQuery );

		</script>

		<?php
	}

}

# Configuration

new GP_Limit_Choices_Field_Group( array(
    'form_id'   => 123,
    'field_ids' => array( 3, 4 )
) );
