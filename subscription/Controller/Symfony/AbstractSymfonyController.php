<?php

namespace Mollie\Subscription\Controller\Symfony;

use Module;
use Mollie;
use Mollie\ServiceProvider\LeagueServiceContainerProvider;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

/**
 * Class AbstractAdminController - an abstraction for all admin module controllers
 */
abstract class AbstractSymfonyController extends FrameworkBundleAdminController
{
    /** @var LeagueServiceContainerProvider */
    protected $serviceProvider;

    /** @var Mollie */
    protected $module;

    public function __construct()
    {
        parent::__construct();

        $this->serviceProvider = new LeagueServiceContainerProvider();

        /* @phpstan-ignore-next-line */
        $this->module = Module::getInstanceByName('mollie');
    }
}
