<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

class Language
{
    /**
     * @return int
     **/
    public function getDefaultLanguageId()
    {
        return \Configuration::get('PS_LANG_DEFAULT');
    }

    /**
     * @return array
     **/
    public function getAllLanguages()
    {
        return \Language::getLanguages(false);
    }
}
