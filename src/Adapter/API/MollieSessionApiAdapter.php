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

namespace Mollie\Adapter\API;

use Mollie\Api\MollieApiClient;
use Mollie\DTO\Express\ExpressSession;
use Mollie\DTO\Express\ExpressSessionRequest;
use Mollie\Exception\MollieApiException;
use Mollie\Factory\ModuleFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieSessionApiAdapter implements MollieSessionApiAdapterInterface
{
    /** @var ModuleFactory */
    private $moduleFactory;

    public function __construct(ModuleFactory $moduleFactory)
    {
        $this->moduleFactory = $moduleFactory;
    }

    public function create(ExpressSessionRequest $request): ExpressSession
    {
        return ExpressSession::fromResource(
            $this->getApiClient()->sessions->create($request->jsonSerialize())
        );
    }

    public function get(string $sessionId): ExpressSession
    {
        return ExpressSession::fromResource(
            $this->getApiClient()->sessions->get($sessionId)
        );
    }

    /**
     * @throws MollieApiException
     */
    private function getApiClient(): MollieApiClient
    {
        $module = $this->moduleFactory->getModule();
        $api = $module ? $module->getApiClient() : null;

        if (!$api) {
            throw new MollieApiException('Mollie API is null. Check if API key is correct', MollieApiException::MOLLIE_API_IS_NULL);
        }

        return $api;
    }
}
