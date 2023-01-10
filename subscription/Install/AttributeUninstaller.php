<?php

declare(strict_types=1);

namespace Mollie\Subscription\Install;

use AttributeCore as Attribute;
use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Logger\LoggerInterface;
use PrestaShopException;
use Psr\Log\LogLevel;

class AttributeUninstaller extends AbstractUninstaller
{
    private const FILE_NAME = 'AttributeUninstaller';

    /** @var ConfigurationAdapter */
    private $configuration;

    /** @var Mollie */
    private $module;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        ConfigurationAdapter $configuration,
        Mollie $module
    ) {
        $this->configuration = $configuration;
        $this->module = $module;
        $this->logger = $logger;
    }

    public function uninstall(): bool
    {
        try {
            foreach (Config::getSubscriptionAttributeOptions() as $attributeName => $attributeConfigKey) {
                $attribute = new Attribute($this->configuration->get($attributeConfigKey));
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
