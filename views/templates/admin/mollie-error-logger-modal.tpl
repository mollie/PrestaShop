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
<!-- Modal -->
<div class="modal fade" id="errorLoggingModal" tabindex="-1" role="dialog" aria-labelledby="errorLoggingModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="errorLoggingModalTitle" style="font-weight: 600;">{l s='Error and Logging Information Sharing Consent' mod='mollie'}</h3>
            </div>
            <div class="modal-body">
                <p class="h4">{l s='To provide you with a better experience and improve our services, we would like to collect error and logging information from your device. This information will help us identify and fix any issues you may encounter while using our application.' mod='mollie'}</p>
                <p class="h4" style="font-weight: 600;">{l s='The error and logging information may include:' mod='mollie'}</p>
                <ul class="h4">
                    <li>{l s='Error messages and the request code.' mod='mollie'}</li>
                    <li>{l s='Device information (such as device model, operating system version)' mod='mollie'}</li>
                    <li>{l s='Application version' mod='mollie'}</li>
                    <li>{l s='Time and date of the error occurrence' mod='mollie'}</li>
                </ul>
                <br>
                <p class="h4">{l s='By granting consent, you agree to allow us to collect and analyze this information. Rest assured, all data will be treated in accordance with our privacy policy and will only be used for the purpose of improving our applications performance and stability.' mod='mollie'}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{l s='Close'}</button>
            </div>
        </div>
    </div>
</div>