/**
 * Gravity Shop // Product Configurator // Update product price when form total changes
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * Instructions:
 *  1. Add to form using https://gravitywiz.com/gravity-forms-code-chest/
 */
jQuery('.ginput_total_GFFORMID').on('change', function() {
  var total = jQuery(this).val();
  var formatted = gformFormatMoney(total, true);

  // Find any character outside of 0-9, period, or comma and wrap it with span.woocommerce-Price-currencySymbol
  formatted = formatted.replace(/[^0-9\.,]/g, '<span class="woocommerce-Price-currencySymbol">$&</span>');

  jQuery('p.price .woocommerce-Price-amount bdi').html(formatted);
});
