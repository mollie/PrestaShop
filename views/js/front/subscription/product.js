$(document).ready(function () {
    $(document).ajaxComplete(function (event, xhr, settings) {
      if (isVersionGreaterOrEqualTo177) {
        return;
      }

      if (
        settings.url.toLowerCase().indexOf('controller=cart') > 0 &&
        settings.data.toLowerCase().indexOf('action=update') > 0 &&
        settings.data.toLowerCase().indexOf('add=1') > 0
      ) {
        validateProduct(getProductData());
      }
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
