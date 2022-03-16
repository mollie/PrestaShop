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

final class SecondChanceRowAction extends AbstractRowAction
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'second_chance';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        /*
         * options passed to the resolver will be available in the Grid Row action
         * and also in the template responsible of rendering the action.
         */

        $resolver
            ->setRequired([
                'route',
                'route_param_name',
                'route_param_field',
            ])
            ->setDefaults([
                'use_inline_display' => false,
                'accessibility_checker' => null,
            ])
            ->setAllowedTypes('route', 'string')
            ->setAllowedTypes('route_param_name', 'string')
            ->setAllowedTypes('route_param_field', 'string')
            ->setAllowedTypes('accessibility_checker', [AccessibilityCheckerInterface::class, 'callable', 'null'])
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
