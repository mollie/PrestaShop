// the code in this file is related with preventing users from double clicking the payment method on 1.6 prestashop version.
$(document).ready(function () {
  // a helper to release the lock in case of an error.
  var releaseLock = function($paymentMethod) {
    setTimeout(function () { $paymentMethod.removeClass('locked') }, 2000)
  }

  $(document).on('click', '.mollie_method', function (e) {
    var $self = $(this);

    if ($self.hasClass('locked')) {
      e.preventDefault();
      releaseLock($self)
    }

    $self.addClass('locked')

    releaseLock($self)
  })
})
