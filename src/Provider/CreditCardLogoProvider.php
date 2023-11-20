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

namespace Mollie\Provider;

use Configuration;
use Mollie\Config\Config;
use Mollie\Factory\ModuleFactory;
use Mollie\Utility\CustomLogoUtility;
use Mollie\Utility\ImageUtility;
use MolPaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class CreditCardLogoProvider extends AbstractCustomLogoProvider
{
    /**
     * @var string
     */
    private $localPath;

    /**
     * @var string
     */
    private $pathUri;

    public function __construct(ModuleFactory $moduleFactory)
    {
        $this->localPath = $moduleFactory->getLocalPath();
        $this->pathUri = $moduleFactory->getPathUri();
    }

    public function getName()
    {
        return 'customCreditCardLogo';
    }

    public function getLocalPath()
    {
        return $this->localPath;
    }

    public function getPathUri()
    {
        return $this->pathUri;
    }

    public function getMethodOptionLogo(MolPaymentMethod $methodObj)
    {
        $isCustomLogoEnabled = CustomLogoUtility::isCustomLogoEnabled($methodObj->id_method);
        $imageConfig = Configuration::get(Config::MOLLIE_IMAGES);

        if (Config::LOGOS_HIDE !== $imageConfig && $isCustomLogoEnabled && $this->logoExists()) {
            $dateStamp = time();

            return $this->getLogoPathUri() . "?{$dateStamp}";
        }

        $image = json_decode($methodObj->images_json, true);

        return ImageUtility::setOptionImage($image, $imageConfig);
    }
}
