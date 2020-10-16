// the code in this file is related with preventing users from double clicking the payment method on 1.6 prestashop version.
$(document).ready(function () {
  var actionPlaceholder = '#'

  $(document).on('click', '.mollie_method', function (e) {
    var href = $(this).attr('href')

    // not disabling anything since its not a link action
    if (href === actionPlaceholder) {
      return;
    }

    var $self = $(this);

    if ($self.hasClass('locked')) {
      e.preventDefault();
    }

    $self.addClass('locked')
  })
})
