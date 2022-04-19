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

class RetryHandler implements RetryHandlerInterface
{
    const DEFAULT_MAX_RETRY = 3;
    const DEFAULT_WAIT_TIME = 1;
    const DEFAULT_ACCEPTED_EXCEPTION = RetryOverException::class;

    /**
     * @var callable
     */
    protected $function;

    /**
     * @throws RetryOverException
     */
    public function retry($function, array $options = [], $moreOptions = [])
    {
        $this->function = $function;
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
                return call_user_func($this->function);
            } catch (\Exception $e) {
                if (!is_a($e, $acceptedException)) {
                    throw $e;
                }
            }
            sleep($wait);
        }

        throw new RetryOverException();
    }
}
