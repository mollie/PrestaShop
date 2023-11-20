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

namespace Mollie\Handler\Certificate\Exception;

use Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CertificationException extends Exception
{
    const FILE_COPY_EXCEPTON = 0;
    const DIR_CREATION_EXCEPTON = 1;
}
