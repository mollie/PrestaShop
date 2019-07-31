<?php
/**
 * Copyright (c) 2012-2019, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

class Configuration extends ObjectModel
{
    public $id;

    /** @var string Key */
    public $name;

    public $id_shop_group;
    public $id_shop;

    /** @var string Value */
    public $value;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'configuration',
        'primary' => 'id_configuration',
        'multilang' => true,
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isConfigName', 'required' => true, 'size' => 254),
            'id_shop_group' => array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'),
            'id_shop' => array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'),
            'value' => array('type' => self::TYPE_STRING),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );
    protected static $_cache = null;
    protected static $_new_cache_shop = null;
    protected static $_new_cache_group = null;
    protected static $_new_cache_global = null;
    protected static $_initialized = false;
    protected static $types = array();
    protected $webserviceParameters = array(
        'fields' => array(
            'value' => array(),
        ),
    );

    public static function get($key, $idLang = null, $idShopGroup = null, $idShop = null, $default = false)
    {
        return $default;
    }

    public static function getGlobalValue($key, $idLang = null)
    {
        return Configuration::get($key, $idLang, 0, 0);
    }

    public static function getInt($key, $idShopGroup = null, $idShop = null)
    {
        return [];
    }

    public static function getMultiShopValues($key, $idLang = null)
    {
        return [];
    }

    public static function getMultiple($keys, $idLang = null, $idShopGroup = null, $idShop = null)
    {
        return [];
    }

    public static function hasKey($key, $idLang = null, $idShopGroup = null, $idShop = null)
    {
        return true;
    }

    public static function set($key, $values, $idShopGroup = null, $idShop = null)
    {
    }

    public static function updateGlobalValue($key, $values, $html = false)
    {
        return Configuration::updateValue($key, $values, $html, 0, 0);
    }

    public static function updateValue($key, $values, $html = false, $idShopGroup = null, $idShop = null)
    {
        return true;
    }

    public static function deleteByName($key)
    {
        return true;
    }

    public static function deleteFromContext($key)
    {
    }

    public static function hasContext($key, $idLang, $context)
    {
        return false;
    }

    public static function isOverridenByCurrentContext($key)
    {
        return false;
    }

    public static function isLangKey($key)
    {
        return false;
    }

    public static function isCatalogMode()
    {
        return false;
    }

    /**
     * Add SQL restriction on shops for configuration table.
     *
     * @param int $idShopGroup
     * @param int $idShop
     *
     * @return string
     */
    protected static function sqlRestriction($idShopGroup, $idShop)
    {
        if ($idShop) {
            return ' AND id_shop = ' . (int) $idShop;
        } elseif ($idShopGroup) {
            return ' AND id_shop_group = ' . (int) $idShopGroup . ' AND (id_shop IS NULL OR id_shop = 0)';
        } else {
            return ' AND (id_shop_group IS NULL OR id_shop_group = 0) AND (id_shop IS NULL OR id_shop = 0)';
        }
    }

    /**
     * This method is override to allow TranslatedConfiguration entity.
     *
     * @param string $sqlJoin
     * @param string $sqlFilter
     * @param string $sqlSort
     * @param string $sqlLimit
     *
     * @return array
     */
    public function getWebserviceObjectList($sqlJoin, $sqlFilter, $sqlSort, $sqlLimit)
    {
        $query = '
        SELECT DISTINCT main.`' . bqSQL($this->def['primary']) . '`
        FROM `' . _DB_PREFIX_ . bqSQL($this->def['table']) . '` main
        ' . $sqlJoin . '
        WHERE id_configuration NOT IN (
            SELECT id_configuration
            FROM `' . _DB_PREFIX_ . bqSQL($this->def['table']) . '_lang`
        ) ' . $sqlFilter . '
        ' . ($sqlSort != '' ? $sqlSort : '') . '
        ' . ($sqlLimit != '' ? $sqlLimit : '');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }
}
