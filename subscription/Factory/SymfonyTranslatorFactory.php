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

namespace Mollie\Subscription\Factory;

use Symfony\Contracts\Translation\TranslatorInterface as NewTranslatorInterface;  // Newer Symfony translator
use Symfony\Component\Translation\TranslatorInterface as OldTranslatorInterface;  // Older Symfony translator

class SymfonyTranslatorFactory
{
    /**
     * @return NewTranslatorInterface|OldTranslatorInterface
     */
    public function create() {
        if (interface_exists(NewTranslatorInterface::class)) {
            return NewTranslatorInterface();
        } else {
            return OldTranslatorInterface();
        }
    }
}
