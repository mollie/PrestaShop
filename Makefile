ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
module = mollie

# target: fix-lint			- Launch php cs fixer
fix-lint:
	docker-compose run --rm php sh -c "vendor/bin/php-cs-fixer fix --using-cache=no"

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
	docker-compose -f docker-compose.$(VERSION).yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.$(VERSION).yml ps
	make waiting-for-containers-local
	make seeding-customized-sql
	make installing-uninstalling-enabling-module
	make chmod-app
	make open-e2e-tests-locally

# For CI build with PS autoinstall
e2eh$(VERSION):
	# detaching containers
	docker-compose -f docker-compose.$(VERSION).yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.$(VERSION).yml ps
	make waiting-for-containers-CI
	make seeding-customized-sql
	make installing-uninstalling-enabling-module
	make chmod-app

waiting-for-containers-CI:
	# waiting for app containers to build up
	sleep 90s

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

open-e2e-tests-locally:
	npm install -D cypress
	npm ci
	npx cypress open --config baseUrl=$(ENV_baseUrl$(VERSION)) --env MOLLIE_TEST_API_KEY='$(ENV_MOLLIE_TEST_API_KEY)'

run-e2e-tests-locally:
	npm install -D cypress
	npm ci
	npx cypress run

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

npm-package-install:
	cd views/assets && npm i && npm run build
