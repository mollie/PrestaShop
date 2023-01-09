<?php

namespace Mollie\Subscription\Controller;

use ModuleAdminController;

/**
 * Class AbstractAdminController - an abstraction for all admin module controllers
 */
abstract class AbstractAdminController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }
}
