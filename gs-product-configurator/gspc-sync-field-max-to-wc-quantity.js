/**
 * Gravity Shop // Product Configurator // Sync Field Max to WooCommerce Quantity
 *
 * Sync a field's max to the max of the WooCommerce product's quantity input. Works with Variable Products.
 *
 * Instructions:
 *     1. Install our free Gravity Forms Code Chest plugin.
 *         Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the "JavaScript" setting under the Code Chest form settings subview.
 */
var $form = jQuery('#gform_GFFORMID');
var $quantity = jQuery('.input-text.qty');
var quantityMax = $quantity.attr('max');

var getSyncedInputs = function() {
    return $form.find('.sync-max-to-quantity input');
}

// Create MutationObserver on $quantity's max attribute.
var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.attributeName === 'max') {
            quantityMax = $quantity.attr('max');

            getSyncedInputs()
                .attr('max', quantityMax)
                .each(function() {
                    var $this = jQuery(this);
                    if ($this.val() > quantityMax) {
                        $this.val(quantityMax);
                    }
              });
        }
    });
});

observer.observe($quantity[0], {
    attributes: true,
    attributeFilter: ['max']
});

// Listen to input events on the synced inputs to ensure they don't exceed the max.
getSyncedInputs().on('input', function() {
    var $this = jQuery(this);
    if ($this.val() > quantityMax) {
        $this.val(quantityMax);
    }
});
