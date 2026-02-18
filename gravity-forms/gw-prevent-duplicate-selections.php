<?php
/**
 * Gravity Wiz // Gravity Forms // Prevent Duplicate Selections
 * https://gravitywiz.com/
 *
 * Prevent duplicate selections in choice-based fields. Currently works with Checkbox, Radio Button, Drop Down and
 * Enhanced-UI-enabled Multi Select fields.
 * Also supports multiple independent duplicate-prevention groups.
 *
 * Instructions:
 *
 * 1. Install this snippet.
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Add one of the following CSS classes to your fields:
 *
 *    Global group (all fields interact together):
 *    gw-prevent-duplicates
 *
 *    OR grouped mode (fields only interact within same group):
 *    gw-prevent-duplicates-1
 *    gw-prevent-duplicates-2
 *    gw-prevent-duplicates-teamA
 *    gw-prevent-duplicates-anything
 *
 *    Any CSS class beginning with:
 *    gw-prevent-duplicates
 *    will be treated as its own independent duplicate-prevention group.
 *
 * Example:
 *    gw-prevent-duplicates-1 -> fields only prevent duplicates within group 1
 *    gw-prevent-duplicates-2 -> fields only prevent duplicates within group 2
 *
 * Plugin Name:  Gravity Forms Prevent Duplicate Selections
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Prevent duplicate selections in choice-based fields. Currently works with Checkbox, Radio Button, Drop Down and Enhanced-UI-enabled Multi Select fields.
 * Author:       Gravity Wiz
 * Version:      0.2
 * Author URI:   http://gravitywiz.com
 */

class GW_Prevent_Duplicate_Selections {
	public function __construct() {
		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );
	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function add_init_script( $form ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$script = 'new ' . __CLASS__ . '();';
		$slug   = implode( '_', array( strtolower( __CLASS__ ) ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	public function output_script() {
		?>

		<script type="text/javascript">
		window.<?php echo __CLASS__; ?> = function() {

			var $ = jQuery;
			const gpadvsPreviousValues = {};

			window.gform.addFilter('gplc_excluded_input_selectors', function(selectors) {
				selectors.push('.gw-disable-duplicates-disabled');
				return selectors;
			});

			function getDuplicateGroups() {

				let groups = [];

				$('[class*="gw-prevent-duplicates"]').each(function() {

					let classes = this.className.split(/\s+/);

					classes.forEach(function(cls) {
						if (cls.indexOf('gw-prevent-duplicates') === 0 && groups.indexOf(cls) === -1) {
							groups.push(cls);
						}
					});
				});

				return groups;
			}

			function initGroups() {

				getDuplicateGroups().forEach(function(groupClass) {

					let $groupWrapper = $('.' + groupClass);

					$groupWrapper.on('change', 'input, select', function(event, selected) {

						gwDisableDuplicates(
							$(this),
							$groupWrapper.find('input, select'),
							selected
						);
					});

					let $inputs = $groupWrapper.find('input, select');

					$inputs.each(function() {
						gwDisableDuplicates(
							$(this),
							$inputs.not('.gw-disable-duplicates-disabled')
						);
					});
				});
			}

			function getChangedOptionElFromSelect($select, selected) {

				if (selected) {
					let value = selected.selected ? selected.selected : selected.deselected;
					return findOptionByValue($select, value);
				}

				if ($select.siblings('.ts-wrapper').length) {

					const val = $select.val();

					if (typeof val === 'string') {
						return findOptionByValue($select, val);
					}

					const selectId = $select.attr('id');
					const prevVal = gpadvsPreviousValues[selectId] || null;
					gpadvsPreviousValues[selectId] = val;

					let changedOptVal;

					if (!prevVal) {
						changedOptVal = val[0];
					} else if (prevVal.length > val.length) {
						changedOptVal = getArrayDiff(prevVal, val);
					} else {
						changedOptVal = getArrayDiff(val, prevVal);
					}

					return findOptionByValue($select, changedOptVal);
				}

				return $select.find('option:selected');
			}

			function getArrayDiff(arr1, arr2) {
				return arr1.filter(x => !arr2.includes(x))[0];
			}

			function findOptionByValue($select, value) {
				return $select.find('[value="' + value + '"]');
			}

			function gwDisableDuplicates($elem, $group, selected) {

				let $parent = $elem;

				if ($elem.is('select')) {
					$elem = getChangedOptionElFromSelect($elem, selected);
					$group = $group.find('option');
				}

				let value     = $elem.val();
				let $targets  = $group.not($elem).not('.gplc-disabled').not('.gpi-disabled').not('.gf_placeholder');
				let isChecked = $elem.is(':checked');

				let disabledClass = 'gf-default-disabled gw-disable-duplicates-disabled';
				let previousValue;

				if ($elem.is(':radio, option') && !$parent.prop('multiple')) {

					previousValue = $elem.parents('.gfield').data('previous-value');
					$elem.parents('.gfield').data('previous-value', $elem.val());

					if (previousValue) {
						$targets
							.filter('[value="{0}"]'.gformFormat(previousValue))
							.prop('disabled', false)
							.removeClass(disabledClass);
					}
				}

				let $filteredTargets = $targets
					.filter('[value="{0}"]'.gformFormat(value))
					.prop('disabled', isChecked);

				if ($elem.is('option')) {

					$filteredTargets.parents('select').each(function() {

						let $options = $(this).find('option');

						if ($options.filter(':selected:disabled').length) {
							$options.not(':disabled').first().prop('selected', true);
						}

						$(this).trigger('chosen:updated');
					});
				}

				if (isChecked) {
					$filteredTargets.addClass(disabledClass);
				} else {
					$filteredTargets.removeClass(disabledClass);
				}
			}

			initGroups();
		};
		</script>

		<?php
	}

	public function is_applicable_form( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( isset( $field->cssClass ) && str_contains( $field->cssClass, 'gw-prevent-duplicates' ) ) {
				return true;
			}
		}

		return false;
	}

}

# Configuration
new GW_Prevent_Duplicate_Selections();
