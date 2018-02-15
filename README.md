# Mollie
This is a Mollie module for PrestaShop and thirty bees.
You will need to have a [Mollie](https://www.mollie.com) account before you can use this plugin.

## Installation
### Module installation
- Upload the module via your Back Office
- Install the module
- Check if there are any errors and correct them if necessary
- Profit!

## Compatibility
This module has been tested with these versions:  
- `1.5.0.17` - `1.5.6.3`
- `1.6.0.5` - `1.6.1.18`
- `1.7.0.5` - `1.7.3.0`

## Requirements
- PHP > 5.2.0
- PHP cURL extension
- PHP JSON extension

## How to build a package
### Webpack
The plugin contains a webpack package at `views/js/app`.
Before the module can be zipped, this webpack item needs to be built for 
dev (`webpack`) or production (`NODE_ENV=production webpack`).

Begin with CD'ing into on the directory, e.g. `views/js/app/`, then install the necessary node modules:
```shell
$ npm i
```
(shorthand for `npm install`, you can also use [yarn](https://yarnpkg.com/lang/en/),
 but be sure to replace `npm i` with `yarn install` and `npm i -g` with `yarn add global`)

Install webpack globally:
```shell
$ npm i -g webpack
```
You can now build the package
```shell
$ cd /views/js/app
$ webpack
```
To build a production package you will have to set the environment:
```shell
$ cd /views/js/app
$ NODE_ENV=production webpack
```
This will both run webpack minifier plugins as well as use the production version of React.

### Module zip
To build the whole package, go to the root directory of the module and run:
```shell
$ ./build.sh
```
This will result in a production package named `mollie-vX.X.X.zip`.
