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

use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\MethodCollection;
use Mollie\Service\ApiKeyService;

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
		$testKeyInfo = $this->getApiKeyInfo($this->testKey, true);
		$liveKeyInfo = $this->getApiKeyInfo($this->liveKey, false);

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
	 * @param bool $isTestKey
	 *
	 * @return array
	 */
	public function getApiKeyInfo($apiKey, $isTestKey = true)
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
		try {
			/** @var BaseCollection|MethodCollection $methods */
			$methods = $api->methods->allAvailable();
		} catch (\Exception $e) {
			return [
				'status' => false,
			];
		}
		$methodsAsArray = $methods->getArrayCopy();

		if ($isTestKey) {
			$keyWarning = 0 !== strpos($apiKey, 'test');
		} else {
			$keyWarning = 0 !== strpos($apiKey, 'live');
		}

		return [
			'status' => true,
			'methods' => $this->getPaymentMethodsAsArray($methodsAsArray),
			'warning' => $keyWarning,
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
