<?php

namespace MolliePrefix;

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!\defined('_PS_VERSION_')) {
    exit;
}
class dashproducts extends \MolliePrefix\Module
{
    public function __construct()
    {
        $this->name = 'dashproducts';
        $this->tab = 'dashboard';
        $this->version = '2.0.4';
        $this->author = 'PrestaShop';
        $this->push_filename = \_PS_CACHE_DIR_ . 'push/activity';
        $this->allow_push = \true;
        parent::__construct();
        $this->displayName = $this->trans('Dashboard Products', array(), 'Modules.Dashproducts.Admin');
        $this->description = $this->trans('Adds a block with a table of your latest orders and a ranking of your products', array(), 'Modules.Dashproducts.Admin');
        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => \_PS_VERSION_);
    }
    public function install()
    {
        \MolliePrefix\Configuration::updateValue('DASHPRODUCT_NBR_SHOW_LAST_ORDER', 10);
        \MolliePrefix\Configuration::updateValue('DASHPRODUCT_NBR_SHOW_BEST_SELLER', 10);
        \MolliePrefix\Configuration::updateValue('DASHPRODUCT_NBR_SHOW_MOST_VIEWED', 10);
        \MolliePrefix\Configuration::updateValue('DASHPRODUCT_NBR_SHOW_TOP_SEARCH', 10);
        return parent::install() && $this->registerHook('dashboardZoneTwo') && $this->registerHook('dashboardData') && $this->registerHook('actionObjectOrderAddAfter') && $this->registerHook('actionSearch');
    }
    public function hookDashboardZoneTwo($params)
    {
        $this->context->smarty->assign(array('DASHACTIVITY_CART_ACTIVE' => \MolliePrefix\Configuration::get('DASHACTIVITY_CART_ACTIVE'), 'DASHACTIVITY_VISITOR_ONLINE' => \MolliePrefix\Configuration::get('DASHACTIVITY_VISITOR_ONLINE'), 'DASHPRODUCT_NBR_SHOW_LAST_ORDER' => \MolliePrefix\Configuration::get('DASHPRODUCT_NBR_SHOW_LAST_ORDER'), 'DASHPRODUCT_NBR_SHOW_BEST_SELLER' => \MolliePrefix\Configuration::get('DASHPRODUCT_NBR_SHOW_BEST_SELLER'), 'DASHPRODUCT_NBR_SHOW_TOP_SEARCH' => \MolliePrefix\Configuration::get('DASHPRODUCT_NBR_SHOW_TOP_SEARCH'), 'date_from' => \MolliePrefix\Tools::displayDate($params['date_from']), 'date_to' => \MolliePrefix\Tools::displayDate($params['date_to']), 'dashproducts_config_form' => $this->renderConfigForm()));
        return $this->display(__FILE__, 'dashboard_zone_two.tpl');
    }
    public function hookDashboardData($params)
    {
        $table_recent_orders = $this->getTableRecentOrders();
        $table_best_sellers = $this->getTableBestSellers($params['date_from'], $params['date_to']);
        $table_most_viewed = $this->getTableMostViewed($params['date_from'], $params['date_to']);
        $table_top_10_most_search = $this->getTableTop10MostSearch($params['date_from'], $params['date_to']);
        //$table_top_5_search = $this->getTableTop5Search();
        return array('data_table' => array('table_recent_orders' => $table_recent_orders, 'table_best_sellers' => $table_best_sellers, 'table_most_viewed' => $table_most_viewed, 'table_top_10_most_search' => $table_top_10_most_search));
    }
    public function getTableRecentOrders()
    {
        $header = array(array('title' => $this->trans('Customer Name', array(), 'Modules.Dashproducts.Admin'), 'class' => 'text-left'), array('title' => $this->trans('Products', array(), 'Admin.Global'), 'class' => 'text-center'), array('title' => $this->trans('Total', array(), 'Admin.Global') . ' ' . $this->trans('Tax excl.', array(), 'Admin.Global'), 'class' => 'text-center'), array('title' => $this->trans('Date', array(), 'Admin.Global'), 'class' => 'text-center'), array('title' => $this->trans('Status', array(), 'Admin.Global'), 'class' => 'text-center'), array('title' => '', 'class' => 'text-right'));
        $limit = (int) \MolliePrefix\Configuration::get('DASHPRODUCT_NBR_SHOW_LAST_ORDER') ? (int) \MolliePrefix\Configuration::get('DASHPRODUCT_NBR_SHOW_LAST_ORDER') : 10;
        $orders = \MolliePrefix\Order::getOrdersWithInformations($limit);
        $body = array();
        foreach ($orders as $order) {
            $currency = \MolliePrefix\Currency::getCurrency((int) $order['id_currency']);
            $tr = array();
            $tr[] = array('id' => 'firstname_lastname', 'value' => '<a href="' . $this->context->link->getAdminLink('AdminCustomers', \true) . '&id_customer=' . $order['id_customer'] . '&viewcustomer">' . \MolliePrefix\Tools::htmlentitiesUTF8($order['firstname']) . ' ' . \MolliePrefix\Tools::htmlentitiesUTF8($order['lastname']) . '</a>', 'class' => 'text-left');
            $tr[] = array('id' => 'total_products', 'value' => \count(\MolliePrefix\OrderDetail::getList((int) $order['id_order'])), 'class' => 'text-center');
            $tr[] = array('id' => 'total_paid', 'value' => \MolliePrefix\Tools::displayPrice((float) $order['total_paid_tax_excl'], $currency), 'class' => 'text-center', 'wrapper_start' => $order['valid'] ? '<span class="badge badge-success">' : '', 'wrapper_end' => '<span>');
            $tr[] = array('id' => 'date_add', 'value' => \MolliePrefix\Tools::displayDate($order['date_add']), 'class' => 'text-center');
            $tr[] = array('id' => 'status', 'value' => \MolliePrefix\Tools::htmlentitiesUTF8($order['state_name']), 'class' => 'text-center');
            $tr[] = array('id' => 'details', 'value' => '', 'class' => 'text-right', 'wrapper_start' => '<a class="btn btn-default" href="index.php?tab=AdminOrders&id_order=' . (int) $order['id_order'] . '&vieworder&token=' . \MolliePrefix\Tools::getAdminTokenLite('AdminOrders') . '" title="' . $this->trans('Details', array(), 'Modules.Dashproducts.Admin') . '"><i class="icon-search"></i>', 'wrapper_end' => '</a>');
            $body[] = $tr;
        }
        return array('header' => $header, 'body' => $body);
    }
    public function getTableBestSellers($date_from, $date_to)
    {
        $header = array(array('id' => 'image', 'title' => $this->trans('Image', array(), 'Admin.Global'), 'class' => 'text-center'), array('id' => 'product', 'title' => $this->trans('Product', array(), 'Admin.Global'), 'class' => 'text-center'), array('id' => 'category', 'title' => $this->trans('Category', array(), 'Admin.Catalog.Feature'), 'class' => 'text-center'), array('id' => 'total_sold', 'title' => $this->trans('Total sold', array(), 'Modules.Dashproducts.Admin'), 'class' => 'text-center'), array('id' => 'sales', 'title' => $this->trans('Sales', array(), 'Admin.Global'), 'class' => 'text-center'), array('id' => 'net_profit', 'title' => $this->trans('Net profit', array(), 'Modules.Dashproducts.Admin'), 'class' => 'text-center'));
        $products = \MolliePrefix\Db::getInstance()->ExecuteS('
					SELECT
						product_id,
						product_name,
						SUM(product_quantity-product_quantity_refunded-product_quantity_return-product_quantity_reinjected) as total,
						p.price as price,
						pa.price as price_attribute,
						SUM(total_price_tax_excl / conversion_rate) as sales,
						SUM(product_quantity * purchase_supplier_price / conversion_rate) as expenses
					FROM `' . \_DB_PREFIX_ . 'orders` o
		LEFT JOIN `' . \_DB_PREFIX_ . 'order_detail` od ON o.id_order = od.id_order
		LEFT JOIN `' . \_DB_PREFIX_ . 'product` p ON p.id_product = product_id
		LEFT JOIN `' . \_DB_PREFIX_ . 'product_attribute` pa ON pa.id_product_attribute = od.product_attribute_id
		WHERE `invoice_date` BETWEEN "' . \MolliePrefix\pSQL($date_from) . ' 00:00:00" AND "' . \MolliePrefix\pSQL($date_to) . ' 23:59:59"
		AND valid = 1
		' . \MolliePrefix\Shop::addSqlRestriction(\false, 'o') . '
		GROUP BY product_id, product_attribute_id
		ORDER BY total DESC
		LIMIT ' . (int) \MolliePrefix\Configuration::get('DASHPRODUCT_NBR_SHOW_BEST_SELLER'));
        $body = array();
        foreach ($products as $product) {
            $product_obj = new \MolliePrefix\Product((int) $product['product_id'], \false, $this->context->language->id);
            if (!\MolliePrefix\Validate::isLoadedObject($product_obj)) {
                continue;
            }
            $category = new \MolliePrefix\Category($product_obj->getDefaultCategory(), $this->context->language->id);
            $img = '';
            if (($row_image = \MolliePrefix\Product::getCover($product_obj->id)) && $row_image['id_image']) {
                $image = new \MolliePrefix\Image($row_image['id_image']);
                $path_to_image = \_PS_PROD_IMG_DIR_ . $image->getExistingImgPath() . '.' . $this->context->controller->imageType;
                $img = \MolliePrefix\ImageManager::thumbnail($path_to_image, 'product_mini_' . $row_image['id_image'] . '.' . $this->context->controller->imageType, 45, $this->context->controller->imageType);
            }
            $productPrice = $product['price'];
            if (isset($product['price_attribute']) && $product['price_attribute'] != '0.000000') {
                $productPrice = $product['price_attribute'];
            }
            $body[] = array(array('id' => 'product', 'value' => $img, 'class' => 'text-center'), array('id' => 'product', 'value' => '<a href="' . $this->context->link->getAdminLink('AdminProducts', \true) . '&id_product=' . $product_obj->id . '&updateproduct">' . \MolliePrefix\Tools::htmlentitiesUTF8($product['product_name']) . '</a>' . '<br/>' . \MolliePrefix\Tools::displayPrice($productPrice), 'class' => 'text-center'), array('id' => 'category', 'value' => $category->name, 'class' => 'text-center'), array('id' => 'total_sold', 'value' => $product['total'], 'class' => 'text-center'), array('id' => 'sales', 'value' => \MolliePrefix\Tools::displayPrice($product['sales']), 'class' => 'text-center'), array('id' => 'net_profit', 'value' => \MolliePrefix\Tools::displayPrice($product['sales'] - $product['expenses']), 'class' => 'text-center'));
        }
        return array('header' => $header, 'body' => $body);
    }
    public function getTableMostViewed($date_from, $date_to)
    {
        $header = array(array('id' => 'image', 'title' => $this->trans('Image', array(), 'Admin.Global'), 'class' => 'text-center'), array('id' => 'product', 'title' => $this->trans('Product', array(), 'Admin.Global'), 'class' => 'text-center'), array('id' => 'views', 'title' => $this->trans('Views', array(), 'Modules.Dashproducts.Admin'), 'class' => 'text-center'), array('id' => 'added_to_cart', 'title' => $this->trans('Added to cart', array(), 'Modules.Dashproducts.Admin'), 'class' => 'text-center'), array('id' => 'purchased', 'title' => $this->trans('Purchased', array(), 'Modules.Dashproducts.Admin'), 'class' => 'text-center'), array('id' => 'rate', 'title' => $this->trans('Percentage', array(), 'Admin.Global'), 'class' => 'text-center'));
        if (\MolliePrefix\Configuration::get('PS_STATSDATA_PAGESVIEWS')) {
            $products = $this->getTotalViewed($date_from, $date_to, (int) \MolliePrefix\Configuration::get('DASHPRODUCT_NBR_SHOW_MOST_VIEWED'));
            $body = array();
            if (\is_array($products) && \count($products)) {
                foreach ($products as $product) {
                    $product_obj = new \MolliePrefix\Product((int) $product['id_object'], \true, $this->context->language->id);
                    if (!\MolliePrefix\Validate::isLoadedObject($product_obj)) {
                        continue;
                    }
                    $img = '';
                    if (($row_image = \MolliePrefix\Product::getCover($product_obj->id)) && $row_image['id_image']) {
                        $image = new \MolliePrefix\Image($row_image['id_image']);
                        $path_to_image = \_PS_PROD_IMG_DIR_ . $image->getExistingImgPath() . '.' . $this->context->controller->imageType;
                        $img = \MolliePrefix\ImageManager::thumbnail($path_to_image, 'product_mini_' . $product_obj->id . '.' . $this->context->controller->imageType, 45, $this->context->controller->imageType);
                    }
                    $tr = array();
                    $tr[] = array('id' => 'product', 'value' => $img, 'class' => 'text-center');
                    $tr[] = array('id' => 'product', 'value' => \MolliePrefix\Tools::htmlentitiesUTF8($product_obj->name) . '<br/>' . \MolliePrefix\Tools::displayPrice(\MolliePrefix\Product::getPriceStatic((int) $product_obj->id)), 'class' => 'text-center');
                    $tr[] = array('id' => 'views', 'value' => $product['counter'], 'class' => 'text-center');
                    $added_cart = $this->getTotalProductAddedCart($date_from, $date_to, (int) $product_obj->id);
                    $tr[] = array('id' => 'added_to_cart', 'value' => $added_cart, 'class' => 'text-center');
                    $purchased = $this->getTotalProductPurchased($date_from, $date_to, (int) $product_obj->id);
                    $tr[] = array('id' => 'purchased', 'value' => $this->getTotalProductPurchased($date_from, $date_to, (int) $product_obj->id), 'class' => 'text-center');
                    $tr[] = array('id' => 'rate', 'value' => $product['counter'] ? \round(100 * $purchased / $product['counter'], 1) . '%' : '-', 'class' => 'text-center');
                    $body[] = $tr;
                }
            }
        } else {
            $body = '<div class="alert alert-info">' . $this->trans('You must enable the "Save global page views" option from the "Data mining for statistics" module in order to display the most viewed products, or use the Google Analytics module.', array(), 'Modules.Dashproducts.Admin') . '</div>';
        }
        return array('header' => $header, 'body' => $body);
    }
    public function getTableTop10MostSearch($date_from, $date_to)
    {
        $header = array(array('id' => 'reference', 'title' => $this->trans('Term', array(), 'Modules.Dashproducts.Admin'), 'class' => 'text-left'), array('id' => 'name', 'title' => $this->trans('Search', array(), 'Admin.Shopparameters.Feature'), 'class' => 'text-center'), array('id' => 'totalQuantitySold', 'title' => $this->trans('Results', array(), 'Modules.Dashproducts.Admin'), 'class' => 'text-center'));
        $terms = $this->getMostSearchTerms($date_from, $date_to, (int) \MolliePrefix\Configuration::get('DASHPRODUCT_NBR_SHOW_TOP_SEARCH'));
        $body = array();
        if (\is_array($terms) && \count($terms)) {
            foreach ($terms as $term) {
                $tr = array();
                $tr[] = array('id' => 'product', 'value' => $term['keywords'], 'class' => 'text-left');
                $tr[] = array('id' => 'product', 'value' => $term['count_keywords'], 'class' => 'text-center');
                $tr[] = array('id' => 'product', 'value' => $term['results'], 'class' => 'text-center');
                $body[] = $tr;
            }
        }
        return array('header' => $header, 'body' => $body);
    }
    public function getTableTop5Search()
    {
        $header = array(array('id' => 'reference', 'title' => $this->trans('Product', array(), 'Admin.Global')));
        $body = array();
        return array('header' => $header, 'body' => $body);
    }
    public function getTotalProductSales($date_from, $date_to, $id_product)
    {
        $sql = 'SELECT SUM(od.`product_quantity` * od.`product_price`) AS total
				FROM `' . \_DB_PREFIX_ . 'order_detail` od
				JOIN `' . \_DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
				WHERE od.`product_id` = ' . (int) $id_product . '
					' . \MolliePrefix\Shop::addSqlRestriction(\MolliePrefix\Shop::SHARE_ORDER, 'o') . '
					AND o.valid = 1
					AND o.`date_add` BETWEEN "' . \MolliePrefix\pSQL($date_from) . '" AND "' . \MolliePrefix\pSQL($date_to) . '"';
        return (int) \MolliePrefix\Db::getInstance(\_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
    public function getTotalProductAddedCart($date_from, $date_to, $id_product)
    {
        return \MolliePrefix\Db::getInstance(\_PS_USE_SQL_SLAVE_)->getValue('
		SELECT count(`id_product`) as count
		FROM `' . \_DB_PREFIX_ . 'cart_product` cp
		WHERE cp.`id_product` = ' . (int) $id_product . '
		' . \MolliePrefix\Shop::addSqlRestriction(\false, 'cp') . '
		AND cp.`date_add` BETWEEN "' . \MolliePrefix\pSQL($date_from) . '" AND "' . \MolliePrefix\pSQL($date_to) . '"');
    }
    public function getTotalProductPurchased($date_from, $date_to, $id_product)
    {
        return \MolliePrefix\Db::getInstance(\_PS_USE_SQL_SLAVE_)->getValue('
		SELECT count(`product_id`) as count
		FROM `' . \_DB_PREFIX_ . 'order_detail` od
		JOIN `' . \_DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
		WHERE od.`product_id` = ' . (int) $id_product . '
		' . \MolliePrefix\Shop::addSqlRestriction(\false, 'od') . '
		AND o.valid = 1
		AND o.`date_add` BETWEEN "' . \MolliePrefix\pSQL($date_from) . '" AND "' . \MolliePrefix\pSQL($date_to) . '"');
    }
    public function getTotalViewed($date_from, $date_to, $limit = 10)
    {
        $gapi = \MolliePrefix\Module::isInstalled('gapi') ? \MolliePrefix\Module::getInstanceByName('gapi') : \false;
        if (\MolliePrefix\Validate::isLoadedObject($gapi) && $gapi->isConfigured()) {
            $products = array();
            // Only works with the default product URL pattern at this time
            $result = $gapi->requestReportData('ga:pagePath', 'ga:visits', $date_from, $date_to, '-ga:visits', 'ga:pagePath=~/([a-z]{2}/)?([a-z]+/)?[0-9][0-9]*\\-.*\\.html$', 1, 10);
            if ($result) {
                foreach ($result as $row) {
                    if (\preg_match('@/([a-z]{2}/)?([a-z]+/)?([0-9]+)\\-.*\\.html$@', $row['dimensions']['pagePath'], $matches)) {
                        $products[] = array('id_object' => (int) $matches[3], 'counter' => $row['metrics']['visits']);
                    }
                }
            }
            return $products;
        } else {
            return \MolliePrefix\Db::getInstance(\_PS_USE_SQL_SLAVE_)->executeS('
			SELECT p.id_object, pv.counter
			FROM `' . \_DB_PREFIX_ . 'page_viewed` pv
			LEFT JOIN `' . \_DB_PREFIX_ . 'date_range` dr ON pv.`id_date_range` = dr.`id_date_range`
			LEFT JOIN `' . \_DB_PREFIX_ . 'page` p ON pv.`id_page` = p.`id_page`
			LEFT JOIN `' . \_DB_PREFIX_ . 'page_type` pt ON pt.`id_page_type` = p.`id_page_type`
			WHERE pt.`name` = \'product\'
			' . \MolliePrefix\Shop::addSqlRestriction(\false, 'pv') . '
			AND dr.`time_start` BETWEEN "' . \MolliePrefix\pSQL($date_from) . '" AND "' . \MolliePrefix\pSQL($date_to) . '"
			AND dr.`time_end` BETWEEN "' . \MolliePrefix\pSQL($date_from) . '" AND "' . \MolliePrefix\pSQL($date_to) . '"
			ORDER BY pv.counter DESC
			LIMIT ' . (int) $limit);
        }
    }
    public function getMostSearchTerms($date_from, $date_to, $limit = 10)
    {
        if (!\MolliePrefix\Module::isInstalled('statssearch')) {
            return array();
        }
        return \MolliePrefix\Db::getInstance(\_PS_USE_SQL_SLAVE_)->executeS('
		SELECT `keywords`, count(`id_statssearch`) as count_keywords, `results`
		FROM `' . \_DB_PREFIX_ . 'statssearch` ss
		WHERE ss.`date_add` BETWEEN "' . \MolliePrefix\pSQL($date_from) . '" AND "' . \MolliePrefix\pSQL($date_to) . '"
		' . \MolliePrefix\Shop::addSqlRestriction(\false, 'ss') . '
		GROUP BY ss.`keywords`
		ORDER BY `count_keywords` DESC
		LIMIT ' . (int) $limit);
    }
    public function renderConfigForm()
    {
        $fields_form = array('form' => array('input' => array(), 'submit' => array('title' => $this->trans('Save', array(), 'Admin.Actions'), 'class' => 'btn btn-default pull-right submit_dash_config', 'reset' => array('title' => $this->trans('Cancel', array(), 'Admin.Actions'), 'class' => 'btn btn-default cancel_dash_config'))));
        $inputs = array(array('label' => $this->trans('Number of "Recent Orders" to display', array(), 'Modules.Dashproducts.Admin'), 'config_name' => 'DASHPRODUCT_NBR_SHOW_LAST_ORDER'), array('label' => $this->trans('Number of "Best Sellers" to display', array(), 'Modules.Dashproducts.Admin'), 'config_name' => 'DASHPRODUCT_NBR_SHOW_BEST_SELLER'), array('label' => $this->trans('Number of "Most Viewed" to display', array(), 'Modules.Dashproducts.Admin'), 'config_name' => 'DASHPRODUCT_NBR_SHOW_MOST_VIEWED'), array('label' => $this->trans('Number of "Top Searches" to display', array(), 'Modules.Dashproducts.Admin'), 'config_name' => 'DASHPRODUCT_NBR_SHOW_TOP_SEARCH'));
        foreach ($inputs as $input) {
            $fields_form['form']['input'][] = array('type' => 'select', 'label' => $input['label'], 'name' => $input['config_name'], 'options' => array('query' => array(array('id' => 5, 'name' => 5), array('id' => 10, 'name' => 10), array('id' => 20, 'name' => 20), array('id' => 50, 'name' => 50)), 'id' => 'id', 'name' => 'name'));
        }
        $helper = new \MolliePrefix\HelperForm();
        $helper->show_toolbar = \false;
        $helper->table = $this->table;
        $lang = new \MolliePrefix\Language((int) \MolliePrefix\Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = \MolliePrefix\Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? \MolliePrefix\Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int) \MolliePrefix\Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitDashConfig';
        $helper->tpl_vars = array('fields_value' => $this->getConfigFieldsValues(), 'languages' => $this->context->controller->getLanguages(), 'id_language' => $this->context->language->id);
        return $helper->generateForm(array($fields_form));
    }
    public function getConfigFieldsValues()
    {
        return array('DASHPRODUCT_NBR_SHOW_LAST_ORDER' => \MolliePrefix\Configuration::get('DASHPRODUCT_NBR_SHOW_LAST_ORDER'), 'DASHPRODUCT_NBR_SHOW_BEST_SELLER' => \MolliePrefix\Configuration::get('DASHPRODUCT_NBR_SHOW_BEST_SELLER'), 'DASHPRODUCT_NBR_SHOW_MOST_VIEWED' => \MolliePrefix\Configuration::get('DASHPRODUCT_NBR_SHOW_MOST_VIEWED'), 'DASHPRODUCT_NBR_SHOW_TOP_SEARCH' => \MolliePrefix\Configuration::get('DASHPRODUCT_NBR_SHOW_TOP_SEARCH'));
    }
    public function hookActionObjectOrderAddAfter($params)
    {
        \MolliePrefix\Tools::changeFileMTime($this->push_filename);
    }
    public function hookActionSearch($params)
    {
        \MolliePrefix\Tools::changeFileMTime($this->push_filename);
    }
}
\class_alias('MolliePrefix\\dashproducts', 'dashproducts', \false);
