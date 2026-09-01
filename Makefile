ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
module = mollie

# Both captured at parse time, before upgrading-module-test checks out v5.2.0:
# the commit to come back to (CI leaves a detached HEAD, so there is not always a
# branch name) and the version the shop must report once the upgrade has run.
HEAD_REF := $(shell git rev-parse HEAD)
MODULE_VERSION := $(shell sed -n "s/.*this->version = '\([0-9.]*\)'.*/\1/p" mollie.php | head -1)
LOCK_BACKUP := $(ROOT_DIR)/../composer.lock.upgrading-module-test
# The oldest release in the supported window: Mollie's compatibility table pairs
# PS 1.7.6 - 9.0.0 with Mollie 6, and there is no v6.0.0 tag, only betas.
BASE_TAG := v6.0.1

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
	# Same check as CI. The old wait-loader.sh printed "Failed: Docker container
	# host did not return a 302" and still exited 0, so a cold start marched on
	# into seeding against a MySQL that was not up yet and e2eh<VERSION>_local
	# died there every time.
	/bin/bash .docker/wait-for-shop.sh 8002 300

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

# target: trust-forwarded-proto	- Teach Apache that `X-Forwarded-Proto: https` means HTTPS.
# Cloudflare terminates TLS and forwards plain HTTP to the container. Without
# this the shop is configured for https (set-shop-domain with SSL=1) but PHP
# sees http, so PrestaShop keeps redirecting to its own canonical https URL and
# either loops or lands on /security/compromised. Run it after set-shop-domain
# whenever something else terminates TLS in front of the shop.
trust-forwarded-proto:
	docker exec -i prestashop-$(module)-$(VERSION) sh -c "echo 'SetEnvIf X-Forwarded-Proto \"https\" HTTPS=on' > /etc/apache2/conf-enabled/zz-forwarded-https.conf && service apache2 reload"

# target: tunnel			- Expose the local shop through a Cloudflare named tunnel (foreground).
# Needed for the checkout specs: Mollie rejects an unreachable webhookUrl, so no
# checkout can complete against localhost. Put CF_TUNNEL_TOKEN in .env, then in
# one terminal `make tunnel`, and in another:
#   make VERSION=8 set-shop-domain HOST=ps8-checkout.invertusdemo.com
#   make VERSION=8 trust-forwarded-proto
#   cd tests/e2e && E2E_BASE_URL=https://ps8-checkout.invertusdemo.com \
#     E2E_CHECKOUT_API=orders npx playwright test --project=checkout-orders
tunnel:
	@test -n "$(CF_TUNNEL_TOKEN)" || (echo "CF_TUNNEL_TOKEN is not set (put it in .env)"; exit 1)
	cloudflared tunnel --no-autoupdate run --token $(CF_TUNNEL_TOKEN)

# target: e2e-tests-locally	- Run the Playwright suite against a shop already started by e2eh<VERSION>_local.
# The two checkout phases are separate invocations, exactly as CI runs them: they
# rewrite the same per-method API assignment and so must not overlap.
e2e-tests-locally:
	npm ci
	npx playwright install chromium
	cd tests/e2e && npx playwright test --project=admin --project=webhook --project=mobile
	# Its own invocation with --workers=1: these rewrite global config and the
	# whole method set, so they must not overlap each other or the projects above.
	cd tests/e2e && npx playwright test --project=config --workers=1
	cd tests/e2e && E2E_CHECKOUT_API=orders npx playwright test --project=checkout-orders
	cd tests/e2e && E2E_CHECKOUT_API=payments npx playwright test --project=cfg-payments --project=checkout-payments
	# Guest card checkout and single-click. Completing a payment needs a publicly
	# reachable E2E_BASE_URL (see `make tunnel`); against localhost the paid cases
	# skip themselves and the payment-step assertions still run.
	cd tests/e2e && npx playwright test --project=checkout-config --workers=1

# target: e2e-tests-ui		- Same suite in Playwright's interactive UI mode.
e2e-tests-ui:
	npm ci
	npx playwright install chromium
	cd tests/e2e && npx playwright test --ui

