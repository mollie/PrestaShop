<?php

namespace Mollie\Subscription\Controller\Symfony;

use Module;
use Mollie;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

/**
 * Class AbstractAdminController - an abstraction for all admin module controllers
 */
abstract class AbstractSymfonyController extends FrameworkBundleAdminController
{
    /** @var Mollie */
    protected $module;

    public function __construct()
    {
        parent::__construct();

        /* @phpstan-ignore-next-line */
        $this->module = Module::getInstanceByName('mollie');
    }
}
