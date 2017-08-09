#!/bin/sh
git submodule update --init
zip -9 -r mollie-prestashop-x.x.x.zip mollie README.mdown -x mollie/*.git* mollie/*.DS_Store