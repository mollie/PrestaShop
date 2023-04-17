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

    function getProductData()
    {
      let productDetails = $('#product-details').attr('data-product');

      if (productDetails.length < 1) {
        return null;
      }

      productDetails = JSON.parse(productDetails);

      return {
        'id_product': productDetails.id_product,
        'id_product_attribute': productDetails.id_product_attribute,
      }
    }

    function validateProduct(product)
    {
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
                    successMsg(response.message);
                }
            }
        })
    }

    function successMsg(message) {
        $.growl.warning({ title: "", message: message, duration: 15000});
    }
});
