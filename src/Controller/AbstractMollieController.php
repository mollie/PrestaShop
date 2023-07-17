<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

namespace Mollie\Controller;

use Mollie\Errors\Error;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Utility\PsVersionUtility;

class AbstractMollieController extends \ModuleFrontControllerCore
{
    protected function respond($status, $statusCode = HttpStatusCode::HTTP_OK, $message = '')
    {
        http_response_code($statusCode);

        $response = ['status' => $status];

        if ($message) {
            $response['error'] = new Error($statusCode, $message);
        }

        $this->ajaxRender(json_encode($response));
    }

    protected function ajaxRender($value = null, $controller = null, $method = null)
    {
        if (PsVersionUtility::isPsVersionLowerThan(_PS_VERSION_, '1.7.5.0')) {
            $this->ajaxDie($value, $controller, $method);

            exit;
        }

        parent::ajaxRender($value, $controller, $method);

        exit;
    }
}
