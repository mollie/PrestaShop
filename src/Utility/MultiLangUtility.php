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

namespace Mollie\Utility;

use Language;

class MultiLangUtility
{
    public static function createMultiLangField($field, $languageIds = null)
    {
        $result = [];

        if (!$languageIds) {
            $languageIds = Language::getIDs(false);
        }

        foreach ($languageIds as $languageId) {
            $result[$languageId] = $field;
        }

        return $result;
    }
}
