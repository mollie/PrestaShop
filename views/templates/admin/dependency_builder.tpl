{*
* 2007-2025 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!-- Load cdc library -->
<script src="https://assets.prestashop3.com/dst/mbo/v1/mbo-cdc-dependencies-resolver.umd.js"></script>

<!-- cdc container -->
<div id="cdc-container"></div>

<script defer>
  const renderMboCdcDependencyResolver = window.mboCdcDependencyResolver.render
  const context = {
    ...{$dependencies|json_encode},
    onDependenciesResolved: () => location.reload(),
    onDependencyResolved: (dependencyData) => console.log('Dependency installed', dependencyData), // name, displayName, version
    onDependencyFailed: (dependencyData) => console.log('Failed to install dependency', dependencyData),
    onDependenciesFailed: () => console.log('There are some errors'),
  }
  renderMboCdcDependencyResolver(context, '#cdc-container')
</script>
