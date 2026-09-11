<?php
/**
 * Runs a module's own upgrade/Upgrade-*.php files against the version the shop has
 * registered, the way PrestaShop does once a merchant has replaced the module files.
 *
 * `bin/console prestashop:module upgrade` cannot be used: ModuleManager::upgrade()
 * calls setModuleOnDiskFromAddons() first, which unpacks the released module over
 * modules/<name> - here a bind mount of the checkout - so the code under test is
 * thrown away before a single upgrade file runs.
 *
 * Usage: php .docker/upgrade-module.php <module>
 */
if (php_sapi_name() !== 'cli') {
    exit(1);
}

$moduleName = isset($argv[1]) ? $argv[1] : '';

if ($moduleName === '') {
    fwrite(STDERR, "usage: php .docker/upgrade-module.php <module>\n");
    exit(1);
}

require_once __DIR__ . '/../../../config/config.inc.php';

// Same bootstrap as bin/console: upgrade files reach for Symfony services, and
// SymfonyContainer::getInstance() only hands one back when the global $kernel is booted.
$kernel = new AppKernel(getenv('SYMFONY_ENV') ?: 'dev', false);
$kernel->boot();

$context = Context::getContext();

if (!$context->employee) {
    $context->employee = new Employee(1);
}

$query = new DbQuery();
$query->select('version')
    ->from('module')
    ->where('name = "' . pSQL($moduleName) . '"');

$registeredVersion = Db::getInstance()->getValue($query);

if (!$registeredVersion) {
    fwrite(STDERR, sprintf("The shop has no %s module installed.\n", $moduleName));
    exit(1);
}

$module = Module::getInstanceByName($moduleName);

if (!$module instanceof Module) {
    fwrite(STDERR, sprintf("Could not instantiate the %s module.\n", $moduleName));
    exit(1);
}

$module->installed = true;
$module->database_version = $registeredVersion;

if (!Module::initUpgradeModule($module)) {
    fwrite(STDERR, sprintf(
        "Nothing to upgrade: the shop reports %s %s and the files ship %s.\n",
        $moduleName,
        $registeredVersion,
        $module->version
    ));
    exit(1);
}

try {
    $upgrade = $module->runUpgradeModule();
} catch (Throwable $exception) {
    fwrite(STDERR, sprintf(
        "Upgrading %s from %s threw %s: %s\n  in %s line %d\n",
        $moduleName,
        $registeredVersion,
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));
    exit(1);
}

foreach ($module->getErrors() as $error) {
    fwrite(STDERR, strip_tags($error) . "\n");
}

if (!$upgrade['success'] || $upgrade['number_upgrade_left'] > 0) {
    fwrite(STDERR, sprintf(
        "Upgrading %s from %s failed on version %s, %d file(s) left.\n",
        $moduleName,
        $registeredVersion,
        $upgrade['version_fail'],
        $upgrade['number_upgrade_left']
    ));
    exit(1);
}

// runUpgradeModule() records the last upgrade file it ran, so a module whose newest
// upgrade file predates its current version stays behind. PrestaShop closes that gap
// on the next request; do it here so the shop ends up where a merchant's would.
Module::upgradeModuleVersion($moduleName, $module->version);

printf(
    "Upgraded %s from %s to %s, %d upgrade file(s) applied.\n",
    $moduleName,
    $registeredVersion,
    $module->version,
    $upgrade['number_upgraded']
);
