
/**
 * Gravity Perks // GP Populate Anything // Add Support for Aggregate Functions
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Perform  calculations on the values in a field/column and return a single value.
 * To use the snippet you'd use a custom template type and enter the merge tag manually adding the specific property
 * (e.g. column, field) you want to target, like this: {sum:your_property}
 *
 * Merge Tags
 * {sum:ID} - The SUM merge tag adds up all values in a specific column/field.
 * {avg:ID} - The AVG merge tag adds up all values and then calculates the average.
 * {min:ID} - The MIN merge tag finds the minimum value in a specific column/field.
 * {max:ID} - The MAX merge tag finds the maximum value in a specific column/field.
 *
 * Plugin Name:  GP Populate Anything – Aggregate Functions
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Perform calculations on the values in a field/column and return a single value.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
class GPPA_Aggregate_Functions {

	public static $default_count_regex = '/{count}/';
	public static $sum_regex = '/{sum:(.+)}/';
	public static $avg_regex = '/{avg:(.+)}/';
	public static $min_regex = '/{min:(.+)}/';
	public static $max_regex = '/{max:(.+)}/';

	private static $instance;

	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// Required by GPPA 2.0+ to ensure that all results are returned.
		add_filter( 'gppa_query_all_value_objects', array( $this, 'enable_query_all_value_objects' ), 10, 7 );

		add_filter( 'gppa_process_template', array( $this, 'replace_template_sum_merge_tags' ), 2, 7 );
		add_filter( 'gppa_process_template', array( $this, 'replace_template_avg_merge_tags' ), 2, 7 );
		add_filter( 'gppa_process_template', array( $this, 'replace_template_min_merge_tags' ), 2, 7 );
		add_filter( 'gppa_process_template', array( $this, 'replace_template_max_merge_tags' ), 2, 7 );

	}

	public function enable_query_all_value_objects( $query_all_value_objects, $field, $field_values, $object_type_instance, $filter_groups, $primary_property, $templates ) {
		return $this->matches_any_merge_tag( rgar( $templates, 'value' ) );
	}

	// The count function normally has "query all value objects" enabled, but this function overrides that.
	// We need to specify that count has to have "query all value objects" enabled to avoid breaking it.
	public function matches_any_merge_tag( $template_value ) {
		return preg_match( self::$default_count_regex, $template_value ) ||
			preg_match( self::$sum_regex, $template_value ) ||
			preg_match( self::$avg_regex, $template_value ) ||
			preg_match( self::$min_regex, $template_value ) ||
			preg_match( self::$max_regex, $template_value );
	}

	public function replace_template_sum_merge_tags( $template_value, $field, $template, $populate, $object, $object_type, $objects ) {
		preg_match_all( self::$sum_regex, $template_value, $matches, PREG_SET_ORDER );
		if ( $matches ) {
			foreach ( $matches as $match ) {
				$full_match = $match[0];
				$merge_tag  = str_replace( $object_type->id . ':', '', $match[1] );
				$sum        = 0;
				foreach ( $objects as $object ) {
					$value = $this->get_object_value( $merge_tag, $object, $object_type );
					if ( is_numeric( $value ) ) {
						$sum += floatval( $value );
					}
				}
				$template_value = str_replace( $full_match, $sum, $template_value );
			}
		}
		return $template_value;
	}

	public function replace_template_avg_merge_tags( $template_value, $field, $template, $populate, $object, $object_type, $objects ) {
		preg_match_all( self::$avg_regex, $template_value, $matches, PREG_SET_ORDER );
		if ( $matches ) {
			foreach ( $matches as $match ) {
				$full_match = $match[0];
				$merge_tag  = str_replace( $object_type->id . ':', '', $match[1] );
				$sum        = 0;
				$count      = count( $objects );
				foreach ( $objects as $object ) {
					$value = $this->get_object_value( $merge_tag, $object, $object_type );
					if ( is_numeric( $value ) ) {
						$sum += floatval( $value );
					}
				}
				$avg            = ( $count > 0 ) ? ( $sum / $count ) : 0;
				$template_value = str_replace( $full_match, $avg, $template_value );
			}
		}
		return $template_value;
	}

	public function replace_template_min_merge_tags( $template_value, $field, $template, $populate, $object, $object_type, $objects ) {
		preg_match_all( self::$min_regex, $template_value, $matches, PREG_SET_ORDER );
		if ( $matches ) {
			foreach ( $matches as $match ) {
				$full_match = $match[0];
				$merge_tag  = str_replace( $object_type->id . ':', '', $match[1] );
				$min        = null;
				foreach ( $objects as $object ) {
					$value = $this->get_object_value( $merge_tag, $object, $object_type );
					if ( is_numeric( $value ) ) {
						$value = floatval( $value );
						if ( is_null( $min ) || ( $value < $min ) ) {
							$min = $value;
						}
					}
				}
				if ( is_null( $min ) ) {
					$min = ' - ';
				}
				$template_value = str_replace( $full_match, $min, $template_value );
			}
		}
		return $template_value;
	}

	public function replace_template_max_merge_tags( $template_value, $field, $template, $populate, $object, $object_type, $objects ) {
		preg_match_all( self::$max_regex, $template_value, $matches, PREG_SET_ORDER );
		if ( $matches ) {
			foreach ( $matches as $match ) {
				$full_match = $match[0];
				$merge_tag  = str_replace( $object_type->id . ':', '', $match[1] );
				$max        = null;
				foreach ( $objects as $object ) {
					$value = $this->get_object_value( $merge_tag, $object, $object_type );
					if ( is_numeric( $value ) ) {
						$value = floatval( $value );
						if ( is_null( $max ) || ( $value > $max ) ) {
							$max = $value;
						}
					}
				}
				if ( is_null( $max ) ) {
					$max = ' - ';
				}
				$template_value = str_replace( $full_match, $max, $template_value );
			}
		}
		return $template_value;
	}

	public function get_object_value( $merge_tag, $object, $object_type ) {
		$value = $object_type->get_object_prop_value( $object, $merge_tag );
		// Convert currency values to numbers for Gravity Forms product fields.
		if ( $object_type->id === 'gf_entry' && strpos( $merge_tag, 'gf_field_' ) !== false ) {
			$input_id     = str_replace( 'gf_field_', '', $merge_tag );
			$source_field = GFAPI::get_field( $object->form_id, $input_id );
			if ( GFCommon::is_product_field( $source_field->type ) ) {
				$value = GFCommon::to_number( $value, $object->currency );
			}
		} else {
			// Even if this isn't a product field, it might have a currency value in it.
			// Handle it accordingly.
			// TODO:  Handle other currency locales
			$language = get_user_locale();
			if (str_contains($language, "en")) {
				$value = preg_replace("/[\$\,]/", "", $value);
			}
		}
		return $value;
	}

}

function gppa_aggregate_functions() {
	return GPPA_Aggregate_Functions::get_instance();
}

gppa_aggregate_functions();