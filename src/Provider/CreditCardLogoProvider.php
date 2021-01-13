<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Provider;

use Configuration;
use Mollie\Config\Config;
use Mollie\Utility\CustomLogoUtility;
use Mollie\Utility\ImageUtility;
use MolPaymentMethod;

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

	public function __construct($localPath, $pathUri)
	{
		$this->localPath = $localPath;
		$this->pathUri = $pathUri;
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
