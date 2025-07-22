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

  function showModal(action, productId) {
    actionContext = { action: action, productId: productId };
    if (action === 'refund' || action === 'refund_all') {
      $('#mollieRefundModal').modal('show');
    } else if (action === 'ship' || action === 'ship_all') {
      $('#mollieShipModal').modal('show');
    }
  }

  $('.mollie-refund-btn').on('click', function() {
    var productId = $(this).data('product');
    showModal('refund', productId);
  });

  $('.mollie-ship-btn').on('click', function() {
    var productId = $(this).data('product');
    showModal('ship', productId);
  });

  $('#mollie-initiate-refund').on('click', function() {
    showModal('refund_all', null);
  });

  $('#mollie-ship-all').on('click', function() {
    showModal('ship_all', null);
  });

  // Refund modal confirm
  $('#mollieRefundModalConfirm').on('click', function() {
    $('#mollieRefundModal').modal('hide');
    // Implement refund logic here, e.g. submit form or AJAX
    // Example: $('#mollieRefundForm').submit();
    // Or trigger a custom event for maintainability
    $(document).trigger('mollie:refund:confirmed', [actionContext]);
  });

  // Ship modal confirm
  $('#mollieShipModalConfirm').on('click', function() {
    $('#mollieShipModal').modal('hide');
    // Implement ship logic here, e.g. submit form or AJAX
    $(document).trigger('mollie:ship:confirmed', [actionContext]);
  });
});