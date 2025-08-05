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
<div class="modal fade" id="mollieShipModal" tabindex="-1" role="dialog" aria-labelledby="mollieShipModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="mollieShipModalLabel">{l s='Confirm Shipment' mod='mollie'}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p>{l s='Please provide shipping information:' mod='mollie'}</p>

        <div class="form-group">
          <label for="mollie-carrier">{l s='Carrier (Optional)' mod='mollie'}</label>
          <input type="text" class="form-control" id="mollie-carrier" placeholder="{l s='e.g., DHL, UPS, FedEx' mod='mollie'}">
        </div>

        <div class="form-group">
          <label for="mollie-tracking-number">{l s='Tracking Number (Optional)' mod='mollie'}</label>
          <input type="text" class="form-control" id="mollie-tracking-number" placeholder="{l s='Enter tracking number' mod='mollie'}">
        </div>

        <div class="form-group">
          <label for="mollie-tracking-url">{l s='Tracking URL (Optional)' mod='mollie'}</label>
          <input type="url" class="form-control" id="mollie-tracking-url" placeholder="{l s='https://example.com/track/123456' mod='mollie'}">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Cancel' mod='mollie'}</button>
        <button type="button" class="btn btn-primary" id="mollieShipModalConfirm">{l s='Confirm Shipment' mod='mollie'}</button>
      </div>
    </div>
  </div>
</div>