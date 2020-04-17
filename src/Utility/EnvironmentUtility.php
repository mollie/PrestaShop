<?php

namespace Mollie\Utility;

use Tools;

class EnvironmentUtility
{
    /**
     * Check if local domain
     *
     * @param string|null $host
     *
     * @return bool
     *
     * @since 3.3.2
     */
    public static function isLocalEnvironment($host = null)
    {
        if (!$host) {
            $host = Tools::getHttpHost(false, false, true);
        }
        $hostParts = explode('.', $host);
        $tld = end($hostParts);

        return in_array($tld, ['localhost', 'test', 'dev', 'app', 'local', 'invalid', 'example'])
            || (filter_var($host, FILTER_VALIDATE_IP)
                && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE));
    }
}