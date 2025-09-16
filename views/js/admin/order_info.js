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

  function showModal(action, productId, productAmount, orderline) {
    var amount = productAmount;

    // For refund actions, get amount from input field if not provided
    if ((action === 'refund' || action === 'refundAll') && amount === undefined) {
      amount = $('#mollie-refund-amount').val();
    }

    // For capture actions, get amount from input field if not provided
    if ((action === 'capture' || action === 'captureAll') && amount === undefined) {
      amount = $('#mollie-capture-amount').val();
    }

    if (!transaction_id || !resource) {
      console.error('Missing required config values:', { transaction_id, resource });
      showErrorMessage('Configuration error');
      return;
    }

    actionContext = {
      action: action,
      productId: productId,
      transactionId: transaction_id,
      resource: resource,
      amount: amount,
      orderline: orderline || null,
    };

    if (action === 'refund' || action === 'refundAll') {
      // Update modal message based on action type
      if (action === 'refundAll') {
        $('#mollie-refund-modal-message').text('Are you sure you want to refund the full order amount? This action cannot be undone.');
      } else {
        $('#mollie-refund-modal-message').text('Are you sure you want to refund this order? This action cannot be undone.');
      }
      $('#mollieRefundModal').modal('show');
    } else if (action === 'ship' || action === 'shipAll') {
      $('#mollieShipModal').modal('show');
    } else if (action === 'capture' || action === 'captureAll') {
      // Update modal message based on action type
      if (action === 'captureAll') {
        $('#mollie-capture-modal-message').text('Are you sure you want to capture the full order amount?');
      } else {
        $('#mollie-capture-modal-message').text('Are you sure you want to capture this payment?');
      }
      $('#mollieCaptureModal').modal('show');
    } else if (action === 'cancel' || action === 'cancelAll') {
      // Update modal message based on action type
      if (action === 'cancelAll') {
        $('#mollie-cancel-modal-message').text('Are you sure you want to cancel the entire order? This action cannot be undone.');
      } else {
        $('#mollie-cancel-modal-message').text('Are you sure you want to cancel this order line? This action cannot be undone.');
      }
      $('#mollieCancelModal').modal('show');
    }
  }

  $('.mollie-refund-btn').on('click', function() {
    var productId = $(this).data('product');
    var amount = $(this).data('price');
    var orderline = $(this).data('orderline');

    showModal('refund', productId, amount, orderline);
  });

  $('.mollie-ship-btn').on('click', function() {
    var productId = $(this).data('product');
    var orderline = $(this).data('orderline');

    showModal('ship', productId, null, orderline);
  });

  $('.mollie-capture-btn').on('click', function() {
    var productId = $(this).data('product');
    var amount = $(this).data('price');

    showModal('capture', productId, amount);
  });

  $('.mollie-cancel-btn').on('click', function() {
    var orderline = $(this).data('orderline');

    showModal('cancel', null, null, orderline);
  });

  $('#mollie-initiate-refund').on('click', function() {
    var amount = $('#mollie-refund-amount').val();
    if (!amount || amount <= 0) {
      showErrorMessage('Please enter a valid refund amount');
      return;
    }
    showModal('refundAll', null);
  });

  $('#mollie-refund-all').on('click', function() {
    showModal('refundAll', null);
  });

  $('#mollie-refund-all-orders').on('click', function() {
    showModal('refundAll', null);
  });

  $('#mollie-initiate-capture').on('click', function() {
    var amount = $('#mollie-capture-amount').val();
    if (!amount || amount <= 0) {
      showErrorMessage('Please enter a valid capture amount');
      return;
    }
    showModal('captureAll', null, amount);
  });

  $('#mollie-ship-all').on('click', function() {
    showModal('shipAll', null);
  });

  $('#mollie-capture-all').on('click', function() {
    var amount = $('#mollie-capture-amount').val();
    if (!amount || amount <= 0) {
      showErrorMessage('Please enter a valid capture amount');
      return;
    }
    showModal('captureAll', null, amount);
  });

  $('#mollie-cancel-all').on('click', function() {
    showModal('cancelAll', null);
  });

  $('#mollieShipModal').on('show.bs.modal', function() {
    $('#mollie-skip-shipping-details').prop('checked', false);
    $('#mollie-carrier').val('');
    $('#mollie-tracking-number').val('');
    $('#mollie-tracking-url').val('');
    toggleShippingDetailsInputs(false);
  });

  $('#mollie-skip-shipping-details').on('change', function() {
    var isChecked = $(this).is(':checked');
    toggleShippingDetailsInputs(isChecked);
  });

  function toggleShippingDetailsInputs(disabled) {
    $('#mollie-carrier').prop('disabled', disabled);
    $('#mollie-tracking-number').prop('disabled', disabled);
    $('#mollie-tracking-url').prop('disabled', disabled);

    if (disabled) {
      $('#mollie-shipping-details-container').addClass('disabled');
      $('#mollie-carrier').val('');
      $('#mollie-tracking-number').val('');
      $('#mollie-tracking-url').val('');
    } else {
      $('#mollie-shipping-details-container').removeClass('disabled');
    }
  }

  $('#mollieRefundModalConfirm').on('click', function() {
    $('#mollieRefundModal').modal('hide');
    processOrderAction(actionContext);
  });

  $('#mollieShipModalConfirm').on('click', function() {
    $('#mollieShipModal').modal('hide');
    processOrderAction(actionContext);
  });

  $('#mollieCaptureModalConfirm').on('click', function() {
    $('#mollieCaptureModal').modal('hide');
    processOrderAction(actionContext);
  });

  $('#mollieCancelModalConfirm').on('click', function() {
    $('#mollieCancelModal').modal('hide');
    processOrderAction(actionContext);
  });

  function processOrderAction(context) {
    var data = {
      ajax: 1,
      action: context.action,
      orderId: order_id,
      transactionId: context.transactionId,
      productId: context.productId,

    };

    // Only add refundAmount for partial refunds, not for refundAll
    if (context.action === 'refund' && context.amount) {
      data.refundAmount = context.amount;
    } else if (context.action === 'refundAll') {
      // For refundAll, don't pass amount to let the service calculate the full refundable amount
      data.refundAmount = null;
    }

    // Add capture amount for capture actions
    if (context.action === 'capture' || context.action === 'captureAll') {
      data.captureAmount = context.amount;
    }

    if (context.productId && (context.action === 'refund' || context.action === 'ship' || context.action === 'capture')) {
      data.orderLines = [{
        id: context.productId,
      }];
    }

    if (context.action === 'ship' || context.action === 'shipAll') {
      var skipShippingDetails = $('#mollie-skip-shipping-details').is(':checked');

      if (!skipShippingDetails) {
        var carrier = $('#mollie-carrier').val().trim();
        var trackingNumber = $('#mollie-tracking-number').val().trim();
        var trackingUrl = $('#mollie-tracking-url').val().trim();

        data.tracking = {
          carrier: carrier || null,
          code: trackingNumber || null,
          tracking_url: trackingUrl || null
        };

      }
    }

    if (actionContext.orderline) {
      data.orderline = actionContext.orderline;
    }

    // Add cancel-specific data
    if (context.action === 'cancel' || context.action === 'cancelAll') {
      // Cancel actions don't need additional data beyond orderline
    }

    if (!ajax_url) {
      console.error('AJAX URL not found in config');
      showErrorMessage('AJAX URL not found');
      return;
    }

    $.ajax({
      url: ajax_url,
      type: 'POST',
      data: data,
      dataType: 'json',
      beforeSend: function() {
        showLoadingState();
      },
      success: function(response) {
        $('#mollie-loading').remove();
        if (response.success) {
          var successMessage = response.message || response.msg_success || 'Action completed successfully';
          if (response.detailed || response.msg_details) {
            successMessage += ' ' + (response.detailed || response.msg_details);
          }
          showSuccessMessage(successMessage);
          if (response.payment) {
            updatePaymentInfo(response.payment);
          }
          if (response.order) {
            updateOrderInfo(response.order);
          }
        } else {
          showErrorMessage(response.message || response.detailed || 'An error occurred');
        }
      },
      error: function(xhr, status, error) {
        $('#mollie-loading').remove();
        showErrorMessage('Network error occurred');
        console.error('AJAX Error:', error);
      }
    });
  }

  function showLoadingState() {
    $('body').append('<div id="mollie-loading" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;"><div style="background: white; padding: 20px; border-radius: 5px;">Processing...</div></div>');
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
    console.log('Payment updated:', payment);
  }

  function updateOrderInfo(order) {
    console.log('Order updated:', order);
  }
});