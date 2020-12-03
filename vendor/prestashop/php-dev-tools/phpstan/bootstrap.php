<?php

declare (strict_types=1);
namespace MolliePrefix;

function requireFileIfItExists(string $filepath) : bool
{
    if (\file_exists($filepath)) {
        require_once $filepath;
        return \true;
    }
    return \false;
}
$rootDir = \getenv('_PS_ROOT_DIR_');
if (!$rootDir) {
    echo '[ERROR] Define _PS_ROOT_DIR_ with the path to PrestaShop folder' . \PHP_EOL;
    exit(1);
}
if (!\realpath($rootDir)) {
    echo \sprintf('[ERROR] _PS_ROOT_DIR_ configuration is wrong. No directory found at %s .', $rootDir) . \PHP_EOL;
    exit(1);
}
// This file will be in the directory vendor/prestashop/php-dev-tools/phpstan.
$pathToModuleRoot = __DIR__ . '/../../../../';
// Add module composer autoloader
require_once \realpath(__DIR__ . '/../../../autoload.php');
// Add PrestaShop composer autoload
\define('_PS_ADMIN_DIR_', $rootDir . '/admin-dev/');
\define('PS_ADMIN_DIR', \_PS_ADMIN_DIR_);
\MolliePrefix\requireFileIfItExists($rootDir . '/tools/smarty/Smarty.class.php');
\MolliePrefix\requireFileIfItExists($rootDir . '/config/defines.inc.php');
\MolliePrefix\requireFileIfItExists($rootDir . '/config/autoload.php');
\MolliePrefix\requireFileIfItExists($rootDir . '/config/bootstrap.php');
// Make sure loader php-parser is coming from php stan composer
// 1- Use module vendors
$loader = new \Composer\Autoload\ClassLoader();
$loader->setPsr4('PhpParser\\', ['vendor/nikic/php-parser/lib/PhpParser']);
$loader->register(\true);
// 2- Use with Docker container
$loader = new \Composer\Autoload\ClassLoader();
$loader->setPsr4('PhpParser\\', ['/composer/vendor/nikic/php-parser/lib/PhpParser']);
$loader->register(\true);
// 3- Use with PHPStan phar
$loader = new \Composer\Autoload\ClassLoader();
// Contains the vendor in phar, like "phar://phpstan.phar/vendor"
$loader->setPsr4('PhpParser\\', ['phar://' . \dirname($_SERVER['PATH_TRANSLATED']) . '/../phpstan/phpstan-shim/phpstan.phar/vendor/nikic/php-parser/lib/PhpParser/']);
$loader->register(\true);
// 4- Use phpstan phar with sym link
$loader = new \Composer\Autoload\ClassLoader();
$loader->setPsr4('PhpParser\\', ['phar://' . \realpath($_SERVER['PATH_TRANSLATED']) . '/vendor/nikic/php-parser/lib/PhpParser/']);
$loader->register(\true);
/*
 * At this time if _PS_VERSION_ is still undefined, this is likely because
 * - we are on a old PrestaShop (below 1.7.0.0),
 * - and the shop hasn't been installed yet.
 *
 * In that case, the constant can be set from another file in the installation folder.
 */
if (!\defined('_PS_VERSION_')) {
    $legacyInstallationFileDefiningConstant = ['/install-dev/install_version.php', '/install/install_version.php'];
    foreach ($legacyInstallationFileDefiningConstant as $file) {
        if (\MolliePrefix\requireFileIfItExists($rootDir . $file)) {
            \define('_PS_VERSION_', \_PS_INSTALL_VERSION_);
            break;
        }
    }
}
/*
 * Display version of PrestaShop, useful for debug
 */
if (\defined('_PS_VERSION_')) {
    echo 'Detected PS version ' . \_PS_VERSION_ . \PHP_EOL;
}
// We must declare these constant in this boostrap script.
// Ignoring the error partern with this value will throw another error if not found
// during the checks.
$constantsToDefine = ['_DB_SERVER_' => ['type' => 'string'], '_DB_NAME_' => ['type' => 'string'], '_DB_USER_' => ['type' => 'string'], '_DB_PASSWD_' => ['type' => 'string'], '_MYSQL_ENGINE_' => ['type' => 'string'], '_COOKIE_KEY_' => ['type' => 'string'], '_COOKIE_IV_' => ['type' => 'string'], '_DB_PREFIX_' => ['type' => 'string'], '_PS_SSL_PORT_' => ['type' => 'int'], '_THEME_NAME_' => ['type' => 'string'], '_THEME_COL_DIR_' => ['type' => 'string'], '_PARENT_THEME_NAME_' => ['type' => 'string'], '__PS_BASE_URI__' => ['type' => 'string'], '_PS_PRICE_DISPLAY_PRECISION_' => ['type' => 'int'], '_PS_PRICE_COMPUTE_PRECISION_' => ['type' => 'string', 'from' => '1.6.0.11'], '_PS_OS_CHEQUE_' => ['type' => 'int'], '_PS_OS_PAYMENT_' => ['type' => 'int'], '_PS_OS_PREPARATION_' => ['type' => 'int'], '_PS_OS_SHIPPING_' => ['type' => 'int'], '_PS_OS_DELIVERED_' => ['type' => 'int'], '_PS_OS_CANCELED_' => ['type' => 'int'], '_PS_OS_REFUND_' => ['type' => 'int'], '_PS_OS_ERROR_' => ['type' => 'int'], '_PS_OS_OUTOFSTOCK_' => ['type' => 'int'], '_PS_OS_OUTOFSTOCK_PAID_' => ['type' => 'int'], '_PS_OS_OUTOFSTOCK_UNPAID_' => ['type' => 'int'], '_PS_OS_BANKWIRE_' => ['type' => 'int'], '_PS_OS_PAYPAL_' => ['type' => 'int'], '_PS_OS_WS_PAYMENT_' => ['type' => 'int'], '_PS_OS_COD_VALIDATION_' => ['type' => 'int'], '_PS_THEME_DIR_' => ['type' => 'string'], '_PS_BASE_URL_' => ['type' => 'string'], '_MODULE_DIR_' => ['type' => 'string']];
foreach ($constantsToDefine as $key => $constantDetails) {
    // If already defined, continue
    if (\defined($key)) {
        continue;
    }
    // Some constants exist from a specific version of PrestaShop.
    // If the running PS version is below the one that created this constant, we pass.
    if (!empty($constantDetails['from']) && \defined('_PS_VERSION_') && \version_compare(\_PS_VERSION_, $constantDetails['from'], '<')) {
        continue;
    }
    switch ($constantDetails['type']) {
        case 'string':
            \define($key, 'DUMMY_VALUE');
            break;
        case 'int':
            \define($key, 1);
            break;
        default:
            \define($key, 'DUMMY_VALUE');
            break;
    }
}
