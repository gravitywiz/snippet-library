/**
 * Gravity Shop // Product Configurator // Sync Field Max to WooCommerce Quantity
 *
 * Sync a field's max to the max of the WooCommerce product's quantity input. Works with Variable Products.
 *
 * Instructions:
 *     1. Install our free Gravity Forms Code Chest plugin.
 *         Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the "JavaScript" setting under the Code Chest form settings subview.
 *     3. Add "sync-max-to-quantity" to the Custom CSS Class setting of the fields you want to sync the max.
 */
var $form = jQuery('#gform_GFFORMID');
var getSyncedInputs = function() {
    return $form.find('.sync-max-to-quantity input');
}

$form.on('found_variation', (event, variation) => {
  var maxQty = variation.max_qty;
  getSyncedInputs()
    .attr('max', maxQty)
    .each(function () {
      var $this = jQuery(this);
      if ($this.val() > maxQty) {
        $this.val(maxQty);
      }
    });
});
