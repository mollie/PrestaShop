<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Builder;

use Mollie\Service\ApiKeyService;
use MolliePrefix\Mollie\Api\Resources\BaseCollection;
use MolliePrefix\Mollie\Api\Resources\MethodCollection;

class ApiTestFeedbackBuilder implements TemplateBuilderInterface
{
	/**
	 * @var ApiKeyService
	 */
	private $apiKeyService;

	/**
	 * @var string
	 */
	private $moduleVersion;

	/**
	 * @var string
	 */
	private $testKey;

	/**
	 * @var string
	 */
	private $liveKey;

	public function __construct($moduleVersion, ApiKeyService $apiKeyService)
	{
		$this->apiKeyService = $apiKeyService;
		$this->moduleVersion = $moduleVersion;
	}

	/**
	 * @return string
	 */
	public function getTestKey()
	{
		return $this->testKey;
	}

	/**
	 * @param string $testKey
	 */
	public function setTestKey($testKey)
	{
		$this->testKey = $testKey;
	}

	/**
	 * @return string
	 */
	public function getLiveKey()
	{
		return $this->liveKey;
	}

	/**
	 * @param string $liveKey
	 */
	public function setLiveKey($liveKey)
	{
		$this->liveKey = $liveKey;
	}

	/**
	 * @return array
	 */
	public function buildParams()
	{
		$testKeyInfo = $this->getApiKeyInfo($this->testKey);
		$liveKeyInfo = $this->getApiKeyInfo($this->liveKey);

		return [
			'testKeyInfo' => $testKeyInfo,
			'liveKeyInfo' => $liveKeyInfo,
		];
	}

	/**
	 * @param string $testKey
	 * @param string $liveKey
	 *
	 * @return array
	 */
	public function getApiKeysTestResult($testKey, $liveKey)
	{
		$testKeyInfo = $this->getApiKeyInfo($testKey);
		$liveKeyInfo = $this->getApiKeyInfo($liveKey);

		return [
			'testKeyInfo' => $testKeyInfo,
			'liveKeyInfo' => $liveKeyInfo,
		];
	}

	/**
	 * @param string $apiKey
	 *
	 * @return array
	 */
	public function getApiKeyInfo($apiKey)
	{
		if (!$apiKey) {
			return [
				'status' => false,
			];
		}
		$api = $this->apiKeyService->setApiKey($apiKey, $this->moduleVersion);
		if (!$api) {
			return [
				'status' => false,
			];
		}
		/** @var BaseCollection|MethodCollection $methods */
		$methods = $api->methods->allAvailable();
		$methodsAsArray = $methods->getArrayCopy();

		return [
			'status' => true,
			'methods' => $this->getPaymentMethodsAsArray($methodsAsArray),
		];
	}

	/**
	 * @param array $methods
	 *
	 * @return array
	 */
	private function getPaymentMethodsAsArray($methods)
	{
		$methodNameArray = [];

		foreach ($methods as $method) {
			$methodNameArray[] = $method->id;
		}

		return $methodNameArray;
	}
}
