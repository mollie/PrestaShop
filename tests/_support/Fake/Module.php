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

abstract class Module
{
    public $id = null;
    public $version;
    public $database_version;
    public $registered_version;
    public $ps_versions_compliancy = array();
    public $dependencies = array();
    public $name;
    public $displayName;
    public $description;
    public $author;
    public $author_uri = '';
    public $module_key = '';
    public $description_full;
    public $additional_description;
    public $compatibility;
    public $nb_rates;
    public $avg_rate;
    public $badges;
    public $need_instance = 1;
    public $tab = null;
    public $active = false;
    public $trusted = false;
    public $warning;
    public $enable_device = 7;
    public $limited_countries = array();
    public $controllers = array();
    public static $classInModule = array();
    protected $_lang = array();
    protected $_path = null;
    protected $local_path = null;
    protected $_errors = array();
    protected $_confirmations = array();
    protected $table = 'module';
    protected $identifier = 'id_module';
    protected static $modules_cache;
    protected static $_INSTANCE = array();
    protected static $_generate_config_xml_mode = false;
    protected static $l_cache = array();
    protected static $cache_permissions = array();
    protected static $cache_lgc_access = array();
    protected $context;
    protected $smarty;
    protected $current_subtemplate = null;
    protected static $update_translations_after_install = true;
    protected static $_batch_mode = false;
    protected static $_defered_clearCache = array();
    protected static $_defered_func_call = array();
    protected $tabs = array();
    public $allow_push;
    public $push_time_limit = 180;
    public static $_log_modules_perfs = null;
    public static $_log_modules_perfs_session = null;
    private $container;
    private static $cachedModuleNames = null;

    const CACHE_FILE_MODULES_LIST = '/config/xml/modules_list.xml';
    const CACHE_FILE_TAB_MODULES_LIST = '/config/xml/tab_modules_list.xml';
    const CACHE_FILE_ALL_COUNTRY_MODULES_LIST = '/config/xml/modules_native_addons.xml';
    const CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST = '/config/xml/default_country_modules_list.xml';
    const CACHE_FILE_CUSTOMER_MODULES_LIST = '/config/xml/customer_modules_list.xml';
    const CACHE_FILE_MUST_HAVE_MODULES_LIST = '/config/xml/must_have_modules_list.xml';
    const CACHE_FILE_TRUSTED_MODULES_LIST = '/config/xml/trusted_modules_list.xml';
    const CACHE_FILE_UNTRUSTED_MODULES_LIST = '/config/xml/untrusted_modules_list.xml';
    public static $hosted_modules_blacklist = array('autoupgrade');

    public function __construct($name = null, Context $context = null)
    {
    }

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }

//    public function l($string, $specific = false, $locale = null)
//    {
//        return $string;
//    }
}
