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

namespace Mollie\Service;

use Mollie;
use Smarty;

class ApiTestService
{
    /**
     * @var ApiService
     */
    private $apiService;

    /**
     * @var Smarty
     */
    private $smarty;

    public function __construct(Mollie $module, ApiService $apiService, Smarty $smarty)
    {
        $this->module = $module;
        $this->apiService = $apiService;
        $this->smarty = $smarty;
    }

    public function getApiKeysTestResult($testKey, $liveKey)
    {
        $testKeyInfo = $this->getApiKeyInfo($testKey);
        $liveKeyInfo = $this->getApiKeyInfo($liveKey);

        $this->smarty->assign(
            [
                'testKeyInfo' => $testKeyInfo,
                'liveKeyInfo' => $liveKeyInfo
            ]
        );

        return $this->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/api_test_results.tpl');
    }

    public function getApiKeyInfo($apiKey)
    {
        if (!$apiKey) {
            return [
                'status' => false
            ];
        }
        $api = $this->apiService->setApiKey($apiKey, $this->module->version);
        if (!$api) {
            return [
                'status' => false
            ];
        }
        $methods = $api->methods->allAvailable()->getArrayCopy();

        return [
            'status' => true,
            'methods' => $this->getPaymentMethodsAsArray($methods)
        ];
    }

    private function getPaymentMethodsAsArray($methods)
    {
        $methodNameArray = [];

        foreach ($methods as $method) {
            $methodNameArray[] = $method->id;
        }

        return $methodNameArray;
    }
}
