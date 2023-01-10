<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

use Mollie\Api\Types\PaymentMethod;
use Mollie\Provider\ProfileIdProviderInterface;

class MolliePayScreenModuleFrontController extends ModuleFrontController
{
    /** @var Mollie */
    public $module;

    public function postProcess()
    {
        $cardToken = Tools::getValue('mollieCardToken');
        $isSaveCard = (bool) Tools::getValue('mollieSaveCard');
        $useSavedCard = (bool) Tools::getValue('mollieUseSavedCard');

        $validateUrl = Context::getContext()->link->getModuleLink(
            'mollie',
            'payment',
            [
                'method' => PaymentMethod::CREDITCARD,
                'rand' => time(),
                'cardToken' => $cardToken,
                'saveCard' => $isSaveCard,
                'useSavedCard' => $useSavedCard,
            ],
            true
        );

        Tools::redirect($validateUrl);
    }

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign([
            'mollieIFrameJS' => 'https://js.mollie.com/v1/mollie.js',
            'price' => $this->context->cart->getOrderTotal(),
            'priceSign' => $this->context->currency->getSign(),
        ]);
        $this->setTemplate('module:mollie/views/templates/' . 'front/mollie_iframe.tpl');
    }

    public function setMedia()
    {
        /** @var ProfileIdProviderInterface $profileIdProvider */
        $profileIdProvider = $this->module->getService(ProfileIdProviderInterface::class);

        Media::addJsDef([
            'profileId' => $profileIdProvider->getProfileId($this->module->api),
        ]);
        $this->addJS("{$this->module->getPathUri()}views/js/front/mollie_iframe.js");
        $this->addCSS("{$this->module->getPathUri()}views/css/mollie_iframe.css");

        return parent::setMedia();
    }
}
