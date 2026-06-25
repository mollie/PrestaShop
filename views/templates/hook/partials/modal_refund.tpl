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
<div class="modal fade" id="mollieRefundModal" tabindex="-1" role="dialog" aria-labelledby="mollieRefundModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="mollieRefundModalLabel">{l s='Confirm Refund' mod='mollie'}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p id="mollie-refund-modal-message">{l s='Are you sure you want to refund this order? This action cannot be undone.' mod='mollie'}</p>
        <div class="form-group" id="mollie-refund-quantity-group" style="display:none;">
          <label for="mollie-refund-quantity">{l s='Quantity to refund' mod='mollie'}</label>
          <select class="form-control" id="mollie-refund-quantity"></select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Cancel' mod='mollie'}</button>
        <button type="button" class="btn btn-primary" id="mollieRefundModalConfirm">{l s='Confirm Refund' mod='mollie'}</button>
      </div>
    </div>
  </div>
</div>