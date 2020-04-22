<?php

namespace _PhpScoper5ea00cc67502b;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use _PhpScoper5ea00cc67502b\Symfony\Polyfill\Apcu as p;
if (!\extension_loaded('apc') && !\extension_loaded('apcu')) {
    return;
}
if (!\function_exists('_PhpScoper5ea00cc67502b\\apcu_add')) {
    if (\extension_loaded('Zend Data Cache')) {
        function apcu_add($key, $var = null, $ttl = 0)
        {
            return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Apcu\Apcu::apcu_add($key, $var, $ttl);
        }
        function apcu_delete($key)
        {
            return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Apcu\Apcu::apcu_delete($key);
        }
        function apcu_exists($keys)
        {
            return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Apcu\Apcu::apcu_exists($keys);
        }
        function apcu_fetch($key, &$success = null)
        {
            return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Apcu\Apcu::apcu_fetch($key, $success);
        }
        function apcu_store($key, $var = null, $ttl = 0)
        {
            return \_PhpScoper5ea00cc67502b\Symfony\Polyfill\Apcu\Apcu::apcu_store($key, $var, $ttl);
        }
    } else {
        function apcu_add($key, $var = null, $ttl = 0)
        {
            return \_PhpScoper5ea00cc67502b\apc_add($key, $var, $ttl);
        }
        function apcu_delete($key)
        {
            return \_PhpScoper5ea00cc67502b\apc_delete($key);
        }
        function apcu_exists($keys)
        {
            return \_PhpScoper5ea00cc67502b\apc_exists($keys);
        }
        function apcu_fetch($key, &$success = null)
        {
            return \_PhpScoper5ea00cc67502b\apc_fetch($key, $success);
        }
        function apcu_store($key, $var = null, $ttl = 0)
        {
            return \_PhpScoper5ea00cc67502b\apc_store($key, $var, $ttl);
        }
    }
    function apcu_cache_info($limited = \false)
    {
        return \_PhpScoper5ea00cc67502b\apc_cache_info('user', $limited);
    }
    function apcu_cas($key, $old, $new)
    {
        return \_PhpScoper5ea00cc67502b\apc_cas($key, $old, $new);
    }
    function apcu_clear_cache()
    {
        return \_PhpScoper5ea00cc67502b\apc_clear_cache('user');
    }
    function apcu_dec($key, $step = 1, &$success = \false)
    {
        return \_PhpScoper5ea00cc67502b\apc_dec($key, $step, $success);
    }
    function apcu_inc($key, $step = 1, &$success = \false)
    {
        return \_PhpScoper5ea00cc67502b\apc_inc($key, $step, $success);
    }
    function apcu_sma_info($limited = \false)
    {
        return \_PhpScoper5ea00cc67502b\apc_sma_info($limited);
    }
}
if (!\class_exists('_PhpScoper5ea00cc67502b\\APCUIterator', \false) && \class_exists('_PhpScoper5ea00cc67502b\\APCIterator', \false)) {
    class APCUIterator extends \_PhpScoper5ea00cc67502b\APCIterator
    {
        public function __construct($search = null, $format = \APC_ITER_ALL, $chunk_size = 100, $list = \APC_LIST_ACTIVE)
        {
            parent::__construct('user', $search, $format, $chunk_size, $list);
        }
    }
}
