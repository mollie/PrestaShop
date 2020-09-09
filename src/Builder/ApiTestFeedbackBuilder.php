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

namespace Mollie\Builder;

use Mollie\Service\ApiService;

class ApiTestFeedbackBuilder implements TemplateBuilderInterface
{
    /**
     * @var ApiService
     */
    private $apiService;

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

    public function __construct($moduleVersion, ApiService $apiService)
    {
        $this->apiService = $apiService;
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
            'liveKeyInfo' => $liveKeyInfo
        ];
    }

    /**
     * @param $testKey string
     * @param $liveKey string
     * @return array
     */
    public function getApiKeysTestResult($testKey, $liveKey)
    {
        $testKeyInfo = $this->getApiKeyInfo($testKey);
        $liveKeyInfo = $this->getApiKeyInfo($liveKey);

        return  [
            'testKeyInfo' => $testKeyInfo,
            'liveKeyInfo' => $liveKeyInfo
        ];
    }

    /**
     * @param $apiKey string
     * @return array
     */
    public function getApiKeyInfo($apiKey)
    {
        if (!$apiKey) {
            return [
                'status' => false
            ];
        }
        $api = $this->apiService->setApiKey($apiKey, $this->moduleVersion);
        if (!$api) {
            return [
                'status' => false
            ];
        }
        /** @var  $methods */
        $methods = $api->methods->allAvailable();
        $methodsASArray = $methods->getArrayCopy();

        return [
            'status' => true,
            'methods' => $this->getPaymentMethodsAsArray($methodsASArray)
        ];
    }

    /**
     * @param $methods
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
