/**
 * NOTICE OF LICENSE
 *
 * @author    INVERTUS, UAB www.invertus.eu <support@invertus.eu>
 * @copyright Copyright (c) permanent, INVERTUS, UAB
 * @license   MIT
 * @see       /LICENSE
 *
 *  International Registered Trademark & Property of INVERTUS, UAB
 */
const Encore = require('@symfony/webpack-encore');

Encore
  .configureRuntimeEnvironment('production')
  .setPublicPath('/')
  .setOutputPath('./compiled')
  .configureFilenames({
    js: '[name].bundle.js',
    css: '[name].bundle.css'
  })
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .addEntry('subscription', './js/subscription.js')
  .enableSassLoader()
  .configureBabel((babelConfig) => {})
Encore.disableSingleRuntimeChunk();
module.exports = Encore.getWebpackConfig();
