<?php

foreach (['PRESTASHOP_VERSION', 'MOLLIE_API_KEY'] as $env) {
    if (!getenv($env)) {
        echo "`$env` is missing!";
        exit(1);
    }
}
