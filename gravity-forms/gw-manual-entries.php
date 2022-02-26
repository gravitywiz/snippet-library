<?php
/**
 * Gravity Wiz // Gravity Forms // Manual Entries
 *
 * Create entries manually for Gravity Forms. Adds an "Add New" button next to the page title on all entry-related pages.
 * Also integrates with Nested Forms and adds support for adding new child entries to a Nested Form field from the entry
 * detail view.
 *
 * Plugin Name: Gravity Forms Manual Entries
 * Plugin URI: http://gravitywiz.com
 * Description: Create entries manually for Gravity Forms. Adds an "Add New" button next to the page title on all entry-related pages.
 * Author: Gravity Wiz
 * Version: 1.5
 * Author URI: http://gravitywiz.com
 */
class GW_Manual_Entries {

	public function __construct( $args = array() ) {

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		add_filter( 'admin_print_scripts-forms_page_gf_entries', array( $this, 'output_entry_button_script' ) );

		add_filter( 'gpnf_template_args', array( $this, 'gpnf_add_entry_action' ), 10, 2 );
		add_filter( 'gform_entry_field_value', array( $this, 'gpnf_no_entries_add_entry_link' ), 10, 4 );
		add_filter( 'gform_post_add_entry', array( $this, 'gpnf_add_meta' ), 10, 2 );
		add_filter( 'gfme_edit_url', array( $this, 'gpnf_add_edit_url_query_args' ), 10, 2 );
		add_filter( 'gfme_redirect_from_edit_mode_url', array( $this, 'gpnf_redirect_to_parent_entry' ), 9, 2 );

		$this->process_query_string();

	}

	public function output_entry_button_script() {

		$button_url   = $this->get_add_entry_url();
		$button_label = __( 'Add New Entry' );
		$button       = sprintf( '<a href="%s" class="page-title-action">%s</a>', $button_url, $button_label );

		?>

		<script type="text/javascript">

			var gwmeInterval = setInterval( function() {

				var isLegacyUI = document.querySelectorAll( 'body' )[0].classList.contains( 'gf-legacy-ui' );

				if ( isLegacyUI ) {
					// GF 2.4
					var header = document.querySelectorAll( 'h1, h2' )[0];
					if( header ) {
						clearInterval( gwmeInterval );
						header.innerHTML = header.innerHTML + '<?php echo $button; ?>';
					}
				} else {
					// GF 2.5+
					var tableNavTop = document.querySelectorAll( '.tablenav.top' )[0];
					if( tableNavTop ) {
						clearInterval( gwmeInterval );
						var a        = document.createElement( 'a' );
						var linkText = document.createTextNode( '<?php echo $button_label; ?>' );
						a.appendChild( linkText );
						a.title     = '<?php echo $button_label; ?>';
						a.href      = '<?php echo $button_url; ?>';
						var marginLeft = document.querySelectorAll( '.tablenav-pages.no-pages' ).length > 0 ? 'auto' : '0.375rem;'
						a.style     = 'margin-left:{0};'.format( marginLeft );
						a.className = 'button';
						tableNavTop.appendChild( a );
					}
				}

			}, 100 );

		</script>

		<?php
	}

	public function get_add_entry_url( $args = array(), $url = false ) {

		$args = wp_parse_args( $args, array(
			'add_new' => 1,
		) );

		if ( $url !== false ) {
			$return = add_query_arg( $args, $url );
		} else {
			$return = add_query_arg( $args );
		}

		return $return;
	}

	public function is_add_entry_request() {

		$is_entry_view = rgget( 'page' ) == 'gf_entries';

		return $is_entry_view && rgget( 'add_new' ) && rgget( 'id' );
	}

	public function process_query_string() {

		if ( $this->is_add_entry_request() ) {

			$form_id = rgget( 'id' ); // @todo add support for id-less entry list page

			/**
			 * Filter the data that will be used to create a new manual entry.
			 *
			 * @since 1.5
			 *
			 * @param array $data Any array of data that will be used to create a new manual entry.
			 */
			$entry    = gf_apply_filters( array( 'gwme_entry_data', $form_id ), array( 'form_id' => $form_id ) );
			$entry_id = GFAPI::add_entry( $entry );

			/*
			 * GF will not fetch an entry that does not have any data in the lead detail table.
			 * Let's add a placeholder value to avoid this error.
			 */
			global $wpdb;
			if ( is_callable( array( 'GravityPerks', 'get_gravityforms_db_version' ) ) && version_compare( GravityPerks::get_gravityforms_db_version(), '2.3-beta-1', '<' ) ) {
				$wpdb->insert( $wpdb->prefix . 'rg_lead_detail', array(
					'lead_id'      => $entry_id,
					'form_id'      => $form_id,
					'field_number' => 1,
					'value'        => '',
				) );
			}

			$entry_url = sprintf( '%s/wp-admin/admin.php?page=gf_entries&view=entry&id=%d&lid=%d&pos=0', get_bloginfo( 'wpurl' ), $form_id, $entry_id );
			wp_redirect( apply_filters( 'gfme_edit_url', add_query_arg( array( 'edit' => 1 ), $entry_url ) ) );

			exit;
		}

		$is_entry_view   = rgget( 'page' ) == 'gf_entries';
		$is_entry_detail = $is_entry_view && rgget( 'lid' );

		if ( $is_entry_detail ) {

			$is_edit_mode = rgget( 'edit' ) && rgget( 'lid' );

			if ( $is_edit_mode && ! rgpost( 'action' ) ) {

				$_POST['screen_mode'] = 'edit';

			} elseif ( $is_edit_mode && rgpost( 'action' ) == 'update' ) {

				ob_start();

				add_action( 'gform_after_update_entry', array( $this, 'redirect_from_edit_mode' ) );

			}
		}

	}

