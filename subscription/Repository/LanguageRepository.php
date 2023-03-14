<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

class LanguageRepository
{
    /**
     * @return int
     **/
    public function getDefaultLanguageId(): int
    {
        return (int) \Configuration::get('PS_LANG_DEFAULT');
    }

    /**
     * @return array
     **/
    public function getAllLanguages(): array
    {
        return \Language::getLanguages(false);
    }
}
