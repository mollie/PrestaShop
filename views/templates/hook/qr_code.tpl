{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}

<div class="mollie-bancontact-container">
    <div class="mollie-bancontact-inputs">
        <input type="hidden" value="{$methodId|escape:'html':'UTF-8'}" name="mollie-method-id">
    </div>
</div>

<div class="modal fade" id="mollie-bancontact-modal" tabindex="-1" role="dialog"
     aria-labelledby="mollie-bancontact-modal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mollie-bancontact-modal-label">{l s='Scan QR' mod='mollie'}</h5>
            </div>
            <div class="modal-body" style="text-align: center;">
                <div style="margin: 5px">
                    <h6 class="modal-title"
                        id="mollie-bancontact-modal-label">{l s='Open your Bancontact app to scan the QR code' mod='mollie'}</h6>
                </div>
                <div>
                    <img id="mollie-bancontact-qr-code" src="#"/>
                </div>
                <div>
                    <h6 class="modal-title" id="mollie-bancontact-modal-label">{l s='Or' mod='mollie'}</h6>
                </div>
                <div style="margin: 5px">
                    <button type="button" class="btn btn-primary" id="js-mollie-bancontact-continue">{l s='Continue without QR code' mod='mollie'}</button>
                </div>
                <div style="text-align: right;">
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{l s='Cancel' mod='mollie'}</button>
                </div>
            </div>
        </div>
    </div>
</div>
