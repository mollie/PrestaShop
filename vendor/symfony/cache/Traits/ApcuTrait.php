<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Traits;

use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\CacheItem;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Exception\CacheException;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @internal
 */
trait ApcuTrait
{
    public static function isSupported()
    {
        return \function_exists('_PhpScoper5ea00cc67502b\\apcu_fetch') && \filter_var(\ini_get('apc.enabled'), \FILTER_VALIDATE_BOOLEAN);
    }
    private function init($namespace, $defaultLifetime, $version)
    {
        if (!static::isSupported()) {
            throw new \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\Exception\CacheException('APCu is not enabled.');
        }
        if ('cli' === \PHP_SAPI) {
            \ini_set('apc.use_request_time', 0);
        }
        parent::__construct($namespace, $defaultLifetime);
        if (null !== $version) {
            \_PhpScoper5ea00cc67502b\Symfony\Component\Cache\CacheItem::validateKey($version);
            if (!apcu_exists($version . '@' . $namespace)) {
                $this->doClear($namespace);
                apcu_add($version . '@' . $namespace, null);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function doFetch(array $ids)
    {
        try {
            foreach (apcu_fetch($ids, $ok) ?: [] as $k => $v) {
                if (null !== $v || $ok) {
                    (yield $k => $v);
                }
            }
        } catch (\Error $e) {
            throw new \ErrorException($e->getMessage(), $e->getCode(), \E_ERROR, $e->getFile(), $e->getLine());
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function doHave($id)
    {
        return apcu_exists($id);
    }
    /**
     * {@inheritdoc}
     */
    protected function doClear($namespace)
    {
        return isset($namespace[0]) && \class_exists('_PhpScoper5ea00cc67502b\\APCuIterator', \false) && ('cli' !== \PHP_SAPI || \filter_var(\ini_get('apc.enable_cli'), \FILTER_VALIDATE_BOOLEAN)) ? apcu_delete(new \_PhpScoper5ea00cc67502b\APCuIterator(\sprintf('/^%s/', \preg_quote($namespace, '/')), APC_ITER_KEY)) : apcu_clear_cache();
    }
    /**
     * {@inheritdoc}
     */
    protected function doDelete(array $ids)
    {
        foreach ($ids as $id) {
            apcu_delete($id);
        }
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    protected function doSave(array $values, $lifetime)
    {
        try {
            if (\false === ($failures = apcu_store($values, null, $lifetime))) {
                $failures = $values;
            }
            return \array_keys($failures);
        } catch (\Error $e) {
        } catch (\Exception $e) {
        }
        if (1 === \count($values)) {
            // Workaround https://github.com/krakjoe/apcu/issues/170
            apcu_delete(\key($values));
        }
        throw $e;
    }
}
