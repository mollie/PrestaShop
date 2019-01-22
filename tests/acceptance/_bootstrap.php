<?php

foreach (['PRESTASHOP_VERSION', 'MOLLIE_API_KEY'] as $env) {
    if (!getenv($env)) {
        die("`$env` is missing!");
    }
}
