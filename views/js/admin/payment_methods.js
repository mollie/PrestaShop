/*
 * NOTICE OF LICENSE
 *
 * @author    INVERTUS, UAB www.invertus.eu <support@invertus.eu>
 * @copyright Copyright (c) permanent, INVERTUS, UAB
 * @license   Addons PrestaShop license limitation
 * @see       /LICENSE
 *
 *  International Registered Trademark & Property of INVERTUS, UAB
 */

$(document).ready(function() {
  // jquery sortable plugin must be included. @see https://jqueryui.com/sortable/
  var $sortableElement = $('#js-payment-methods-sortable')

  $sortableElement.sortable({
    appendTo: document.body,
    handle: '.js-sort-handle'
  });

  $sortableElement.bind( "sortupdate", function(event, ui) {
    $('.js-payment-option-position').each(function (index) {
      $(this).val(index)
    })
  });
})
