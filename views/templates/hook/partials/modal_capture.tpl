{**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 *}
<div class="modal fade" id="mollieCaptureModal" tabindex="-1" role="dialog" aria-labelledby="mollieCaptureModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="mollieCaptureModalLabel">{l s='Confirm Capture' mod='mollie'}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p id="mollie-capture-modal-message">{l s='Are you sure you want to capture this payment?' mod='mollie'}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Cancel' mod='mollie'}</button>
        <button type="button" class="btn btn-primary" id="mollieCaptureModalConfirm">{l s='Confirm Capture' mod='mollie'}</button>
      </div>
    </div>
  </div>
</div>