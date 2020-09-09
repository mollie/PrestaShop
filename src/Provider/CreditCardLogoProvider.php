<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
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

        if ($imageConfig !== Config::LOGOS_HIDE && $isCustomLogoEnabled && $this->logoExists()) {
            $dateStamp = time();
            return $this->getLogoPathUri() . "?{$dateStamp}";
        }

        $image = json_decode($methodObj->images_json, true);

        return ImageUtility::setOptionImage($image, $imageConfig);
    }
}
