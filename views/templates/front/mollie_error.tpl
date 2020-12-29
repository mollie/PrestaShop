{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}

<div class="container js-mollie-payment-error">
    <article class="alert alert-danger" role="alert" data-alert="danger">
        <ul id="mollie-notifications">
            <li>{$errorMessage|escape:'html':'UTF-8'}</li>
        </ul>
    </article>
</div>