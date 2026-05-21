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

namespace Mollie\Subscription\Controller\Symfony;

use Mollie;
use Mollie\Factory\ModuleFactory;
use Mollie\PsCompat\AdminBaseController;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AbstractAdminController - an abstraction for all admin module controllers
 */
abstract class AbstractSymfonyController extends AdminBaseController
{
    /** @var Mollie */
    protected $module;

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->module = (new ModuleFactory())->getModule();
    }

    /**
     * Backward-compatible service locator. PrestaShopAdminController removed
     * the FrameworkBundleAdminController::get() bridge, so subclasses that
     * still rely on `$this->get(...)` resolve through the injected service
     * container (passed as the first constructor argument in subclasses).
     */
    protected function get(string $id): object
    {
        if (isset($this->container) && $this->container->has($id)) {
            return $this->container->get($id);
        }

        return $this->module->getService($id);
    }
}
