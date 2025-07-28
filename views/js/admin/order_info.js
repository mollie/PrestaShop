/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */
$(document).ready(function () {
  var actionContext = {};

  // Check if config is available
  if (typeof mollieOrderInfoConfig === 'undefined') {
    console.error('Mollie Order Info Config not found');
    return;
  }



    function showModal(action, productId, productAmount) {
      var transactionId = mollieOrderInfoConfig.transaction_id;
      var resource = mollieOrderInfoConfig.resource;
      var amount = productAmount || $('#mollie-refund-amount').val();

      if (!transactionId || !resource) {
        console.error('Missing required config values:', { transactionId, resource });
        showErrorMessage('Configuration error');
        return;
      }

      actionContext = {
        action: action,
        productId: productId,
        transactionId: transactionId,
        resource: resource,
        amount: amount
      };

      if (action === 'refund' || action === 'refund_all') {
        $('#mollieRefundModal').modal('show');
      } else if (action === 'ship' || action === 'ship_all') {
        $('#mollieShipModal').modal('show');
      }
  }

  $('.mollie-refund-btn').on('click', function() {
    var productId = $(this).data('product');
    var amount = $(this).closest('tr').find('td:nth-child(2)').text().replace(/[^0-9.,]/g, '');
    showModal('refund', productId, amount);
  });

  $('.mollie-ship-btn').on('click', function() {
    var productId = $(this).data('product');
    showModal('ship', productId);
  });

  $('#mollie-initiate-refund').on('click', function() {
    var amount = $('#mollie-refund-amount').val();
    if (!amount || amount <= 0) {
      showErrorMessage('Please enter a valid refund amount');
      return;
    }
    showModal('refund_all', null);
  });

  $('#mollie-ship-all').on('click', function() {
    showModal('ship_all', null);
  });

  // Refund modal confirm
  $('#mollieRefundModalConfirm').on('click', function() {
    $('#mollieRefundModal').modal('hide');
    processOrderAction(actionContext);
  });

  // Ship modal confirm
  $('#mollieShipModalConfirm').on('click', function() {
    $('#mollieShipModal').modal('hide');
    processOrderAction(actionContext);
  });

  function processOrderAction(context) {
    var data = {
      ajax: 1,
      action: 'processOrderAction',
      transactionId: context.transactionId,
      resource: context.resource,
      action: context.action,
      amount: context.amount,
      orderLines: context.orderLines || [],
      tracking: context.tracking,
      order: context.order || {}
    };

    if (context.productId && (context.action === 'refund' || context.action === 'ship')) {
      data.orderLines = [{
        id: context.productId,
        quantity: 1
      }];
    }

    var ajaxUrl = mollieOrderInfoConfig.ajax_url;
    if (!ajaxUrl) {
      console.error('AJAX URL not found in config');
      showErrorMessage('AJAX URL not found');
      return;
    }

    $.ajax({
      url: ajaxUrl,
      type: 'POST',
      data: data,
      dataType: 'json',
      beforeSend: function() {
        showLoadingState();
      },
      success: function(response) {
        hideLoadingState();
        if (response.success) {
          showSuccessMessage(response.message || 'Action completed successfully');
          if (response.payment) {
            updatePaymentInfo(response.payment);
          }
          if (response.order) {
            updateOrderInfo(response.order);
          }
        } else {
          showErrorMessage(response.message || 'An error occurred');
        }
      },
      error: function(xhr, status, error) {
        hideLoadingState();
        showErrorMessage('Network error occurred');
        console.error('AJAX Error:', error);
      }
    });
  }

  function showLoadingState() {
    $('body').append('<div id="mollie-loading" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;"><div style="background: white; padding: 20px; border-radius: 5px;">Processing...</div></div>');
  }

  function hideLoadingState() {
    $('#mollie-loading').remove();
  }

  function showSuccessMessage(message) {
    var alertHtml = '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + message + '</div>';
    $('.mollie-order-info-panel').prepend(alertHtml);
    setTimeout(function() {
      $('.alert-success').fadeOut();
    }, 5000);
  }

  function showErrorMessage(message) {
    var alertHtml = '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + message + '</div>';
    $('.mollie-order-info-panel').prepend(alertHtml);
    setTimeout(function() {
      $('.alert-danger').fadeOut();
    }, 5000);
  }

  function updatePaymentInfo(payment) {
    // Update payment information in the UI if needed
    console.log('Payment updated:', payment);
  }

  function updateOrderInfo(order) {
    // Update order information in the UI if needed
    console.log('Order updated:', order);
  }
});