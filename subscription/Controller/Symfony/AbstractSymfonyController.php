<?php

namespace Mollie\Subscription\Controller\Symfony;

use Module;
use Mollie;
use Mollie\Subscription;
use Mollie\Subscription\ServiceProvider\LeagueServiceContainerProvider;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

/**
 * Class AbstractAdminController - an abstraction for all admin module controllers
 */
abstract class AbstractSymfonyController extends FrameworkBundleAdminController
{
    /** @var LeagueServiceContainerProvider */
    protected $leagueContainer;

    /** @var Mollie */
    protected $module;

    public function __construct()
    {
        parent::__construct();
        $this->leagueContainer = new LeagueServiceContainerProvider();
        $this->module = Module::getInstanceByName('mollie');
    }
}
