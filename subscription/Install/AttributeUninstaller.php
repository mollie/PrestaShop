<?php

declare(strict_types=1);

namespace Mollie\Subscription\Install;

use Mollie\Subscription\Adapter\Configuration;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Logger\LoggerInterface;
use PrestaShopException;
use Psr\Log\LogLevel;

class AttributeUninstaller extends AbstractUninstaller
{
    private const FILE_NAME = 'AttributeUninstaller';

    /** @var Configuration */
    private $configuration;

    /** @var MollieSubscription */
    private $module;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        Configuration $configuration,
        MollieSubscription $module
    ) {
        $this->configuration = $configuration;
        $this->module = $module;
        $this->logger = $logger;
    }

    public function uninstall(): bool
    {
        try {
            foreach (Config::getSubscriptionAttributeOptions() as $attributeName => $attributeConfigKey) {
                $attribute = new \Attribute($this->configuration->get($attributeConfigKey));
                $attribute->delete();
            }

            $attributeGroup = new \AttributeGroup($this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_GROUP));
            $attributeGroup->delete();
        } catch (PrestaShopException $e) {
            $this->errors[] = $this->module->l('Failed to delete attributes', self::FILE_NAME);
            $this->logger->log(LogLevel::ERROR, $e->getMessage());

            return false;
        }

        return true;
    }
}
