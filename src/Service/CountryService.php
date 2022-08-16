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

namespace Mollie\Service;

use Context;
use Country;
use Mollie;

class CountryService
{
    const FILE_NAME = 'CountryService';

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    public function getActiveCountriesList($onlyActive = true)
    {
        $context = Context::getContext();
        $langId = $context->language->id;
        $countries = Country::getCountries($langId, $onlyActive);
        $countriesWithNames = [];
        $countriesWithNames[] = [
            'id' => 0,
            'name' => $this->module->l('All', self::FILE_NAME),
        ];
        foreach ($countries as $key => $country) {
            $countriesWithNames[] = [
                'id' => $key,
                'name' => $country['name'],
            ];
        }

        return $countriesWithNames;
    }
}
