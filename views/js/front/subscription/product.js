/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */
$(document).ready(function () {
  prestashop.on('handleError', function(parameters) {
    if (parameters.eventType !== 'addProductToCart' || isVersionGreaterOrEqualTo177) {
      return;
    }

    validateProduct(getProductData());
  });

  prestashop.on('updateCart', function() {
    validateProduct(getProductData());
  });

  function getProductData() {
    const $productDetails = $('#product-details');

    if ($productDetails.length < 1) {
      return null;
    }

    const productDataAttribute = $productDetails.attr('data-product');

    if (!productDataAttribute) {
      return null;
    }

    const productData = JSON.parse(productDataAttribute);

    if (
      !productData.hasOwnProperty('id_product')
      || !productData.hasOwnProperty('id_product_attribute')
    ) {
      return null;
    }

    return {
      'id_product': productData.id_product,
      'id_product_attribute': productData.id_product_attribute,
    }
  }

  function validateProduct(product) {
    if (!product) {
      return;
    }

    $.ajax({
      url: mollieSubAjaxUrl,
      method: 'GET',
      data: {
        ajax: 1,
        action: 'validateProduct',
        product: product
      },
      success: function (response) {
        response = jQuery.parseJSON(response);

        if (!response.isValid) {
          noticeMessage(response.message);
        }
      }
    })
  }

  function noticeMessage(message) {
    $.growl.warning({ title: "", message: message, duration: 15000});
  }
});
