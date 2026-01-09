<?php
/**
 * Gravity Perks // Populate Anything // Preserve Selected Multi-Select Choices Across Filter Changes
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * When GPPA repopulates a Multi-Select field, previously selected choices that are no longer
 * in the new result set get removed. This snippet stores selections in a hidden field and
 * ensures they remain available and selected even after GPPA refreshes the field.
 *
 * Instructions:
 * 1. Add a Hidden field to your form to store selections.
 * 2. Update the form ID, multi-select field ID, and hidden field ID via the configuration
 */
class GPPA_Preserve_Multiselect_Choices {

	private $form_id;
	private $field_id;
	private $store_id;

	public function __construct( $args ) {
		$this->form_id  = $args['form_id'];
		$this->field_id = $args['field_id'];
		$this->store_id = $args['store_id'];

		add_filter( "gform_pre_render_{$this->form_id}", array( $this, 'pre_render' ), 20 );
		add_filter( "gform_pre_validation_{$this->form_id}", array( $this, 'restore_choices' ), 20 );
	}

	public function pre_render( $form ) {
		$this->enqueue_script();
		return $this->restore_choices( $form );
	}

	public function restore_choices( $form ) {
		$stored = json_decode( wp_unslash( rgpost( 'input_' . $this->store_id ) ), true );
		if ( empty( $stored ) || ! is_array( $stored ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( (int) $field->id !== $this->field_id ) {
				continue;
			}

			$existing = array_column( $field->choices ?: array(), 'value' );
			foreach ( $stored as $value => $label ) {
				if ( ! in_array( (string) $value, $existing, true ) ) {
					$field->choices[] = array(
						'text'  => $label,
						'value' => $value,
					);
				}
			}
			break;
		}

		return $form;
	}

	public function enqueue_script() {
		if ( ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}
	}

	public function output_script() {
		?>
		<script type="text/javascript">
		( function( $ ) {
			window.GPPAPreserveMultiselectChoices = function( args ) {
				var formId     = args.formId,
					fieldId    = args.fieldId,
					storeId    = args.storeId,
					isUpdating = false;

				function getTS() {
					var el = document.getElementById( 'input_' + formId + '_' + fieldId );
					return el?.tomselect;
				}

				function getStore() {
					var $store = $( '#input_' + formId + '_' + storeId );
					try {
						return JSON.parse( $store.val() ) || {};
					} catch ( e ) {
						return {};
					}
				}

				function setStore( map ) {
					$( '#input_' + formId + '_' + storeId ).val( JSON.stringify( map ) );
				}

				function refresh() {
					var ts = getTS();
					if ( ! ts ) {
						return;
					}

					var $select = $( '#input_' + formId + '_' + fieldId );

					// Rebind events (element is replaced on GPPA refresh).
					if ( ! $select.data( 'gppa-preserve-bound' ) ) {
						$select.data( 'gppa-preserve-bound', true );
						ts.on( 'item_add', function( value ) {
							if ( isUpdating ) return;
							var map = getStore();
							map[ value ] = ts.options[ value ]?.text || value;
							setStore( map );
						} );
						ts.on( 'item_remove', function( value ) {
							if ( isUpdating ) return;
							var map = getStore();
							delete map[ value ];
							setStore( map );
						} );
					}

					isUpdating = true;
					var map = getStore();

					// Add stored options and select them.
					Object.keys( map ).forEach( function( val ) {
						if ( ! ts.options[ val ] ) {
							ts.addOption( { id: val, text: map[ val ] } );
						}
					} );
					ts.addItems( Object.keys( map ), true );

					setTimeout( function() { isUpdating = false; }, 0 );
				}

				$( document ).on( 'gppa_updated_batch_fields', function( e, updatedFormId, updatedFieldIds ) {
					if ( Number( updatedFormId ) !== formId ) return;
					if ( updatedFieldIds?.length && ! updatedFieldIds.some( function( id ) { return Number( id ) === fieldId; } ) ) return;
					setTimeout( refresh, 0 );
				} );

				$( document ).on( 'gform_post_render', function( e, id ) {
					if ( Number( id ) === formId ) refresh();
				} );

				refresh();
			};
		} )( jQuery );
		</script>
		<?php
		$script = 'new GPPAPreserveMultiselectChoices( ' . wp_json_encode( array(
			'formId'  => $this->form_id,
			'fieldId' => $this->field_id,
			'storeId' => $this->store_id,
		) ) . ' );';
		GFFormDisplay::add_init_script( $this->form_id, 'gppa_preserve_multiselect_' . $this->field_id, GFFormDisplay::ON_PAGE_RENDER, $script );
	}

}

# Configuration

new GPPA_Preserve_Multiselect_Choices( array(
	'form_id'  => 123, // Replace with your form ID.
	'field_id' => 3,   // Replace with your Multi-Select field ID.
	'store_id' => 5,   // Replace with your Hidden field ID.
) );
