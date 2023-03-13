<?php

declare(strict_types=1);

namespace Mollie\Subscription\Install;

use AttributeGroup;
use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\ProductAttributeAdapter;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Logger\LoggerInterface;
use Mollie\Subscription\Repository\LanguageRepository;
use PrestaShopDatabaseException;
use PrestaShopException;
use Psr\Log\LogLevel;
use Validate;

class AttributeInstaller extends AbstractInstaller
{
    private const FILE_NAME = 'AttributeInstaller';

    /** @var ConfigurationAdapter */
    private $configuration;

    /** @var Mollie */
    private $module;

    /** @var LanguageRepository */
    private $language;

    /** @var LoggerInterface */
    private $logger;

    /** @var ProductAttributeAdapter */
    private $productAttributeAdapter;

    public function __construct(
        LoggerInterface $logger,
        ConfigurationAdapter $configuration,
        Mollie $module,
        LanguageRepository $language,
        ProductAttributeAdapter $productAttributeAdapter
    ) {
        $this->logger = $logger;
        $this->configuration = $configuration;
        $this->module = $module;
        $this->language = $language;
        $this->productAttributeAdapter = $productAttributeAdapter;
    }

    public function install(): bool
    {
        $languages = $this->language->getAllLanguages();
        try {
            $attributeGroup = $this->createAttributeGroup($languages);

            $this->createAttributes($languages, (int) $attributeGroup->id);
        } catch (PrestaShopDatabaseException|PrestaShopException $e) {
            $this->errors[] = $this->module->l('Failed to add attributes', self::FILE_NAME);
            $this->logger->log(LogLevel::ERROR, $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @param array<string, array<string, string>> $languages
     *
     * @return AttributeGroup
     */
    private function createAttributeGroup(array $languages): AttributeGroup
    {
        $existingAttributeGroup = new AttributeGroup($this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_GROUP));
        if (Validate::isLoadedObject($existingAttributeGroup)) {
            return $existingAttributeGroup;
        }

        $attributeGroup = new AttributeGroup();
        foreach ($languages as $language) {
            /* @phpstan-ignore-next-line */
            $attributeGroup->name[$language['id_lang']] = 'Mollie Subscription';
            /* @phpstan-ignore-next-line */
            $attributeGroup->public_name[$language['id_lang']] = 'Subscription';
        }

        $attributeGroup->group_type = 'select';
        $attributeGroup->add();
        $this->configuration->updateValue(Config::SUBSCRIPTION_ATTRIBUTE_GROUP, $attributeGroup->id);

        return $attributeGroup;
    }

    /**
     * @param array<string, array<string, string>> $languages
     * @param int $attributeGroupId
     *
     * @return void
     */
    private function createAttributes(array $languages, int $attributeGroupId): void
    {
        foreach (Config::getSubscriptionAttributeOptions() as $attributeName => $attributeConfigKey) {
            $existingAttribute = $this->productAttributeAdapter->getProductAttribute((int) $this->configuration->get($attributeConfigKey));
            if (Validate::isLoadedObject($existingAttribute)) {
                continue;
            }

            $attribute = $this->productAttributeAdapter->getProductAttribute();
            foreach ($languages as $language) {
                /* @phpstan-ignore-next-line */
                $attribute->name[$language['id_lang']] = $attributeName;
            }
            $attribute->id_attribute_group = $attributeGroupId;
            $attribute->add();
            $this->configuration->updateValue($attributeConfigKey, $attribute->id);
        }
    }
}
