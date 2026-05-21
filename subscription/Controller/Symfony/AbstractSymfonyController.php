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
     * Signature mirrors FrameworkBundleAdminController::get() (public, untyped)
     * for LSP compatibility on PS 8. On PS 9 the bridge was removed, so we
     * resolve through the injected container or fall back to the module's
     * service locator.
     */
    public function get($id)
    {
        if (isset($this->container) && $this->container->has($id)) {
            return $this->container->get($id);
        }

        return $this->module->getService($id);
    }
}
