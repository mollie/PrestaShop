ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

# target: fix-lint			- Launch php cs fixer
fix-lint:
	docker-compose run --rm php sh -c "vendor/bin/php-cs-fixer fix --using-cache=no"

############ PS8 ############################

# All the commands required to build prestashop-8 version locally
bps8: build-ps-8
build-ps-8:
	# configuring your prestashop
	docker exec -i prestashop-8 sh -c "rm -rf /var/www/html/install"
	# configuring base database
	mysql -h 127.0.0.1 -P 9003 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_8.sql
	# installing module
	docker exec -i prestashop-8 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# uninstalling module
	docker exec -i prestashop-8 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall mollie"
	# installing the module again
	docker exec -i prestashop-8 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# chmod all folders
	docker exec -i prestashop-8 sh -c "chmod -R 777 /var/www/html"

# Preparing prestashop-8 for e2e tests - this actually launched an app in background. You can access it already!
e2e8p: e2e-8-prepare
e2e-8-prepare:
	# detaching containers
	docker-compose -f docker-compose.8.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.8.yml ps
	# waits for mysql to load
	/bin/bash .docker/wait-for-container.sh mollie-mysql-8
	# preloads initial data
	make bps8
	/bin/bash .docker/wait-for-container.sh prestashop-8

# Run e2e tests in headless way.
e2eh8: test-e2e-headless-8
test-e2e-headless-8:
	make e2e8p

npm-package-install:
	cd views/assets && npm i && npm run build
