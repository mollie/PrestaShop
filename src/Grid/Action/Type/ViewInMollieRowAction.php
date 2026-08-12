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

namespace Mollie\Grid\Action\Type;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\AbstractRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\AccessibilityChecker\AccessibilityCheckerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Row action linking an order to its payment in the Mollie dashboard.
 *
 * The core LinkRowAction is final and does not expose a "target" option, so we mirror it here
 * and add "target" to allow opening the dashboard in a new tab. Reusing the "link" type keeps the
 * core link.html.twig template, which already renders the target attribute.
 */
final class ViewInMollieRowAction extends AbstractRowAction
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'link';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired([
                'route',
                'route_param_name',
                'route_param_field',
            ])
            ->setDefaults([
                'confirm_message' => '',
                'accessibility_checker' => null,
                'extra_route_params' => [],
                'clickable_row' => false,
                'target' => '_blank',
                'attr' => [],
                'use_inline_display' => true,
            ])
            ->setAllowedTypes('route', 'string')
            ->setAllowedTypes('route_param_name', 'string')
            ->setAllowedTypes('route_param_field', 'string')
            ->setAllowedTypes('extra_route_params', 'array')
            ->setAllowedTypes('confirm_message', 'string')
            ->setAllowedTypes('accessibility_checker', [AccessibilityCheckerInterface::class, 'callable', 'null'])
            ->setAllowedTypes('clickable_row', 'boolean')
            ->setAllowedTypes('target', 'string')
            ->setAllowedTypes('attr', 'array')
            ->setAllowedTypes('use_inline_display', 'bool')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(array $record)
    {
        $accessibilityChecker = $this->getOptions()['accessibility_checker'];

        if ($accessibilityChecker instanceof AccessibilityCheckerInterface) {
            return $accessibilityChecker->isGranted($record);
        }

        if (is_callable($accessibilityChecker)) {
            return call_user_func($accessibilityChecker, $record);
        }

        return parent::isApplicable($record);
    }
}
