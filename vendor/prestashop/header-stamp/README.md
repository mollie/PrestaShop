# Header Stamp

![PHP tests](https://github.com/PrestaShopCorp/header-stamp/workflows/PHP%20tests/badge.svg)

Update the headers of the current folder. This tools extracts the command originally available in the PrestaShop Core.

## Installation

This projet is downloadable via Composer, the PHP Package Manager. We recommend having it in the `require-dev` section of your dependancies as it is not needed on production environments.

```
composer require --dev prestashop/header-stamp
```

## Usage

If installed via Composer, the application is available in its binaries folder

```php
php vendor/bin/header-stamp
```

The default behavior is to apply the OSL license in every compatible file found in the current folder.

:warning: Header Stamp will scan and process all your compatible files, including `node_modules` or `vendor` if you do not specify the target. Use `--exclude` to avoid modifying dependency files.

```php
php vendor/bin/header-stamp --exclude=vendor,node_modules
```

Available options:

```
--license=LICENSE        License file to apply [default: "assets/osl3.txt"]
--exclude=EXCLUDE        Comma-separated list of folders and files to exclude from the update [default: ""]
--extensions=EXTENSIONS  Comma-separated list of file extensions to update [default: "php,js,css,scss,tpl,html.twig,json,vue"]
--target=PATH            Folder to work in [default: Current directory]
--dry-run                Dry-run mode does not modify files
--display-report         Whether or not to display a report
```

## Development

Install dependencies with composer. Two CI tools are configured for this project: php-cs-fixer and phpstan

```
composer install
php vendor/bin/php-cs-fixer fix --no-interaction --dry-run --diff
php phpstan analyse tests/phpstan/phpstan.neon
```

Before submitting changes to the projects, tests can be run with:

```
php tests/integration/runner/run.php
```
