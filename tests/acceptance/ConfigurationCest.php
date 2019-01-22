<?php 

class ConfigurationCest
{
    private $oldToken = '';
    private $newToken = '';

    public function _before(\Step\Acceptance\Admin $I)
    {
        $I->loginAsAdmin();
        $I->uploadTheModule();
        $I->configurePrestaShop();
        $I->configureTheModule();

        if (!$this->oldToken || !$this->newToken) {
            $I->click(['css' => '#tab-AdminDashboard > a']);
            $this->oldToken = $I->grabFromCurrentUrl('#token=([a-f0-9]{32})#');
            $I->click(['css' => '#subtab-AdminParentModulesSf > a']);
            $I->waitForElementVisible(['id' => 'subtab-AdminModulesSf']); // Menu animation
            $I->click(['css' => '#subtab-AdminModulesSf > a']);
            $this->newToken = $I->grabFromCurrentUrl('#_token=([a-zA-Z0-9_-]{42})#');
        }
    }

    public function visitTheConfigurationPage(AcceptanceTester $I)
    {
        $I->makeScreenshot();
        $I->see('Mollie');
    }
}
