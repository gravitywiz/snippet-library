<?php
/**
 * Gravity Wiz // Gravity Forms // IPv4 + CIDR Validation + Live Mask
 * https://gravitywiz.com/
 *
 * Adds live and server-side validation for IPv4 addresses with optional CIDR notation.
 *
 * Supported formats:
 * - Standard IPv4: 192.168.1.1
 * - CIDR notation: 192.168.1.1/24
 *
 * Instructions:
 * 1. Add this snippet to your site. See https://gravitywiz.com/documentation/how-do-i-install-a-snippet/.
 * 2. Add 'validate-ipv4' to the "Custom CSS Class" field setting of the Single Line Text field(s) you want to validate.
 */
class GW_IPv4_Mask_Validation {

	public function __construct() {

		add_action( 'gform_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_filter( 'gform_field_validation', array( $this, 'validate_ipv4' ), 10, 4 );

	}

	/**
	 * Frontend Live Validation + Input Mask
	 */
	public function enqueue_script() {
		?>

		<script>
		(function() {

			function isIPv4Valid(value) {

				var regex = /^((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)\.){3}(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\/([0-9]|[12][0-9]|3[0-2]))?$/;

				return regex.test(value);

			}

			function sanitizeInput(value) {
				return value.replace(/[^0-9./]/g, '');
			}

			document.addEventListener('input', function(e) {

				var input = e.target;

				if (!input.closest('.gf-ipv4-mask')) return;

				input.value = sanitizeInput(input.value);

				validateField(input);

				// Mobile numeric keypad
				input.setAttribute('inputmode', 'numeric');

			});

			document.addEventListener('blur', function(e) {

				var input = e.target;

				if (!input.closest('.gf-ipv4-mask')) return;

				validateField(input);

			}, true);

			function validateField(input) {

				var container = input.closest('.gfield');
				var value = input.value.trim();

				if (value === '') {
					container.classList.remove('gfield_error');
					return;
				}

				if (!isIPv4Valid(value)) {
					container.classList.add('gfield_error');
				} else {
					container.classList.remove('gfield_error');
				}

			}

		})();
		</script>

		<?php
	}

	/**
	 * Server-side Validation
	 */
	public function validate_ipv4( $result, $value, $form, $field ) {

		if ( $field->type !== 'text' ) {
			return $result;
		}

		if ( strpos( $field->cssClass, 'gf-ipv4-mask' ) === false ) {
			return $result;
		}

		if ( rgblank( $value ) ) {
			return $result;
		}

		$value = trim( $value );

		$regex = '/^
		(
			(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)
			\.
		){3}
		(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)
		(\/([0-9]|[12][0-9]|3[0-2]))?
		$/x';

		if ( ! preg_match( $regex, $value ) ) {

			$result['is_valid'] = false;
			$result['message']  = 'Please enter a valid IPv4 address (example: 192.168.1.1 or 192.168.1.1/24)';

		}

		return $result;

	}

}

new GW_IPv4_Mask_Validation();
