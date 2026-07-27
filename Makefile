ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
module = mollie

# target: fix-lint			- Launch php cs fixer
fix-lint:
	docker compose run --rm php sh -c "vendor/bin/php-cs-fixer fix --using-cache=no"

# Launch the PS build and E2E Cypress app automatically. Eexample: make VERSION=1785 e2eh1785_local, make VERSION=8 e2eh8_local etc.
# Warning: .env with secrets must be imported if you wanna test locally! This checks the .env existence, ignoring if there is no such on your machine.

ifneq ("$(wildcard .env)","")
    include .env
    export
endif

# Local machine docker build with PS autoinstall
e2eh$(VERSION)_local:
	composer install
	# detaching containers
	docker compose -f docker-compose.$(VERSION).yml up -d --force-recreate
	# sees what containers are running
	docker compose -f docker-compose.$(VERSION).yml ps
	make waiting-for-containers-local
	make seeding-customized-sql
	make installing-uninstalling-enabling-module
	make chmod-app
	make set-shop-domain HOST=localhost:8002 SSL=0
	@echo "Shop ready on http://localhost:8002 — run 'make e2e-tests-locally' or 'make e2e-tests-ui'."

# For CI build with PS autoinstall
e2eh$(VERSION):
	# detaching containers
	docker compose -f docker-compose.$(VERSION).yml up -d --force-recreate
	# sees what containers are running
	docker compose -f docker-compose.$(VERSION).yml ps
	make waiting-for-containers-CI
	make seeding-customized-sql
	make installing-uninstalling-enabling-module
	make chmod-app

waiting-for-containers-CI:
	# waiting for app containers to build up
	/bin/bash .docker/wait-for-shop.sh 8002 150

waiting-for-containers-local:
	# waiting for app containers to build up
	/bin/bash .docker/wait-loader.sh 8002

seeding-customized-sql:
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_$(VERSION).sql

installing-uninstalling-enabling-module:
	# installing module
	docker exec -i prestashop-$(module)-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module install $(module)"
	# uninstalling module
	docker exec -i prestashop-$(module)-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall $(module)"
	# installing the module again
	docker exec -i prestashop-$(module)-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module install $(module)"
	# enabling the module
	docker exec -i prestashop-$(module)-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module enable $(module)"

chmod-app:
	# chmod all folders
	docker exec -i prestashop-$(module)-$(VERSION) sh -c "chmod -R 777 /var/www/html"

# target: set-shop-domain	- Point the shop at HOST, replacing the domain baked into the SQL seed.
# Example (plain http, no TLS in front):  make set-shop-domain VERSION=8 HOST=localhost:8002 SSL=0
# Example (behind a Cloudflare tunnel):   make set-shop-domain VERSION=8 HOST=ps8-checkout.invertusdemo.com
SSL ?= 1
set-shop-domain:
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -uroot -pprestashop prestashop -e " \
		UPDATE ps_shop_url SET domain='$(HOST)', domain_ssl='$(HOST)'; \
		UPDATE ps_configuration SET value='$(HOST)' WHERE name IN ('PS_SHOP_DOMAIN','PS_SHOP_DOMAIN_SSL'); \
		UPDATE ps_configuration SET value='$(SSL)' WHERE name IN ('PS_SSL_ENABLED','PS_SSL_ENABLED_EVERYWHERE');"
	# The cache is cleared as root, so hand `var` back to the web user afterwards
	# or PrestaShop cannot rebuild it and every page 500s.
	docker exec -i prestashop-$(module)-$(VERSION) sh -c "cd /var/www/html && rm -rf var/cache/* && chmod -R 777 var" || true

# target: e2e-tests-locally	- Run the Playwright suite against a shop already started by e2eh<VERSION>_local.
# The two checkout phases are separate invocations, exactly as CI runs them: they
# rewrite the same per-method API assignment and so must not overlap.
e2e-tests-locally:
	npm ci
	npx playwright install chromium
	cd tests/e2e && npx playwright test --project=admin --project=webhook --project=mobile
	cd tests/e2e && E2E_CHECKOUT_API=orders npx playwright test --project=checkout-orders
	cd tests/e2e && E2E_CHECKOUT_API=payments npx playwright test --project=cfg-payments --project=checkout-payments

# target: e2e-tests-ui		- Same suite in Playwright's interactive UI mode.
e2e-tests-ui:
	npm ci
	npx playwright install chromium
	cd tests/e2e && npx playwright test --ui

# checking the module upgrading - installs older module then installs from master branch
upgrading-module-test-$(VERSION):
	git fetch
	git checkout v5.2.0 .
	composer install
	# installing 5.2.0 module
	docker exec -i prestashop-$(module)-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module install $(module)"
	# installing develop branch module
	git checkout -- .
	git checkout develop --force
	docker exec -i prestashop-$(module)-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module install $(module)"

prepare-zip:
	composer install --no-dev --optimize-autoloader --classmap-authoritative
	composer dump-autoload --no-dev --optimize --classmap-authoritative
	rm -rf .git .docker .editorconfig .github tests .php-cs-fixer.php Makefile docker-compose*.yml .gitignore bin codeception.yml package-lock.json package.json .php_cs.dist .php-cs-fixer.dist .php-cs-fixer.dist.php views/assets/webpack.config.js
	rm -rf views/js/admin/library/node_modules views/js/admin/library/src views/js/admin/library/package.json views/js/admin/library/package-lock.json views/js/admin/library/tsconfig.json views/js/admin/library/tsconfig.app.json views/js/admin/library/tsconfig.node.json views/js/admin/library/vite.config.ts views/js/admin/library/eslint.config.js views/js/admin/library/postcss.config.js views/js/admin/library/tailwind.config.js views/js/admin/library/README.md

start-ps-for-tests:
	docker network create prestashop-net-1.7.8-7.4
	docker run -ti --name mollie-testing-env-mysql-1.7.8-7.4 --network prestashop-net-1.7.8-7.4 -e MYSQL_ROOT_PASSWORD=admin -e MYSQL_DATABASE=prestashop -p 3307:3306 -d mysql:5.7
	docker run -ti -v $(ROOT_DIR):/var/www/html/modules/mollie -v $(ROOT_DIR)/.docker/php/php.ini:/usr/local/etc/php/conf.d/custom-php.ini --name mollie-testing-env-prestashop-1.7.8-7.4 --network prestashop-net-1.7.8-7.4 -e DB_SERVER=mollie-testing-env-mysql-1.7.8-7.4 -e PS_INSTALL_AUTO=1 -e DB_NAME=prestashop -e PS_DOMAIN=localhost:8080 -e PS_FOLDER_ADMIN=admin1 -p 8080:80 -d prestashop/prestashop:1.7.8-7.4
	sleep 15s

run-ps-unit-tests:
	docker exec -i mollie-testing-env-prestashop-1.7.8-7.4 bash -c "cd /var/www/html/modules/mollie && php vendor/bin/phpunit -c tests/phpunit.xml --testsuite Unit"

create-env:
	echo "MODULE_ENV='$(env)'" > .env

build-react:
	cd views/js/admin/library && npm install && npm run build && cd ../../../../..