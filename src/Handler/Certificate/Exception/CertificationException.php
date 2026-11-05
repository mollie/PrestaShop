<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Handler\Certificate\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CertificationException extends \Exception
{
    public const FILE_COPY_EXCEPTON = 0;
    public const DIR_CREATION_EXCEPTON = 1;
    public const FILE_CONFLICT_EXCEPTION = 2;
}
