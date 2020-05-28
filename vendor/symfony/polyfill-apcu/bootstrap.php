<?php

namespace _PhpScoper5ece82d7231e4;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use _PhpScoper5ece82d7231e4\Symfony\Polyfill\Apcu as p;
if (!\extension_loaded('apc') && !\extension_loaded('apcu')) {
    return;
}
if (\extension_loaded('Zend Data Cache')) {
    if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_add')) {
        function apcu_add($key, $var = null, $ttl = 0)
        {
            return \_PhpScoper5ece82d7231e4\Symfony\Polyfill\Apcu\Apcu::apcu_add($key, $var, $ttl);
        }
    }
    if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_delete')) {
        function apcu_delete($key)
        {
            return \_PhpScoper5ece82d7231e4\Symfony\Polyfill\Apcu\Apcu::apcu_delete($key);
        }
    }
    if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_exists')) {
        function apcu_exists($keys)
        {
            return \_PhpScoper5ece82d7231e4\Symfony\Polyfill\Apcu\Apcu::apcu_exists($keys);
        }
    }
    if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_fetch')) {
        function apcu_fetch($key, &$success = null)
        {
            return \_PhpScoper5ece82d7231e4\Symfony\Polyfill\Apcu\Apcu::apcu_fetch($key, $success);
        }
    }
    if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_store')) {
        function apcu_store($key, $var = null, $ttl = 0)
        {
            return \_PhpScoper5ece82d7231e4\Symfony\Polyfill\Apcu\Apcu::apcu_store($key, $var, $ttl);
        }
    }
} else {
    if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_add')) {
        function apcu_add($key, $var = null, $ttl = 0)
        {
            return \_PhpScoper5ece82d7231e4\apc_add($key, $var, $ttl);
        }
    }
    if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_delete')) {
        function apcu_delete($key)
        {
            return \_PhpScoper5ece82d7231e4\apc_delete($key);
        }
    }
    if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_exists')) {
        function apcu_exists($keys)
        {
            return \_PhpScoper5ece82d7231e4\apc_exists($keys);
        }
    }
    if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_fetch')) {
        function apcu_fetch($key, &$success = null)
        {
            return \_PhpScoper5ece82d7231e4\apc_fetch($key, $success);
        }
    }
    if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_store')) {
        function apcu_store($key, $var = null, $ttl = 0)
        {
            return \_PhpScoper5ece82d7231e4\apc_store($key, $var, $ttl);
        }
    }
}
if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_cache_info')) {
    function apcu_cache_info($limited = \false)
    {
        return \_PhpScoper5ece82d7231e4\apc_cache_info('user', $limited);
    }
}
if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_cas')) {
    function apcu_cas($key, $old, $new)
    {
        return \_PhpScoper5ece82d7231e4\apc_cas($key, $old, $new);
    }
}
if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_clear_cache')) {
    function apcu_clear_cache()
    {
        return \_PhpScoper5ece82d7231e4\apc_clear_cache('user');
    }
}
if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_dec')) {
    function apcu_dec($key, $step = 1, &$success = \false)
    {
        return \_PhpScoper5ece82d7231e4\apc_dec($key, $step, $success);
    }
}
if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_inc')) {
    function apcu_inc($key, $step = 1, &$success = \false)
    {
        return \_PhpScoper5ece82d7231e4\apc_inc($key, $step, $success);
    }
}
if (!\function_exists('_PhpScoper5ece82d7231e4\\apcu_sma_info')) {
    function apcu_sma_info($limited = \false)
    {
        return \_PhpScoper5ece82d7231e4\apc_sma_info($limited);
    }
}
if (!\class_exists('_PhpScoper5ece82d7231e4\\APCUIterator', \false) && \class_exists('_PhpScoper5ece82d7231e4\\APCIterator', \false)) {
    class APCUIterator extends \_PhpScoper5ece82d7231e4\APCIterator
    {
        public function __construct($search = null, $format = \APC_ITER_ALL, $chunk_size = 100, $list = \APC_LIST_ACTIVE)
        {
            parent::__construct('user', $search, $format, $chunk_size, $list);
        }
    }
}