# checking the module upgrading - installs $(BASE_TAG) then upgrades to the commit under test
upgrading-module-test-$(VERSION):
	git fetch --tags --force
	# e2eh$(VERSION) leaves the module installed at the current version, and
	# `module install` on an installed module is a no-op that still exits 0. Without
	# this the shop never goes back to $(BASE_TAG) and the upgrade leg has nothing to
	# upgrade - the job goes green having tested nothing. Uninstall while the
	# current code is still checked out, so it matches the schema it created.
	docker exec -i prestashop-$(module)-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall $(module)"
	# A real checkout of the tag, not `git checkout $(BASE_TAG) .`. The partial form
	# restores the tag's files but never deletes files added after it, so everything
	# the branch added since - shared/, config/services.yml entries - stayed behind
	# and got compiled against an autoloader composer had just rebuilt from the tag's
	# composer.json, which knows nothing about them.
	# composer.lock is gitignored on the branch and untracked in the tag, so keep the
	# head's aside: the tag needs its own resolve (league/container 2.5.0 against the
	# branch's 3.3.3, among others) and the head must not inherit the result.
	cp composer.lock $(LOCK_BACKUP)
	git checkout --detach --force $(BASE_TAG)
	rm -f composer.lock
	# The tag's require-dev pins roave/security-advisories dev-latest, which always
	# resolves to today's advisory list and by now conflicts with every guzzlehttp/psr7
	# the tag's runtime constraints allow, so a release this old can no longer be
	# resolved. --no-dev is not enough, composer still solves require-dev. Drop the
	# package outright: it is an install-time guard and the shop only needs the runtime
	# set. The checkout back to HEAD_REF undoes the composer.json edit.
	composer remove --dev roave/security-advisories --no-update --no-interaction
	# Composer 2.9 refuses to load any package covered by a security advisory, and a
	# release this old is full of them - symfony/http-client ^4.4 and guzzlehttp/psr7
	# 1.x among its runtime requirements - so the resolve dies on the runner while it
	# still succeeds on an older composer. Turn the policy off for the tag: this
	# checkout exists only to be upgraded away from inside a throwaway container, and
	# the head's own dependencies are resolved separately and still audited.
	php -r '$$f = "composer.json"; $$j = json_decode(file_get_contents($$f), true); $$j["config"]["policy"]["advisories"]["block"] = false; file_put_contents($$f, json_encode($$j, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));'
	composer update --no-dev
	# installing $(BASE_TAG) module
	docker exec -i prestashop-$(module)-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module install $(module)"
	$(call assert_module_version,$(patsubst v%,%,$(BASE_TAG)))
	# the module under test - HEAD_REF, not develop, or the upgrade leg asserts
	# against develop and never sees the commit being tested
	git checkout --detach --force $(HEAD_REF)
	# the autoloader is still the tag's at this point
	mv $(LOCK_BACKUP) composer.lock
	composer install
	# Not `bin/console prestashop:module upgrade`: that pulls the released module from
	# the Addons marketplace over the bind mount and upgrades that instead of the
	# commit under test. .docker/upgrade-module.php runs the local upgrade/*.php files.
	docker exec -i prestashop-$(module)-$(VERSION) sh -c "cd /var/www/html && php  modules/$(module)/.docker/upgrade-module.php $(module)"
	$(call assert_module_version,$(MODULE_VERSION))

# Asserts the shop really records the version we expect. PrestaShop reports success
# for no-op installs, so without this the legs above cannot fail. Has to be a canned
# recipe, not a target: the tag checkout replaces this Makefile on disk with v5.2.0's,
# so a $(MAKE) recursion would look for a target that does not exist there.
define assert_module_version
	@installed=$$(mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop -N -B \
		-e "SELECT version FROM ps_module WHERE name = '$(module)'" prestashop 2>/dev/null); \
	if [ "$$installed" != "$(1)" ]; then \
		echo "FAIL: expected the shop to report module version $(1), got '$$installed'"; \
		exit 1; \
	fi; \
	echo "OK: shop reports module version $$installed"
endef

prepare-zip:
	composer install --no-dev --optimize-autoloader --classmap-authoritative
	composer dump-autoload --no-dev --optimize --classmap-authoritative
	rm -rf .git .docker .editorconfig .github tests .php-cs-fixer.php Makefile docker-compose*.yml .gitignore bin codeception.yml package-lock.json package.json .php_cs.dist .php-cs-fixer.dist .php-cs-fixer.dist.php views/assets/webpack.config.js
	rm -rf views/js/admin/library/node_modules views/js/admin/library/src views/js/admin/library/package.json views/js/admin/library/package-lock.json views/js/admin/library/tsconfig.json views/js/admin/library/tsconfig.app.json views/js/admin/library/tsconfig.node.json views/js/admin/library/vite.config.ts views/js/admin/library/eslint.config.js views/js/admin/library/postcss.config.js views/js/admin/library/tailwind.config.js views/js/admin/library/README.md
	# PrestaShop security requirement: every shipped directory must contain an index.php
	find . -type d ! -path './.git' ! -path './.git/*' | while read -r dir; do [ -f "$$dir/index.php" ] || cp index.php "$$dir/index.php"; done

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