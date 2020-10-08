<?php

namespace MolliePrefix;

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!\defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_2_2($object, $install = \false)
{
    if ($object->active || $install) {
        \MolliePrefix\Configuration::updateValue('GSITEMAP_PRIORITY_HOME', 1.0);
        \MolliePrefix\Configuration::updateValue('GSITEMAP_PRIORITY_PRODUCT', 0.9);
        \MolliePrefix\Configuration::updateValue('GSITEMAP_PRIORITY_CATEGORY', 0.8);
        \MolliePrefix\Configuration::updateValue('GSITEMAP_PRIORITY_CMS', 0.7);
        \MolliePrefix\Configuration::updateValue('GSITEMAP_FREQUENCY', 'weekly');
        \MolliePrefix\Configuration::updateValue('GSITEMAP_LAST_EXPORT', \false);
        return \MolliePrefix\Db::getInstance()->Execute('DROP TABLE IF  EXISTS `' . \_DB_PREFIX_ . 'gsitemap_sitemap`') && \MolliePrefix\Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . \_DB_PREFIX_ . 'gsitemap_sitemap` (`link` varchar(255) DEFAULT NULL, `id_shop` int(11) DEFAULT 0) ENGINE=' . \_MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;');
    }
    $object->upgrade_detail['2.2'][] = 'GSitemap upgrade error !';
    return \false;
}