	public function redirect_from_edit_mode() {
		wp_redirect( apply_filters( 'gfme_redirect_from_edit_mode_url', remove_query_arg( 'edit' ) ) );
		ob_end_flush();
		exit;
	}

	/**
	 * Add "Add Entry" button to Nested Form field's Entry Detail value.
	 *
	 * @param $args
	 * @param $field
	 *
	 * @return mixed
	 */
	public function gpnf_add_entry_action( $args, $field ) {

		if ( $args['template'] == 'nested-entries-detail' ) {
			$args['actions']['gwme_add_entry'] = sprintf(
				'<a class="gpnf-add-new-entry" href="%s">%s</a>',
				$this->get_add_entry_url( array(
					'id'                => $field->gpnfForm,
					'gpnf-field-id'     => $field->id,
					'gpnf-parent-entry' => rgget( 'lid' ),
				) ),
				sprintf( __( 'Add %s', 'gp-nested-forms' ), $field->get_item_label() )
			);
		}

		return $args;
	}

	public function gpnf_no_entries_add_entry_link( $value, $field, $lead, $form ) {

		if ( $field->get_input_type() == 'form' && empty( $value ) ) {
			$value = sprintf(
				'<a class="gpnf-add-new-entry" href="%s">%s</a>',
				$this->get_add_entry_url( array(
					'id'                => $field->gpnfForm,
					'gpnf-field-id'     => $field->id,
					'gpnf-parent-entry' => rgget( 'lid' ),
				) ),
				sprintf( __( 'Add %s', 'gp-nested-forms' ), $field->get_item_label() )
			);
		}

		return $value;
	}

	public function gpnf_add_meta( $entry, $form ) {

		$nested_form_field_id = rgget( 'gpnf-field-id' );
		$parent_entry_id      = rgget( 'gpnf-parent-entry' );

		if ( $this->is_add_entry_request() && $nested_form_field_id && $parent_entry_id ) {

			$entry = new GPNF_Entry( $entry );
			$entry->set_parent_form( $form['id'], $parent_entry_id );
			$entry->set_nested_form_field( $nested_form_field_id );

			$parent_entry = GFAPI::get_entry( $parent_entry_id );
			if ( is_wp_error( $parent_entry ) ) {
				return;
			}

			$child_entry_ids                       = explode( ',', $parent_entry[ $nested_form_field_id ] );
			$child_entry_ids[]                     = $entry->id;
			$parent_entry[ $nested_form_field_id ] = $child_entry_ids;

			GFAPI::update_entry_field( $parent_entry_id, $nested_form_field_id, implode( ',', $child_entry_ids ) );

		}

	}

	public function gpnf_add_edit_url_query_args( $url ) {

		$parent_entry_id = rgget( 'gpnf-parent-entry' );
		if ( $parent_entry_id ) {
			$url = add_query_arg( 'gpnf-parent-entry', $parent_entry_id, $url );
		}

		return $url;
	}

	public function gpnf_redirect_to_parent_entry( $url ) {

		$parent_entry_id = rgget( 'gpnf-parent-entry' );
		if ( $parent_entry_id ) {
			$entry = GFAPI::get_entry( $parent_entry_id );
			$url   = $this->get_entry_detail_url( $entry );
		}

		return $url;
	}

	public function get_entry_detail_url( $entry ) {

		require_once( GFCommon::get_base_path() . '/entry_list.php' );

		$gf_entry_list = new GF_Entry_List_Table( array(
			'form_id' => $entry['form_id'],
			'form'    => GFAPI::get_form( $entry['form_id'] ),
		) );

		return $gf_entry_list->get_detail_url( $entry );
	}

}

# Configuration

new GW_Manual_Entries();
