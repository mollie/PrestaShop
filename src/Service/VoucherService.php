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

namespace Mollie\Service;

use Configuration;
use Feature;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Repository\AttributeRepository;

class VoucherService
{

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var ConfigurationAdapter
     */
    private $configuration;

    public function __construct(
        AttributeRepository $attributeRepository,
        ConfigurationAdapter $configuration
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->configuration = $configuration;
    }

    public function getVoucherCategory(array $cartItem, $selectedVoucherCategory)
    {
        switch ($selectedVoucherCategory) {
            case Config::MOLLIE_VOUCHER_CATEGORY_MEAL:
            case Config::MOLLIE_VOUCHER_CATEGORY_GIFT:
            case Config::MOLLIE_VOUCHER_CATEGORY_ECO:
                return $selectedVoucherCategory;
            case Config::MOLLIE_VOUCHER_CATEGORY_NULL:
            default:
                return $this->getProductCategory($cartItem);
        }
    }

    private function getProductCategory(array $cartItem)
    {
        $idFeatureValue = false;
        foreach ($cartItem['features'] as $feature) {
            if (!$this->isVoucherFeature($feature['id_feature'])) {
               continue;
            }
            $idFeatureValue = $feature['id_feature_value'];
        }

        if (!$idFeatureValue) {
            return '';
        }

        return $this->getVoucherCategoryByFeatureValueId($idFeatureValue);
    }

    private function isVoucherFeature($featureId)
    {
        return (int)$this->configuration->get(Config::MOLLIE_VOUCHER_FEATURE_ID) === (int)$featureId;
    }

    private function getVoucherCategoryByFeatureValueId($idFeatureValue)
    {
        foreach (Config::MOLLIE_VOUCHER_CATEGORIES as $key => $categoryName) {
            if ($this->configuration->get(Config::MOLLIE_VOUCHER_FEATURE . $key) === $idFeatureValue) {
                return $key;
            }
        }

        return '';
    }
}
