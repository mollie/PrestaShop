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

namespace Mollie\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

class AdminAjaxRightsTest extends TestCase
{
    const ORDER_ACTIONS = ['refundAll', 'refund', 'shipAll', 'ship', 'captureAll', 'capture', 'cancelAll', 'cancel'];

    public function testEveryAjaxControllerActionHasARight(): void
    {
        $source = $this->source('AdminMollieAjaxController');
        $mapped = $this->mapKeys($this->between($source, 'const ACTION_RIGHTS = [', '];'));
        $dispatched = $this->caseLabels($this->between($source, 'public function postProcess()', 'private function denyWithoutActionRight'));

        $this->assertNotEmpty($dispatched);
        $this->assertSame([], array_values(array_diff($dispatched, $mapped, self::ORDER_ACTIONS)));
        $this->assertSame([], array_values(array_diff($mapped, $dispatched)));
    }

    public function testAjaxControllerChecksTheRightBeforeDispatch(): void
    {
        $postProcess = $this->between($this->source('AdminMollieAjaxController'), 'public function postProcess()', 'private function denyWithoutActionRight');

        $this->assertLessThan(strpos($postProcess, 'switch ($action)'), strpos($postProcess, '$this->denyWithoutActionRight($action)'));
    }

    /**
     * @dataProvider provideSettingsControllers
     */
    public function testSettingsControllerChecksTheRightBeforeDispatch(string $controller, string $tab): void
    {
        $source = $this->source($controller);
        $displayAjax = $this->between($source, 'public function displayAjax(): void', 'private function denyWithoutTabRight');

        $this->assertLessThan(strpos($displayAjax, 'switch ($action)'), strpos($displayAjax, '$this->denyWithoutTabRight($requiredRight)'));
        $this->assertContains("\$this->context->employee, \$right, '" . $tab . "')", $source);
    }

    public function provideSettingsControllers(): array
    {
        return [
            ['AdminMollieAuthenticationController', 'AdminMollieAuthentication'],
            ['AdminMolliePaymentMethodsController', 'AdminMolliePaymentMethods'],
            ['AdminMollieAdvancedSettingsController', 'AdminMollieAdvancedSettings'],
        ];
    }

    public function testEveryWritingSettingsActionNeedsTheEditRight(): void
    {
        $expected = [
            'AdminMollieAuthenticationController' => ['saveApiKey', 'switchEnvironment', 'copyTestSettingsToLive'],
            'AdminMolliePaymentMethodsController' => ['togglePaymentMethod', 'savePaymentMethodSettings', 'updateMethodsOrder', 'refreshMethods', 'uploadCustomLogo', 'dismissExpressPromo'],
        ];

        foreach ($expected as $controller => $actions) {
            $source = $this->source($controller);
            $list = $this->quotedStrings($this->between($source, '$settingsMutatingActions = [', '];'));

            $this->assertSame([], array_values(array_diff($actions, $list)), $controller);
            $this->assertContains("in_array(\$action, \$settingsMutatingActions, true) ? 'edit' : 'view'", $source);
        }

        $this->assertContains("'saveSettings' === \$action ? 'edit' : 'view'", $this->source('AdminMollieAdvancedSettingsController'));
    }

    public function testLogDetailsCheckTheViewRightFirst(): void
    {
        $getLog = $this->between($this->source('AdminMollieLogsController'), 'public function displayAjaxGetLog()', 'protected function ajaxResponse');

        $this->assertLessThan(strpos($getLog, 'getService('), strpos($getLog, "verify(\$this->context->employee, 'view', 'AdminMollieLogs')"));
    }

    private function source(string $controller): string
    {
        return (string) file_get_contents(dirname(dirname(dirname(__DIR__))) . '/controllers/admin/' . $controller . '.php');
    }

    private function between(string $source, string $from, string $to): string
    {
        $start = strpos($source, $from);
        $this->assertNotFalse($start, $from);
        $end = strpos($source, $to, $start);
        $this->assertNotFalse($end, $to);

        return substr($source, $start, $end - $start);
    }

    private function mapKeys(string $block): array
    {
        preg_match_all("/'([A-Za-z]+)' =>/", $block, $matches);

        return $matches[1];
    }

    private function quotedStrings(string $block): array
    {
        preg_match_all("/'([A-Za-z]+)'/", $block, $matches);

        return $matches[1];
    }

    private function caseLabels(string $block): array
    {
        preg_match_all("/case '([A-Za-z]+)':/", $block, $matches);

        return $matches[1];
    }
}
