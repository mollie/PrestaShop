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

namespace Mollie\Shared\Infrastructure\Exception;

use Mollie\Exception\Code\ExceptionCode;
use Mollie\Exception\MollieException;

class CouldNotHandleAbstractRepository extends MollieException
{
    public static function failedToFindRecord(string $className, array $keyValues): self
    {
        return new self(
            sprintf(
                'Model [%s] was not found by [%s]',
                $className,
                json_encode($keyValues)
            ),
            ExceptionCode::INFRASTRUCTURE_FAILED_TO_FIND_RECORD
        );
    }
}
