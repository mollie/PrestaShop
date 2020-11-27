#!/bin/bash

function runPHPStan {
    echo "Running PHPStan with PS $1"
    docker run -tid --rm -v ps-volume:/var/www/html --name test-ps prestashop/prestashop:$1
    docker run --rm --volumes-from test-ps -v $PWD:/web/module -e _PS_ROOT_DIR_=/var/www/html --workdir=/web/module phpstan/phpstan analyse --configuration=/web/module/phpstan.neon
    result=$?
    docker kill test-ps
    docker volume rm ps-volume

    if [ $result -ne $2 ]; then
        echo "Expected result $2 does not match $result";
        exit 1;
    fi
}

composer install

# For copy of phpstan folder, in case we work on another branch locally
cp -R ../../../phpstan vendor/prestashop/php-dev-tools/

runPHPStan 1.7 0
runPHPStan 1.6.0.1 1

