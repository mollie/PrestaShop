Auto Index
=========

Automatically add an "index.php" in all your directories or your zip file recursively

## Getting Started

To use this script, choose one of the following options to get started:
* Download the latest release on Auto Index
* Fork this repository on GitHub

Use your own "index.php" file
* Edit "index.php" file in "[sources](https://github.com/jmcollin/autoindex/tree/master/sources)" directory

## Usage

- php-cli: `php index.php ../ps/modules/mymodules/ ../ps/themes/mythemes/`
* Web browser:
  - `http://localhost/autoindex/`
  - `http://localhost/autoindex/?path=../ps/modules/mymodules/,../ps/themes/mythemes/`

## Dependencies

Only for using this tool with a **zip**

(PHP 5 >= 5.2.0, PECL zip >= 1.1.0)

```
$ pecl install zip
```

## Version
1.0.1

## Copyright and License

Copyright 2014 Jean-Marie Collin. Code released under the [MIT License](https://github.com/jmcollin/autoindex/blob/master/LICENSE) license.

Theme used Copyright 2014 Iron Summit Media Strategies, LLC. Code released under the [Apache 2.0](https://github.com/IronSummitMedia/startbootstrap-freelancer/blob/gh-pages/LICENSE) license.
