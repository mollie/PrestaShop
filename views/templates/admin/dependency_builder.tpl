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