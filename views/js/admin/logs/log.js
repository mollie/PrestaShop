/**
 * NOTICE OF LICENSE
 *
 * @author    Mastercard Inc. www.mastercard.com
 * @copyright Copyright (c) permanent, Mastercard Inc.
 * @license   Apache-2.0
 *
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Mastercard Inc.
 */

$(document).ready(function () {
  $('.log-modal-overlay').on('click', function (event) {
    $('.modal.open').removeClass('open');
    event.preventDefault();
  });

  $('.js-log-button').on('click', function (event) {
    var logId = $(this).data('log-id');
    var informationType = $(this).data('information-type');

    // NOTE: opening modal
    $('#' + $(this).data('target')).addClass('open');

    // NOTE: if information has been set already we don't need to call ajax again.
    if (!$('#log-modal-' + logId + '-' + informationType + ' .log-modal-content-data').hasClass('hidden')) {
      return;
    }

    $('.log-modal-content-spinner').removeClass('hidden');

    $.ajax({
      type: 'POST',
      url: mollie.logsUrl,
      data: {
        ajax: true,
        action: 'getLog',
        log_id: logId
      }
    })
      .then(response => jQuery.parseJSON(response))
      .then(data => {
        $('.log-modal-content-spinner').addClass('hidden');

        $('#log-modal-' + logId + '-request .log-modal-content-data').removeClass('hidden').html(prettyJson(data.log.request));
        $('#log-modal-' + logId + '-response .log-modal-content-data').removeClass('hidden').html(prettyJson(data.log.response));
        $('#log-modal-' + logId + '-context .log-modal-content-data').removeClass('hidden').html(prettyJson(data.log.context));
      })
  });
});

function prettyJson(json) {
  return JSON.stringify(jQuery.parseJSON(json), null, 2);
}
