$(document).ready(function () {
    prestashop.on('updateCart', function() {
        const productDetails = JSON.parse(document.getElementById('product-details').dataset.product);
        const product =
            {
                'id_product': productDetails.id_product,
                'id_product_attribute': productDetails.id_product_attribute,
            }

        validateProduct(product);
    });

    function validateProduct(product)
    {
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

                    return false;
                }
            }
        })
    }

    function successMsg(message) {
        $.growl.warning({ title: "", message: message, duration: 15000});
    }
});
