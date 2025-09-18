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
<div class="modal fade" id="mollieCancelModal" tabindex="-1" role="dialog" aria-labelledby="mollieCancelModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="mollieCancelModalLabel">{l s='Confirm Cancel' mod='mollie'}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p id="mollie-cancel-modal-message">{l s='Are you sure you want to cancel this order? This action cannot be undone.' mod='mollie'}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Cancel' mod='mollie'}</button>
        <button type="button" class="btn btn-danger" id="mollieCancelModalConfirm">{l s='Confirm Cancel' mod='mollie'}</button>
      </div>
    </div>
  </div>
</div>
