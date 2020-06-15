<?php

namespace Mollie\Service;

use Context;

class ErrorDisplayService
{
    public function showCookieError($id)
    {
        $context = Context::getContext();
        if (isset($context->cookie->$id)) {
            $context->controller->errors = $this->stripSlashesDeep(json_decode($context->cookie->$id));
            unset($context->cookie->$id);
            unset($_SERVER['HTTP_REFERER']);
        }
    }

    private function stripSlashesDeep($value)
    {
        $value = is_array($value) ?
            array_map('stripslashes', $value) :
            stripslashes($value);

        return $value;
    }
}