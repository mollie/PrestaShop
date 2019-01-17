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
 */
const path = require('path');
const webpack = require('webpack');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
// Uncomment for analyzing webpack size (1/2)
// const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

const production = (process.env.NODE_ENV === 'production');
const plugins = [
  new webpack.optimize.ModuleConcatenationPlugin(),
  new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
  // Uncomment for analyzing webpack size (2/2)
  // new BundleAnalyzerPlugin(),
];
const optimization = {
  minimizer: [],
};

if (production) {
  optimization.minimizer.push(
    new UglifyJsPlugin({
      uglifyOptions: {
        compress: {
          warnings: false,
          conditionals: true,
          unused: true,
          comparisons: true,
          sequences: true,
          dead_code: true,
          evaluate: true,
          if_return: true,
          join_vars: true,
        },
        cache: true,
        parallel: true,
        output: {
          comments: /^\**!|@preserve|@license|@cc_on/,
        },
      },
    })
  );
  plugins.push(
    new webpack.DefinePlugin({
      'process.env.NODE_ENV': JSON.stringify('production'),
    })
  );
  plugins.push(
    new webpack.BannerPlugin(` Copyright (c) 2012-2019, Mollie B.V.
 All rights reserved.
 
 Redistribution and use in source and binary forms, with or without
 modification, are permitted provided that the following conditions are met:
 
 - Redistributions of source code must retain the above copyright notice,
    this list of conditions and the following disclaimer.
 - Redistributions in binary form must reproduce the above copyright
    notice, this list of conditions and the following disclaimer in the
    documentation and/or other materials provided with the distribution.
 
 THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS \`\`AS IS'' AND ANY
 EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 DAMAGE.
 
 @author     Mollie B.V. <info@mollie.nl>
 @copyright  Mollie B.V.
 @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 @category   Mollie
 @package    Mollie
 @link       https://www.mollie.nl`)
  );
} else {
  plugins.push(
    new webpack.LoaderOptionsPlugin({
      debug: true,
    })
  );
}

module.exports = {
  entry: {
    front: ['./front/index.ts'],
    back: ['./back/index.ts'],
  },
  resolve: {
    extensions: ['.js', '.jsx', '.ts', '.tsx', '.css'],
  },
  output: {
    path: path.resolve(__dirname, '../dist'),
    publicPath: '/assets/',
    filename: '[name].min.js',
    library: ['MollieModule', '[name]'],
    libraryTarget: 'var',
  },
  devtool: 'source-map',
  module: {
    rules: [
      {
        test: /\.(tsx?)|(jsx?)$/,
        include: [
          path.join(__dirname, 'front'),
          path.join(__dirname, 'back'),
        ],
        exclude: path.join(__dirname, 'node_modules'),
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
                useBuiltIns: 'entry',
                debug: false,
              }],
              '@babel/typescript',
              '@babel/react',
            ],
            sourceMap: true,
          },
        },
      },
      {
        test: /\.css$/,
        use: [
          'style-loader',
          {
            loader: 'typings-for-css-modules-loader',
            options: {
              modules: true,
              importLoaders: true,
              localIdentName: '[name]__[local]___[hash:base64:5]',
            },
          },
        ],
        include: [
          path.join(__dirname, 'front'),
          path.join(__dirname, 'back'),
        ],
      },
    ],
  },
  plugins,
  optimization,
};
