$(function() {
  var actionContext = {};

  function showModal(action, productId) {
    var text = '';
    switch(action) {
      case 'refund':
        text = window.mollieOrderInfoModalTextRefund || 'Are you sure you want to refund this product?';
        break;
      case 'ship':
        text = window.mollieOrderInfoModalTextShip || 'Are you sure you want to ship this product?';
        break;
      case 'refund_all':
        text = window.mollieOrderInfoModalTextRefundAll || 'Are you sure you want to refund the order?';
        break;
      case 'ship_all':
        text = window.mollieOrderInfoModalTextShipAll || 'Are you sure you want to ship all products?';
        break;
    }
    $('#mollieOrderActionModalText').text(text);
    actionContext = { action: action, productId: productId };
    $('#mollieOrderActionModal').modal('show');
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

  $('#mollieOrderActionModalConfirm').on('click', function() {
    // Here you would trigger the actual refund/ship logic, e.g. AJAX call
    // Use actionContext.action and actionContext.productId
    $('#mollieOrderActionModal').modal('hide');
    // Example: window.location.reload();
  });
});