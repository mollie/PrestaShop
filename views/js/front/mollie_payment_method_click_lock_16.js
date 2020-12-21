/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

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
