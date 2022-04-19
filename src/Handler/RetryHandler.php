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

namespace Mollie\Handler;

use Mollie\Exception\RetryOverException;

class RetryHandler
{
    const DEFAULT_MAX_RETRY = 3;
    const DEFAULT_WAIT_TIME = 1;
    const DEFAULT_ACCEPTED_EXCEPTION = 'RuntimeException';

    /**
     * @var callable
     */
    protected $_proc;

    /**
     * @throws RetryOverException
     */
    public function retry($proc, array $options = [], $moreOptions = [])
    {
        $this->_proc = $proc;
        $options = array_merge($options, $moreOptions);

        $max = $options['max'] ?? self::DEFAULT_MAX_RETRY;
        $wait = $options['wait'] ?? self::DEFAULT_WAIT_TIME;
        $exception = $options['accepted_exception'] ?? self::DEFAULT_ACCEPTED_EXCEPTION;

        return $this->_retry($max, $wait, $exception);
    }

    protected function _retry($max, $wait, $acceptedException)
    {
        $tries = 0;
        while ($tries++ < $max) {
            try {
                return call_user_func($this->_proc);
            } catch (\Exception $e) {
                if (!is_a($e, $acceptedException)) {
                    throw $e;
                }
            }
            sleep($wait);
        }

        throw new RetryOverException;
    }
}
