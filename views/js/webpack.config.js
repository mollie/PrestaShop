/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
/* eslint-disable */
const path = require('path');
const webpack = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const WebpackRequireFrom = require('webpack-require-from');

// Uncomment for analyzing webpack size (1/2)
// const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const { name, version } = require('./package.json');

const production = (process.env.NODE_ENV === 'production');
const plugins = [
  new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
  new HtmlWebpackPlugin({
    filename: 'manifest.php',
    template: '../templates/admin/manifest.php.tpl',
    inject: false,
    production,
    version,
    chunksSortMode: 'none',
  }),
  new WebpackRequireFrom({ variableName: 'window.MollieModule.urls.publicPath' }),
  new webpack.BannerPlugin(`
    Mollie       https://www.mollie.nl
    @author      Mollie B.V. <info@mollie.nl>
    @copyright   Mollie B.V.
    @link        https://github.com/mollie/PrestaShop
    @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
  `),
  // Uncomment for analyzing webpack size (2/2)
  // new BundleAnalyzerPlugin(),
];
const optimization = {
  minimizer: [
    new TerserPlugin({
      terserOptions: {
        output: {
          comments: /^\**!/,
        },
      },
    }),
  ],
  splitChunks: {
    chunks: 'all',
  },
  namedChunks: true,
};

module.exports = {
  entry: {
    app: ['./src/index.ts'],
  },
  resolve: {
    extensions: ['.js', '.jsx', '.ts', '.tsx', '.css'],
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: `[name]${production ? `-v${version}` : ''}.min.js`,
    library: ['MollieModule', '[name]'],
    libraryTarget: 'var',
    jsonpFunction: `webpackJsonP_${name.replace(/[^a-z0-9_]/g, ' ').trim().replace(/\\s+/g, '_')}`,
  },
  devtool: production ? undefined : 'source-map',
  module: {
    rules: [
      {
        test: /\.(tsx?)|(jsx?)$/,
        include: [
          path.resolve(__dirname, 'src'),
        ],
        exclude: path.resolve(__dirname, 'node_modules'),
        use: {
          loader: 'babel-loader',
          options: {
            plugins: [
              '@babel/plugin-proposal-class-properties',
            ],
            presets: [
              ['@babel/preset-env', {
                targets: {
                  browsers: [
                    'defaults',
                    'ie >= 9',
                    'ie_mob >= 10',
                    'edge >= 12',
                    'chrome >= 30',
                    'chromeandroid >= 30',
                    'android >= 4.4',
                    'ff >= 30',
                    'safari >= 9',
                    'ios >= 9',
                    'opera >= 36',
                  ],
                },
                useBuiltIns: 'usage',
              }],
              '@babel/typescript',
              '@babel/react',
            ],
            sourceMap: !production,
          },
        },
      },
    ],
  },
  plugins,
  optimization,
};
