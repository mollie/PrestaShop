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

declare(strict_types=1);

namespace Mollie\Subscription\Controller\Symfony;

use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionFAQController extends AbstractSymfonyController
{
    /** @var ?ContainerInterface */
    protected $container;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;

        parent::__construct();
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        return $this->redirectToRoute('admin_subscription_index');
    }
}
