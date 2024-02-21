/**
 * Gravity Wiz // Gravity Forms // Scientific Notation for Calculations
 * https://gravitywiz.com/
 *
 * Display calculation results in scientific notation (e.g. 176,021,565,000 → 1.760e11).
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Follow the inline comments to configure for your field.
 */
gform.addFilter( 'gform_calculation_format_result', function ( formattedResult, result, formulaField, formId, calcObj ) {
  // Update "4" to your Calculation field ID.
  if ( formulaField.field_id == 4 && result > 0 ) {
    formattedResult = result.toExponential( 3 ).replace( '+', '' );
  }
  return formattedResult;
} );
