<?php
/**
 * Runs a module's own upgrade/Upgrade-*.php files against the version the shop has
 * registered, the way PrestaShop does once a merchant has replaced the module files.
 *
 * `bin/console prestashop:module upgrade` cannot be used for this. ModuleManager::upgrade()
 * calls setModuleOnDiskFromAddons() whenever the marketplace advertises a newer version,
 * which unpacks the released module over modules/<name> - here a bind mount of the
 * checkout - so the code under test is thrown away before a single upgrade file runs.
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

// Same bootstrap as bin/console. Upgrade files reach for Symfony services such as
// prestashop.adapter.module.tab.register, and SymfonyContainer::getInstance() only
// hands one back when the global $kernel is booted.
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

$upgrade = $module->runUpgradeModule();

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

// runUpgradeModule() only records the last upgrade file it ran, so a module whose
// newest upgrade file predates its current version stays behind. PrestaShop closes
// that gap on the next request, when loadUpgradeVersionList() finds nothing left to
// run; do it here so the shop ends up where a merchant's would.
Module::upgradeModuleVersion($moduleName, $module->version);

printf(
    "Upgraded %s from %s to %s, %d upgrade file(s) applied.\n",
    $moduleName,
    $registeredVersion,
    $module->version,
    $upgrade['number_upgraded']
);
