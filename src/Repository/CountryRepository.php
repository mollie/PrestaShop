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

namespace Mollie\Repository;

use Country;
use Db;

final class CountryRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(Country::class);
    }

    public function getMethodCountryIds($methodId)
    {
        $sql = 'SELECT id_country FROM `' . _DB_PREFIX_ . 'mol_country` WHERE id_method = "' . pSQL($methodId) . '"';

        $countryIds = Db::getInstance()->executeS($sql);
        $countryIdsArray = [];
        foreach ($countryIds as $countryId) {
            $countryIdsArray[] = $countryId['id_country'];
        }

        return $countryIdsArray;
    }

    public function updatePaymentMethodCountries($idMethod, $idCountries)
    {

        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'mol_country WHERE `id_method` = "' . $idMethod . '"';
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        if ($idCountries == false) {
            return true;
        }

        $response = true;
        foreach ($idCountries as $idCountry) {
            $allCountries = 0;
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'mol_country` (id_method, id_country, all_countries) VALUES (';

            if ($idCountry === '0') {
                $allCountries = 1;
            }
            $sql .= '"' . pSQL($idMethod) . '", ' . (int)$idCountry . ', ' . (int)$allCountries . ')';

            if (!Db::getInstance()->execute($sql)) {
                $response = false;
            }
        }

        return $response;
    }

    public function getExcludedCountryIds($methodId)
    {
        $sql = 'SELECT id_country
                    FROM `' . _DB_PREFIX_ . 'mol_excluded_country`
                    WHERE id_method = "' . pSQL($methodId) . '"';

        $countryIds = Db::getInstance()->executeS($sql);
        $countryIdsArray = [];
        foreach ($countryIds as $countryId) {
            $countryIdsArray[] = $countryId['id_country'];
        }

        return $countryIdsArray;
    }

    public function updatePaymentMethodExcludedCountries($idMethod, $idCountries)
    {

        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'mol_excluded_country WHERE `id_method` = "' . $idMethod . '"';
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        if ($idCountries == false) {
            return true;
        }

        $response = true;
        foreach ($idCountries as $idCountry) {
            $allCountries = 0;
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'mol_excluded_country` (id_method, id_country, all_countries)
                VALUES (';

            if ($idCountry === '0') {
                $allCountries = 1;
            }
            $sql .= '"' . pSQL($idMethod) . '", ' . (int)$idCountry . ', ' . (int)$allCountries . ')';

            if (!Db::getInstance()->execute($sql)) {
                $response = false;
            }
        }

        return $response;
    }
}
