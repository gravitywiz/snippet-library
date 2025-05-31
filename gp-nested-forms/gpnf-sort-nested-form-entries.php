<?php
/**
 * Gravity Perks // Nested Forms // Sort Nested Form Entries
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/c9fd421f33dd4925ae2c0139abace5f0
 *
 * This snippet allows you to sort the entries of a Nested Form field based on a specific field in the child form.
 *
 * Plugin Name:  GP Nested Forms - Sort Nested Form Entries
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Enable sorting for Nested Form entries.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
class GPNF_Sort_Nested_Entries {

	private $_args = array();

	public function __construct( $args = array() ) {
		$this->_args = wp_parse_args( $args, array(
			'parent_form_id'   => false,
			'nested_field_id'  => false,
			'sort_field_id'    => false,
			'sort_order'       => 'asc',
		) );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( "gpnf_template_args_{$this->_args['parent_form_id']}_{$this->_args['nested_field_id']}", array( $this, 'sort_entries_php' ) );
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
	}

	public function sort_entries_php( $args ) {
		$field_id = $this->_args['sort_field_id'];
		$order    = strtolower( $this->_args['sort_order'] );

		if ( isset( $args['entries'] ) ) {
			usort( $args['entries'], function( $a, $b ) use ( $field_id, $order ) {
				$first  = rgar( $a, $field_id );
				$second = rgar( $b, $field_id );

				if ( $first == $second ) {
					return 0;
				}

				if ( $order === 'asc' ) {
					return ( $first < $second ) ? -1 : 1;
				} else {
					return ( $first > $second ) ? -1 : 1;
				}
			} );
		}

		return $args;
	}

	public function load_form_script( $form, $is_ajax_enabled ) {
		if ( $form['id'] == $this->_args['parent_form_id'] && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function output_script() {
		$args = array(
			'parentFormId'  => (int) $this->_args['parent_form_id'],
			'nestedFieldId' => (int) $this->_args['nested_field_id'],
			'sortFieldId'   => (int) $this->_args['sort_field_id'],
			'sortOrder'     => strtolower( $this->_args['sort_order'] ),
		);
		?>

		<script type="text/javascript">
			(function($) {
				const sortFieldId = "<?php echo esc_js( $args['sortFieldId'] ); ?>";
				const sortOrder   = "<?php echo esc_js( $args['sortOrder'] ); ?>";

				window.gform.addFilter('gpnf_sorted_entries', function(entries, formId, fieldId, gpnf) {
					if (!entries || !entries.length || !entries[0][sortFieldId]) {
						console.warn(`GPNF Sort: Field ID ${sortFieldId} not found in entries or entries are empty.`);
						return entries;
					}

					return entries.sort((a, b) => {
						let valA = a[sortFieldId]?.label || '';
						let valB = b[sortFieldId]?.label || '';

						if (sortOrder === 'desc') {
							return valB.localeCompare(valA);
						}

						return valA.localeCompare(valB);
					});
				});
			})(jQuery);
		</script>

		<?php
	}
}

# Example usage:
new GPNF_Sort_Nested_Entries( array(
	'parent_form_id'  => 184,
	'nested_field_id' => 1,
	'sort_field_id'   => 2,     // field on child form to sort by
	'sort_order'      => 'asc', // or 'desc'
) );
