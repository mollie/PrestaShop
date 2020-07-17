<?php

namespace Mollie\Service;

use Mollie\Config\Config;

class RepeatOrderLinkFactory
{
    public function getLink()
    {
        $globalContext = \Context::getContext();

        if (!Config::isVersion17()) {
            return $globalContext->link->getPageLink(
                'order',
                true,
                null
            );
        }

        return $globalContext->link->getPageLink(
            'cart',
            null,
            $globalContext->language->id,
            [
                'action' => 'show',
            ]
        );
    }
}