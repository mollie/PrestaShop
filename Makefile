ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

# target: fix-lint			- Launch php cs fixer
fix-lint:
	docker-compose run --rm php sh -c "vendor/bin/php-cs-fixer fix --using-cache=no"

# Launch example: make VERSION=1785 e2eh1785_local, make VERSION=8 e2eh8_local etc.
# Local machine docker build with PS autoinstall
e2eh$(VERSION)_local:
	# detaching containers
	docker-compose -f docker-compose.$(VERSION).yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.$(VERSION).yml ps
	# waiting for app containers to build up
	/bin/bash .docker/wait-loader.sh 8002
	# seeding the customized settings for PS
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_$(VERSION).sql
	# installing module
	docker exec -i prestashop-mollie-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# uninstalling module
	docker exec -i prestashop-mollie-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall mollie"
	# installing the module again
	docker exec -i prestashop-mollie-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# enabling the module
	docker exec -i prestashop-mollie-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module enable mollie"
	# chmod all folders
	docker exec -i prestashop-mollie-$(VERSION) sh -c "chmod -R 777 /var/www/html"

# For CI build with PS autoinstall
e2eh$(VERSION):
	# detaching containers
	docker-compose -f docker-compose.$(VERSION).yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.$(VERSION).yml ps
	# waiting for app containers to build up
	sleep 90s
	# configuring base database
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_$(VERSION).sql
	# installing module
	docker exec -i prestashop-mollie-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# uninstalling module
	docker exec -i prestashop-mollie-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall mollie"
	# installing the module again
	docker exec -i prestashop-mollie-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# enabling the module
	docker exec -i prestashop-mollie-$(VERSION) sh -c "cd /var/www/html && php  bin/console prestashop:module enable mollie"
	# chmod all folders
	docker exec -i prestashop-mollie-$(VERSION) sh -c "chmod -R 777 /var/www/html"

npm-package-install:
	cd views/assets && npm i && npm run build

run-e2e-tests-locally:
	npm install -D cypress
	npm ci
	npx cypress run

# checking the module upgrading - installs older module then installs from master branch
upgrading-module-test-1785:
	git fetch
	git checkout v5.2.0 .
	composer install
	# installing 5.2.0 module
	docker exec -i prestashop-mollie-1785 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# installing develop branch module
	git checkout -- .
	git checkout develop --force
	docker exec -i prestashop-mollie-1785 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
