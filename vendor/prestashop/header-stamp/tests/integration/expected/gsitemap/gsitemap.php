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
class Gsitemap extends \MolliePrefix\Module
{
    const HOOK_ADD_URLS = 'gSitemapAppendUrls';
    public $cron = \false;
    protected $sql_checks = array();
    public function __construct()
    {
        $this->name = 'gsitemap';
        $this->tab = 'seo';
        $this->version = '4.1.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;
        $this->bootstrap = \true;
        parent::__construct();
        $this->displayName = $this->trans('Google sitemap', array(), 'Modules.Gsitemap.Admin');
        $this->description = $this->trans('Generate your Google sitemap file', array(), 'Modules.Gsitemap.Admin');
        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => \_PS_VERSION_);
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', array(), 'Admin.Notifications.Warning');
        $this->type_array = array('home', 'meta', 'product', 'category', 'cms', 'module');
        $metas = \MolliePrefix\Db::getInstance()->ExecuteS('SELECT * FROM `' . \_DB_PREFIX_ . 'meta` ORDER BY `id_meta` ASC');
        $disabled_metas = \explode(',', \MolliePrefix\Configuration::get('GSITEMAP_DISABLE_LINKS'));
        foreach ($metas as $meta) {
            if (\in_array($meta['id_meta'], $disabled_metas)) {
                if (($key = \array_search($meta['page'], $this->type_array)) !== \false) {
                    unset($this->type_array[$key]);
                }
            }
        }
    }
    /**
     * Google sitemap installation process:
     *
     * Step 1 - Pre-set Configuration option values
     * Step 2 - Install the Addon and create a database table to store sitemap files name by shop
     *
     * @return bool Installation result
     */
    public function install()
    {
        foreach (array('GSITEMAP_PRIORITY_HOME' => 1.0, 'GSITEMAP_PRIORITY_PRODUCT' => 0.9, 'GSITEMAP_PRIORITY_CATEGORY' => 0.8, 'GSITEMAP_PRIORITY_CMS' => 0.7, 'GSITEMAP_FREQUENCY' => 'weekly', 'GSITEMAP_CHECK_IMAGE_FILE' => \false, 'GSITEMAP_LAST_EXPORT' => \false) as $key => $val) {
            if (!\MolliePrefix\Configuration::updateValue($key, $val)) {
                return \false;
            }
        }
        return parent::install() && \MolliePrefix\Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . \_DB_PREFIX_ . 'gsitemap_sitemap` (`link` varchar(255) DEFAULT NULL, `id_shop` int(11) DEFAULT 0) ENGINE=' . \_MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;') && $this->installHook();
    }
    /**
     * Registers hook(s)
     *
     * @return bool
     */
    protected function installHook()
    {
        $hook = new \MolliePrefix\Hook();
        $hook->name = self::HOOK_ADD_URLS;
        $hook->title = 'GSitemap Append URLs';
        $hook->description = 'This hook allows a module to add URLs to a generated sitemap';
        $hook->position = \true;
        return $hook->save();
    }
    /**
     * Google sitemap uninstallation process:
     *
     * Step 1 - Remove Configuration option values from database
     * Step 2 - Remove the database containing the generated sitemap files names
     * Step 3 - Uninstallation of the Addon itself
     *
     * @return bool Uninstallation result
     */
    public function uninstall()
    {
        foreach (array('GSITEMAP_PRIORITY_HOME' => '', 'GSITEMAP_PRIORITY_PRODUCT' => '', 'GSITEMAP_PRIORITY_CATEGORY' => '', 'GSITEMAP_PRIORITY_CMS' => '', 'GSITEMAP_FREQUENCY' => '', 'GSITEMAP_CHECK_IMAGE_FILE' => '', 'GSITEMAP_LAST_EXPORT' => '') as $key => $val) {
            if (!\MolliePrefix\Configuration::deleteByName($key)) {
                return \false;
            }
        }
        $hook = new \MolliePrefix\Hook(\MolliePrefix\Hook::getIdByName(self::HOOK_ADD_URLS));
        if (\MolliePrefix\Validate::isLoadedObject($hook)) {
            $hook->delete();
        }
        return parent::uninstall() && $this->removeSitemap();
    }
    /**
     * Delete all the generated sitemap files  and drop the addon table.
     *
     * @return bool
     */
    public function removeSitemap()
    {
        $links = \MolliePrefix\Db::getInstance()->ExecuteS('SELECT * FROM `' . \_DB_PREFIX_ . 'gsitemap_sitemap`');
        if ($links) {
            foreach ($links as $link) {
                if (!@\unlink($this->normalizeDirectory(\_PS_ROOT_DIR_) . $link['link'])) {
                    return \false;
                }
            }
        }
        if (!\MolliePrefix\Db::getInstance()->Execute('DROP TABLE `' . \_DB_PREFIX_ . 'gsitemap_sitemap`')) {
            return \false;
        }
        return \true;
    }
    public function getContent()
    {
        /* Store the posted parameters and generate a new Google sitemap files for the current Shop */
        if (\MolliePrefix\Tools::isSubmit('SubmitGsitemap')) {
            \MolliePrefix\Configuration::updateValue('GSITEMAP_FREQUENCY', \MolliePrefix\pSQL(\MolliePrefix\Tools::getValue('gsitemap_frequency')));
            \MolliePrefix\Configuration::updateValue('GSITEMAP_INDEX_CHECK', '');
            \MolliePrefix\Configuration::updateValue('GSITEMAP_CHECK_IMAGE_FILE', \MolliePrefix\pSQL(\MolliePrefix\Tools::getValue('gsitemap_check_image_file')));
            $meta = '';
            if (\MolliePrefix\Tools::getValue('gsitemap_meta')) {
                $meta .= \implode(', ', \MolliePrefix\Tools::getValue('gsitemap_meta'));
            }
            \MolliePrefix\Configuration::updateValue('GSITEMAP_DISABLE_LINKS', $meta);
            $this->emptySitemap();
            $this->createSitemap();
            /* If no posted form and the variable [continue] is found in the HTTP request variable keep creating sitemap */
        } elseif (\MolliePrefix\Tools::getValue('continue')) {
            $this->createSitemap();
        }
        /* Empty the Shop domain cache */
        if (\method_exists('ShopUrl', 'resetMainDomainCache')) {
            \MolliePrefix\ShopUrl::resetMainDomainCache();
        }
        /* Get Meta pages and remove index page it's managed elsewhere (@see $this->getHomeLink()) */
        $store_metas = \array_filter(\MolliePrefix\Meta::getMetasByIdLang((int) $this->context->cookie->id_lang), function ($meta) {
            return $meta['page'] != 'index';
        });
        $store_url = $this->context->link->getBaseLink();
        $this->context->smarty->assign(array('gsitemap_form' => './index.php?tab=AdminModules&configure=gsitemap&token=' . \MolliePrefix\Tools::getAdminTokenLite('AdminModules') . '&tab_module=' . $this->tab . '&module_name=gsitemap', 'gsitemap_cron' => $store_url . 'modules/gsitemap/gsitemap-cron.php?token=' . \MolliePrefix\Tools::substr(\MolliePrefix\Tools::encrypt('gsitemap/cron'), 0, 10) . '&id_shop=' . $this->context->shop->id, 'gsitemap_feed_exists' => \file_exists($this->normalizeDirectory(\_PS_ROOT_DIR_) . 'index_sitemap.xml'), 'gsitemap_last_export' => \MolliePrefix\Configuration::get('GSITEMAP_LAST_EXPORT'), 'gsitemap_frequency' => \MolliePrefix\Configuration::get('GSITEMAP_FREQUENCY'), 'gsitemap_store_url' => $store_url, 'gsitemap_links' => \MolliePrefix\Db::getInstance()->ExecuteS('SELECT * FROM `' . \_DB_PREFIX_ . 'gsitemap_sitemap` WHERE id_shop = ' . (int) $this->context->shop->id), 'store_metas' => $store_metas, 'gsitemap_disable_metas' => \explode(',', \MolliePrefix\Configuration::get('GSITEMAP_DISABLE_LINKS')), 'gsitemap_customer_limit' => array('max_exec_time' => (int) \ini_get('max_execution_time'), 'memory_limit' => (int) \ini_get('memory_limit')), 'prestashop_ssl' => \MolliePrefix\Configuration::get('PS_SSL_ENABLED'), 'gsitemap_check_image_file' => \MolliePrefix\Configuration::get('GSITEMAP_CHECK_IMAGE_FILE'), 'shop' => $this->context->shop));
        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }
    /**
     * Delete all the generated sitemap files from the files system and the database.
     *
     * @param int $id_shop
     *
     * @return bool
     */
    public function emptySitemap($id_shop = 0)
    {
        if (!isset($this->context)) {
            $this->context = new \MolliePrefix\Context();
        }
        if ($id_shop != 0) {
            $this->context->shop = new \MolliePrefix\Shop((int) $id_shop);
        }
        $links = \MolliePrefix\Db::getInstance()->ExecuteS('SELECT * FROM `' . \_DB_PREFIX_ . 'gsitemap_sitemap` WHERE id_shop = ' . (int) $this->context->shop->id);
        if ($links) {
            foreach ($links as $link) {
                @\unlink($this->normalizeDirectory(\_PS_ROOT_DIR_) . $link['link']);
            }
            return \MolliePrefix\Db::getInstance()->Execute('DELETE FROM `' . \_DB_PREFIX_ . 'gsitemap_sitemap` WHERE id_shop = ' . (int) $this->context->shop->id);
        }
        return \true;
    }
    /**
     * @param array $link_sitemap contain all the links for the Google sitemap file to be generated
     * @param array $new_link contain the link elements
     * @param string $lang language of link to add
     * @param int $index index of the current Google sitemap file
     * @param int $i count of elements added to sitemap main array
     * @param int $id_obj identifier of the object of the link to be added to the Gogle sitemap file
     *
     * @return bool
     */
    public function addLinkToSitemap(&$link_sitemap, $new_link, $lang, &$index, &$i, $id_obj)
    {
        if ($i <= 25000 && \memory_get_usage() < 100000000) {
            $link_sitemap[] = $new_link;
            ++$i;
            return \true;
        } else {
            $this->recursiveSitemapCreator($link_sitemap, $lang, $index);
            if ($index % 20 == 0 && !$this->cron) {
                $this->context->smarty->assign(array('gsitemap_number' => (int) $index, 'gsitemap_refresh_page' => $this->context->link->getAdminLink('AdminModules', \true, array(), array('tab_module' => $this->tab, 'module_name' => $this->name, 'continue' => 1, 'type' => $new_link['type'], 'lang' => $lang, 'index' => $index, 'id' => (int) $id_obj, 'id_shop' => $this->context->shop->id))));
                return \false;
            } elseif ($index % 20 == 0 && $this->cron) {
                \header('Refresh: 5; url=http' . (\MolliePrefix\Configuration::get('PS_SSL_ENABLED') ? 's' : '') . '://' . \MolliePrefix\Tools::getShopDomain(\false, \true) . \__PS_BASE_URI__ . 'modules/gsitemap/gsitemap-cron.php?continue=1&token=' . \MolliePrefix\Tools::substr(\MolliePrefix\Tools::encrypt('gsitemap/cron'), 0, 10) . '&type=' . $new_link['type'] . '&lang=' . $lang . '&index=' . $index . '&id=' . (int) $id_obj . '&id_shop=' . $this->context->shop->id);
                die;
            } else {
                if ($this->cron) {
                    \MolliePrefix\Tools::redirect($this->context->link->getBaseLink() . 'modules/gsitemap/gsitemap-cron.php?continue=1&token=' . \MolliePrefix\Tools::substr(\MolliePrefix\Tools::encrypt('gsitemap/cron'), 0, 10) . '&type=' . $new_link['type'] . '&lang=' . $lang . '&index=' . $index . '&id=' . (int) $id_obj . '&id_shop=' . $this->context->shop->id);
                } else {
                    \MolliePrefix\Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', \true, array(), array('tab_module' => $this->tab, 'module_name' => $this->name, 'continue' => 1, 'type' => $new_link['type'], 'lang' => $lang, 'index' => $index, 'id' => (int) $id_obj, 'id_shop' => $this->context->shop->id)));
                }
                die;
            }
        }
    }
    /**
     * Hydrate $link_sitemap with home link
     *
     * @param array $link_sitemap contain all the links for the Google sitemap file to be generated
     * @param string $lang language of link to add
     * @param int $index index of the current Google sitemap file
     * @param int $i count of elements added to sitemap main array
     *
     * @return bool
     */
    protected function getHomeLink(&$link_sitemap, $lang, &$index, &$i)
    {
        $link = new \MolliePrefix\Link();
        return $this->addLinkToSitemap($link_sitemap, array('type' => 'home', 'page' => 'home', 'link' => $link->getPageLink('index', null, $lang['id_lang']), 'image' => \false), $lang['iso_code'], $index, $i, -1);
    }
    /**
     * Hydrate $link_sitemap with meta link
     *
     * @param array $link_sitemap contain all the links for the Google sitemap file to be generated
     * @param string $lang language of link to add
     * @param int $index index of the current Google sitemap file
     * @param int $i count of elements added to sitemap main array
     * @param int $id_meta meta object identifier
     *
     * @return bool
     */
    protected function getMetaLink(&$link_sitemap, $lang, &$index, &$i, $id_meta = 0)
    {
        if (\method_exists('ShopUrl', 'resetMainDomainCache')) {
            \MolliePrefix\ShopUrl::resetMainDomainCache();
        }
        $link = new \MolliePrefix\Link();
        $metas = \MolliePrefix\Db::getInstance()->ExecuteS('SELECT * FROM `' . \_DB_PREFIX_ . 'meta` WHERE `configurable` > 0 AND `id_meta` >= ' . (int) $id_meta . ' AND page <> \'index\' ORDER BY `id_meta` ASC');
        foreach ($metas as $meta) {
            $url = '';
            if (!\in_array($meta['id_meta'], \explode(',', \MolliePrefix\Configuration::get('GSITEMAP_DISABLE_LINKS')))) {
                $url = $link->getPageLink($meta['page'], null, $lang['id_lang']);
                if (!$this->addLinkToSitemap($link_sitemap, array('type' => 'meta', 'page' => $meta['page'], 'link' => $url, 'image' => \false), $lang['iso_code'], $index, $i, $meta['id_meta'])) {
                    return \false;
                }
            }
        }
        return \true;
    }
    /**
     * Hydrate $link_sitemap with products link
     *
     * @param array $link_sitemap contain all the links for the Google sitemap file to be generated
     * @param string $lang language of link to add
     * @param int $index index of the current Google sitemap file
     * @param int $i count of elements added to sitemap main array
     * @param int $id_product product object identifier
     *
     * @return bool
     */
    protected function getProductLink(&$link_sitemap, $lang, &$index, &$i, $id_product = 0)
    {
        $link = new \MolliePrefix\Link();
        if (\method_exists('ShopUrl', 'resetMainDomainCache')) {
            \MolliePrefix\ShopUrl::resetMainDomainCache();
        }
        $products_id = \MolliePrefix\Db::getInstance()->ExecuteS('SELECT `id_product` FROM `' . \_DB_PREFIX_ . 'product_shop` WHERE `id_product` >= ' . (int) $id_product . ' AND `active` = 1 AND `visibility` != \'none\' AND `id_shop`=' . $this->context->shop->id . ' ORDER BY `id_product` ASC');
        foreach ($products_id as $product_id) {
            $product = new \MolliePrefix\Product((int) $product_id['id_product'], \false, (int) $lang['id_lang']);
            $url = $link->getProductLink($product, $product->link_rewrite, \htmlspecialchars(\strip_tags($product->category)), $product->ean13, (int) $lang['id_lang'], (int) $this->context->shop->id, 0);
            $id_image = \MolliePrefix\Product::getCover((int) $product_id['id_product']);
            if (isset($id_image['id_image'])) {
                $image_link = $this->context->link->getImageLink($product->link_rewrite, $product->id . '-' . (int) $id_image['id_image'], \MolliePrefix\ImageType::getFormattedName('large'));
                $image_link = !\in_array(\rtrim(\MolliePrefix\Context::getContext()->shop->virtual_uri, '/'), \explode('/', $image_link)) ? \str_replace(array('https', \MolliePrefix\Context::getContext()->shop->domain . \MolliePrefix\Context::getContext()->shop->physical_uri), array('http', \MolliePrefix\Context::getContext()->shop->domain . \MolliePrefix\Context::getContext()->shop->physical_uri . \MolliePrefix\Context::getContext()->shop->virtual_uri), $image_link) : $image_link;
            }
            $file_headers = \MolliePrefix\Configuration::get('GSITEMAP_CHECK_IMAGE_FILE') ? @\get_headers($image_link) : \true;
            $image_product = array();
            if (isset($image_link) && ($file_headers[0] != 'HTTP/1.1 404 Not Found' || $file_headers === \true)) {
                $image_product = array('title_img' => \htmlspecialchars(\strip_tags($product->name)), 'caption' => \htmlspecialchars(\strip_tags($product->description_short)), 'link' => $image_link);
            }
            if (!$this->addLinkToSitemap($link_sitemap, array('type' => 'product', 'page' => 'product', 'lastmod' => $product->date_upd, 'link' => $url, 'image' => $image_product), $lang['iso_code'], $index, $i, $product_id['id_product'])) {
                return \false;
            }
            unset($image_link);
        }
        return \true;
    }
    /**
     * Hydrate $link_sitemap with categories link
     *
     * @param array $link_sitemap contain all the links for the Google sitemap file to be generated
     * @param string $lang language of link to add
     * @param int $index index of the current Google sitemap file
     * @param int $i count of elements added to sitemap main array
     * @param int $id_category category object identifier
     *
     * @return bool
     */
    protected function getCategoryLink(&$link_sitemap, $lang, &$index, &$i, $id_category = 0)
    {
        $link = new \MolliePrefix\Link();
        if (\method_exists('ShopUrl', 'resetMainDomainCache')) {
            \MolliePrefix\ShopUrl::resetMainDomainCache();
        }
        $categories_id = \MolliePrefix\Db::getInstance()->ExecuteS('SELECT c.id_category FROM `' . \_DB_PREFIX_ . 'category` c
                INNER JOIN `' . \_DB_PREFIX_ . 'category_shop` cs ON c.`id_category` = cs.`id_category`
                WHERE c.`id_category` >= ' . (int) $id_category . ' AND c.`active` = 1 AND c.`id_category` != ' . (int) \MolliePrefix\Configuration::get('PS_ROOT_CATEGORY') . ' AND c.id_category != ' . (int) \MolliePrefix\Configuration::get('PS_HOME_CATEGORY') . ' AND c.id_parent > 0 AND c.`id_category` > 0 AND cs.`id_shop` = ' . (int) $this->context->shop->id . ' ORDER BY c.`id_category` ASC');
        foreach ($categories_id as $category_id) {
            $category = new \MolliePrefix\Category((int) $category_id['id_category'], (int) $lang['id_lang']);
            $url = $link->getCategoryLink($category, \urlencode($category->link_rewrite), (int) $lang['id_lang']);
            if ($category->id_image) {
                $image_link = $this->context->link->getCatImageLink($category->link_rewrite, (int) $category->id_image, \MolliePrefix\ImageType::getFormattedName('category'));
                $image_link = !\in_array(\rtrim(\MolliePrefix\Context::getContext()->shop->virtual_uri, '/'), \explode('/', $image_link)) ? \str_replace(array('https', \MolliePrefix\Context::getContext()->shop->domain . \MolliePrefix\Context::getContext()->shop->physical_uri), array('http', \MolliePrefix\Context::getContext()->shop->domain . \MolliePrefix\Context::getContext()->shop->physical_uri . \MolliePrefix\Context::getContext()->shop->virtual_uri), $image_link) : $image_link;
            }
            $file_headers = \MolliePrefix\Configuration::get('GSITEMAP_CHECK_IMAGE_FILE') ? @\get_headers($image_link) : \true;
            $image_category = array();
            if (isset($image_link) && ($file_headers[0] != 'HTTP/1.1 404 Not Found' || $file_headers === \true)) {
                $image_category = array('title_img' => \htmlspecialchars(\strip_tags($category->name)), 'caption' => \MolliePrefix\Tools::substr(\htmlspecialchars(\strip_tags($category->description)), 0, 350), 'link' => $image_link);
            }
            if (!$this->addLinkToSitemap($link_sitemap, array('type' => 'category', 'page' => 'category', 'lastmod' => $category->date_upd, 'link' => $url, 'image' => $image_category), $lang['iso_code'], $index, $i, (int) $category_id['id_category'])) {
                return \false;
            }
            unset($image_link);
        }
        return \true;
    }
    /**
     * return the link elements for the CMS object
     *
     * @param array $link_sitemap contain all the links for the Google sitemap file to be generated
     * @param string $lang the language of link to add
     * @param int $index the index of the current Google sitemap file
     * @param int $i the count of elements added to sitemap main array
     * @param int $id_cms the CMS object identifier
     *
     * @return bool
     */
    protected function getCmsLink(&$link_sitemap, $lang, &$index, &$i, $id_cms = 0)
    {
        $link = new \MolliePrefix\Link();
        if (\method_exists('ShopUrl', 'resetMainDomainCache')) {
            \MolliePrefix\ShopUrl::resetMainDomainCache();
        }
        $cmss_id = \MolliePrefix\Db::getInstance()->ExecuteS('SELECT c.`id_cms` FROM `' . \_DB_PREFIX_ . 'cms` c INNER JOIN `' . \_DB_PREFIX_ . 'cms_lang` cl ON c.`id_cms` = cl.`id_cms` ' . ($this->tableColumnExists(\_DB_PREFIX_ . 'supplier_shop') ? 'INNER JOIN `' . \_DB_PREFIX_ . 'cms_shop` cs ON c.`id_cms` = cs.`id_cms` ' : '') . 'INNER JOIN `' . \_DB_PREFIX_ . 'cms_category` cc ON c.id_cms_category = cc.id_cms_category AND cc.active = 1
            WHERE c.`active` =1 AND c.`indexation` =1 AND c.`id_cms` >= ' . (int) $id_cms . ($this->tableColumnExists(\_DB_PREFIX_ . 'supplier_shop') ? ' AND cs.id_shop = ' . (int) $this->context->shop->id : '') . ' AND cl.`id_lang` = ' . (int) $lang['id_lang'] . ' GROUP BY  c.`id_cms` ORDER BY c.`id_cms` ASC');
        if (\is_array($cmss_id)) {
            foreach ($cmss_id as $cms_id) {
                $cms = new \MolliePrefix\CMS((int) $cms_id['id_cms'], $lang['id_lang']);
                $cms->link_rewrite = \urlencode(\is_array($cms->link_rewrite) ? $cms->link_rewrite[(int) $lang['id_lang']] : $cms->link_rewrite);
                $url = $link->getCMSLink($cms, null, null, $lang['id_lang']);
                if (!$this->addLinkToSitemap($link_sitemap, array('type' => 'cms', 'page' => 'cms', 'link' => $url, 'image' => \false), $lang['iso_code'], $index, $i, $cms_id['id_cms'])) {
                    return \false;
                }
            }
        }
        return \true;
    }
    /**
     * Returns link elements generated by modules subscribes to hook gsitemap::HOOK_ADD_URLS
     *
     * The hook expects modules to return a vector of associative arrays each of them being acceptable by
     *   the gsitemap::_addLinkToSitemap() second attribute (minus the 'type' index).
     * The 'type' index is automatically set to 'module' (not sure here, should we be safe or trust modules?).
     *
     * @param array $link_sitemap by ref. accumulator for all the links for the Google sitemap file to be generated
     * @param string $lang the language being processed
     * @param int $index the index of the current Google sitemap file
     * @param int $i the count of elements added to sitemap main array
     * @param int $num_link restart at link number #$num_link
     *
     * @return bool
     */
    protected function getModuleLink(&$link_sitemap, $lang, &$index, &$i, $num_link = 0)
    {
        $modules_links = \MolliePrefix\Hook::exec(self::HOOK_ADD_URLS, array('lang' => $lang), null, \true);
        if (empty($modules_links) || !\is_array($modules_links)) {
            return \true;
        }
        $links = array();
        foreach ($modules_links as $module_links) {
            $links = \array_merge($links, $module_links);
        }
        foreach ($links as $n => $link) {
            if ($num_link > $n) {
                continue;
            }
            $link['type'] = 'module';
            if (!$this->addLinkToSitemap($link_sitemap, $link, $lang['iso_code'], $index, $i, $n)) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * Create the Google sitemap by Shop
     *
     * @param int $id_shop Shop identifier
     *
     * @return bool
     */
    public function createSitemap($id_shop = 0)
    {
        if (@\fopen($this->normalizeDirectory(\_PS_ROOT_DIR_) . '/test.txt', 'wb') == \false) {
            $this->context->smarty->assign('google_maps_error', $this->trans('An error occured while trying to check your file permissions. Please adjust your permissions to allow PrestaShop to write a file in your root directory.', array(), 'Modules.Gsitemap.Admin'));
            return \false;
        } else {
            @\unlink($this->normalizeDirectory(\_PS_ROOT_DIR_) . 'test.txt');
        }
        if ($id_shop != 0) {
            $this->context->shop = new \MolliePrefix\Shop((int) $id_shop);
        }
        $type = \MolliePrefix\Tools::getValue('type') ? \MolliePrefix\Tools::getValue('type') : '';
        $languages = \MolliePrefix\Language::getLanguages(\true, $this->context->shop->id);
        $lang_stop = \MolliePrefix\Tools::getValue('lang') ? \true : \false;
        $id_obj = \MolliePrefix\Tools::getValue('id') ? (int) \MolliePrefix\Tools::getValue('id') : 0;
        foreach ($languages as $lang) {
            $i = 0;
            $index = \MolliePrefix\Tools::getValue('index') && \MolliePrefix\Tools::getValue('lang') == $lang['iso_code'] ? (int) \MolliePrefix\Tools::getValue('index') : 0;
            if ($lang_stop && $lang['iso_code'] != \MolliePrefix\Tools::getValue('lang')) {
                continue;
            } elseif ($lang_stop && $lang['iso_code'] == \MolliePrefix\Tools::getValue('lang')) {
                $lang_stop = \false;
            }
            $link_sitemap = array();
            foreach ($this->type_array as $type_val) {
                if ($type == '' || $type == $type_val) {
                    $function = 'get' . \MolliePrefix\Tools::ucfirst($type_val) . 'Link';
                    if (!$this->{$function}($link_sitemap, $lang, $index, $i, $id_obj)) {
                        return \false;
                    }
                    $type = '';
                    $id_obj = 0;
                }
            }
            $this->recursiveSitemapCreator($link_sitemap, $lang['iso_code'], $index);
            $page = '';
            $index = 0;
        }
        $this->createIndexSitemap();
        \MolliePrefix\Configuration::updateValue('GSITEMAP_LAST_EXPORT', \date('r'));
        \MolliePrefix\Tools::file_get_contents('https://www.google.com/webmasters/sitemaps/ping?sitemap=' . \urlencode($this->context->link->getBaseLink() . $this->context->shop->physical_uri . $this->context->shop->virtual_uri . $this->context->shop->id));
        if ($this->cron) {
            die;
        }
        \MolliePrefix\Tools::redirectAdmin('index.php?tab=AdminModules&configure=gsitemap&token=' . \MolliePrefix\Tools::getAdminTokenLite('AdminModules') . '&tab_module=' . $this->tab . '&module_name=gsitemap&validation');
        die;
    }
    /**
     * Store the generated sitemap file to the database
     *
     * @param string $sitemap the name of the generated Google sitemap file
     *
     * @return bool
     */
    protected function saveSitemapLink($sitemap)
    {
        if ($sitemap) {
            return \MolliePrefix\Db::getInstance()->Execute('INSERT INTO `' . \_DB_PREFIX_ . 'gsitemap_sitemap` (`link`, id_shop) VALUES (\'' . \MolliePrefix\pSQL($sitemap) . '\', ' . (int) $this->context->shop->id . ')');
        }
        return \false;
    }
    /**
     * @param array $link_sitemap contain all the links for the Google sitemap file to be generated
     * @param string $lang the language of link to add
     * @param int $index the index of the current Google sitemap file
     *
     * @return bool
     */
    protected function recursiveSitemapCreator($link_sitemap, $lang, &$index)
    {
        if (!\count($link_sitemap)) {
            return \false;
        }
        $sitemap_link = $this->context->shop->id . '_' . $lang . '_' . $index . '_sitemap.xml';
        $write_fd = \fopen($this->normalizeDirectory(\_PS_ROOT_DIR_) . $sitemap_link, 'wb');
        \fwrite($write_fd, '<?xml version="1.0" encoding="UTF-8"?>' . \PHP_EOL . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . \PHP_EOL);
        foreach ($link_sitemap as $key => $file) {
            \fwrite($write_fd, '<url>' . \PHP_EOL);
            $lastmod = isset($file['lastmod']) && !empty($file['lastmod']) ? \date('c', \strtotime($file['lastmod'])) : null;
            $this->addSitemapNode($write_fd, \htmlspecialchars(\strip_tags($file['link'])), $this->getPriorityPage($file['page']), \MolliePrefix\Configuration::get('GSITEMAP_FREQUENCY'), $lastmod);
            if ($file['image']) {
                $this->addSitemapNodeImage($write_fd, \htmlspecialchars(\strip_tags($file['image']['link'])), isset($file['image']['title_img']) ? \htmlspecialchars(\str_replace(array("\r\n", "\r", "\n"), '', $this->removeControlCharacters(\strip_tags($file['image']['title_img'])))) : '', isset($file['image']['caption']) ? \htmlspecialchars(\str_replace(array("\r\n", "\r", "\n"), '', \strip_tags($file['image']['caption']))) : '');
            }
            \fwrite($write_fd, '</url>' . \PHP_EOL);
        }
        \fwrite($write_fd, '</urlset>' . \PHP_EOL);
        \fclose($write_fd);
        $this->saveSitemapLink($sitemap_link);
        ++$index;
        return \true;
    }
    /**
     * return the priority value set in the configuration parameters
     *
     * @param string $page
     *
     * @return float|string|bool
     */
    protected function getPriorityPage($page)
    {
        return \MolliePrefix\Configuration::get('GSITEMAP_PRIORITY_' . \MolliePrefix\Tools::strtoupper($page)) ? \MolliePrefix\Configuration::get('GSITEMAP_PRIORITY_' . \MolliePrefix\Tools::strtoupper($page)) : 0.1;
    }
    /**
     * Add a new line to the sitemap file
     *
     * @param resource $fd file system object resource
     * @param string $loc string the URL of the object page
     * @param string $priority
     * @param string $change_freq
     * @param int $last_mod the last modification date/time as a timestamp
     */
    protected function addSitemapNode($fd, $loc, $priority, $change_freq, $last_mod = null)
    {
        \fwrite($fd, '<loc>' . (\MolliePrefix\Configuration::get('PS_REWRITING_SETTINGS') ? '<![CDATA[' . $loc . ']]>' : $loc) . '</loc>' . \PHP_EOL . ($last_mod ? '<lastmod>' . \date('c', \strtotime($last_mod)) . '</lastmod>' : '') . \PHP_EOL . '<changefreq>' . $change_freq . '</changefreq>' . \PHP_EOL . '<priority>' . \number_format($priority, 1, '.', '') . '</priority>' . \PHP_EOL);
    }
    protected function addSitemapNodeImage($fd, $link, $title, $caption)
    {
        \fwrite($fd, '<image:image>' . \PHP_EOL . '<image:loc>' . (\MolliePrefix\Configuration::get('PS_REWRITING_SETTINGS') ? '<![CDATA[' . $link . ']]>' : $link) . '</image:loc>' . \PHP_EOL . '<image:caption><![CDATA[' . $caption . ']]></image:caption>' . \PHP_EOL . '<image:title><![CDATA[' . $title . ']]></image:title>' . \PHP_EOL . '</image:image>' . \PHP_EOL);
    }
    /**
     * Create the index file for all generated sitemaps
     *
     * @return bool
     */
    protected function createIndexSitemap()
    {
        $sitemaps = \MolliePrefix\Db::getInstance()->ExecuteS('SELECT `link` FROM `' . \_DB_PREFIX_ . 'gsitemap_sitemap` WHERE id_shop = ' . $this->context->shop->id);
        if (!$sitemaps) {
            return \false;
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>';
        $xml_feed = new \SimpleXMLElement($xml);
        foreach ($sitemaps as $link) {
            $sitemap = $xml_feed->addChild('sitemap');
            $sitemap->addChild('loc', $this->context->link->getBaseLink() . $link['link']);
            $sitemap->addChild('lastmod', \date('c'));
        }
        \file_put_contents($this->normalizeDirectory(\_PS_ROOT_DIR_) . $this->context->shop->id . '_index_sitemap.xml', $xml_feed->asXML());
        return \true;
    }
    protected function tableColumnExists($table_name, $column = null)
    {
        if (\array_key_exists($table_name, $this->sql_checks)) {
            if (!empty($column) && \array_key_exists($column, $this->sql_checks[$table_name])) {
                return $this->sql_checks[$table_name][$column];
            } else {
                return $this->sql_checks[$table_name];
            }
        }
        $table = \MolliePrefix\Db::getInstance()->ExecuteS('SHOW TABLES LIKE \'' . $table_name . '\'');
        if (empty($column)) {
            if (\count($table) < 1) {
                return $this->sql_checks[$table_name] = \false;
            } else {
                $this->sql_checks[$table_name] = \true;
            }
        } else {
            $table = \MolliePrefix\Db::getInstance()->ExecuteS('SELECT * FROM `' . $table_name . '` LIMIT 1');
            return $this->sql_checks[$table_name][$column] = \array_key_exists($column, \current($table));
        }
        return \true;
    }
    protected function normalizeDirectory($directory)
    {
        $last = $directory[\MolliePrefix\Tools::strlen($directory) - 1];
        if (\in_array($last, array('/', '\\'))) {
            $directory[\MolliePrefix\Tools::strlen($directory) - 1] = \DIRECTORY_SEPARATOR;
            return $directory;
        }
        $directory .= \DIRECTORY_SEPARATOR;
        return $directory;
    }
    protected function removeControlCharacters($text)
    {
        $text = (string) \preg_replace('/[^\\x{0009}\\x{000a}\\x{000d}\\x{0020}-\\x{D7FF}\\x{E000}-\\x{FFFD}]+/u', ' ', $text);
        $text = (string) \preg_replace('!\\s+!', ' ', $text);
        return $text;
    }
}
\class_alias('MolliePrefix\\Gsitemap', 'Gsitemap', \false);
