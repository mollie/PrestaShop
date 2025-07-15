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

namespace Mollie\Presentation\Admin;

class AdminModuleTabTranslator
{
    private $tabTranslations = [
        'Mollie' => [
            'en' => 'Mollie',
        ],
        'Settings' => [
            'en' => 'Settings',
            'fr' => 'Paramètres',
            'de' => 'Einstellungen',
            'es' => 'Configuración',
            'it' => 'Impostazioni',
            'nl' => 'Instellingen',
            'pl' => 'Ustawienia',
            'pt' => 'Configuração',
        ],
    ];

    /**
     * Used to translate the tab name for the admin module.
     *
     * @param string $tabName
     * @param string $language
     *
     * @return string|null
     */
    public function translate(string $tabName, string $language): string
    {
        if (isset($this->tabTranslations[$tabName][$language])) {
            return $this->tabTranslations[$tabName][$language];
        }

        if (isset($this->tabTranslations[$tabName]['en'])) {
            return $this->tabTranslations[$tabName]['en'];
        }
        
        return 'Missing';
    }
}
