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

namespace Step\Acceptance;

class Admin extends \AcceptanceTester
{
    const ORDERS_API = 'ORDERS_API';
    const PAYMENTS_API = 'PAYMENTS_API';

    public function loginAsAdmin()
    {
        $I = $this;
        if ($I->loadSessionSnapshot('loginAsAdmin')) {
            return;
        }
        $I->amOnPage('/admin-dev');
        $I->see('Email address');
        $I->submitForm('#login_form', [
            'email'          => 'prestashop@mollie.com',
            'passwd'         => 'mollie_test_156',
            'stay_logged_in' => '1',
        ], 'submitLogin');

        $I->waitForElement(['id' => 'header_logo'], 5);
        $I->see('Dashboard', ['css' => '.page-title']);
        $I->saveSessionSnapshot('loginAsAdmin');
    }

    public function uploadTheModule()
    {
        $I = $this;
        if ($I->loadSessionSnapshot('uploadTheModule')) {
            return;
        }
        if (version_compare(getenv('PRESTASHOP_VERSION'), '1.7.0.0', '>=')) {
            $I->navigateToModuleList();

            $I->click(['css' => 'a#page-header-desc-configuration-add_module']);
            $I->waitForElementVisible(['css' => '.module-import-start-select-manual']);
            $I->attachFile(['css' => 'input.dz-hidden-input[type=file]'], 'mollie.zip');
            $I->waitForElementVisible(['css' => '.module-import-success-configure'], 30);
            $I->click('Configure');
        }
        $I->saveSessionSnapshot('uploadTheModule');
    }

    public function configurePrestaShop()
    {
        $I = $this;
        if ($I->loadSessionSnapshot('configurePrestaShop')) {
            return;
        }
        $I->click(['id' => 'subtab-AdminAdvancedParameters']);
        $I->waitForElementVisible(['id' => 'subtab-AdminPerformance']); // Menu animation
        $I->click(['id' => 'subtab-AdminPerformance']);
        $I->selectOption(['id' => 'form_smarty_template_compilation'], '1');
        $I->click(['css' => '.card-footer .btn.btn-primary']);
        $I->saveSessionSnapshot('configurePrestaShop');
    }

    public function configureTheModule($api = self::ORDERS_API)
    {
        $I = $this;
        if ($I->loadSessionSnapshot('configureTheModule')) {
            return;
        }

        $I->navigateToModulePage();
        $I->fillField(['id' => 'MOLLIE_API_KEY'], getenv('MOLLIE_API_KEY'));
        $I->click(['id' => 'module_form_submit_btn_1']);
        $I->saveSessionSnapshot('configureTheModule');
    }

    public function navigateToModuleList()
    {
        $I = $this;
        $I->click(['css' => 'li#subtab-AdminParentModulesSf > a']);
        $I->waitForElementVisible(['id' => 'subtab-AdminModulesSf']);
        $I->click(['css' => 'li#subtab-AdminModulesSf > a']);
        $I->makeScreenshot();
    }

    public function navigateToModulePage()
    {
        $I = $this;
        $I->navigateToModuleList();

        $I->scrollTo(['css' => 'a[href*="configure/mollie"]'], 0, 200);
        $I->click(['css' => 'a[href*="configure/mollie"]']);
    }
}
