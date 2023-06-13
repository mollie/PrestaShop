ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

# target: fix-lint			- Launch php cs fixer
fix-lint:
	docker-compose run --rm php sh -c "vendor/bin/php-cs-fixer fix --using-cache=no"

#PS1784
e2eh1784: test-e2e-headless-1784
test-e2e-headless-1784:
	git checkout v5.2.1
	composer install
	make e2e1784p
	git checkout -- .
	git checkout master --force

e2e1784p: e2e-1784-prepare
e2e-1784-prepare:
	# detaching containers
	docker-compose -f docker-compose.1784.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.1784.yml ps
	# waits for mysql to load
	/bin/bash .docker/wait-for-container.sh mysql-mollie-1784
	# preloads initial data
	make bps1784
	/bin/bash .docker/wait-for-container.sh prestashop-mollie-1784

bps1784: build-ps-1784
build-ps-1784:
	# configuring your prestashop
	docker exec -i prestashop-mollie-1784 sh -c "rm -rf /var/www/html/install"
	# configuring base database
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_1784_2.sql
	# chmod all folders
	docker exec -i prestashop-mollie-1784 sh -c "chmod -R 777 /var/www/html"

#PS8
e2eh8: test-e2e-headless-8
test-e2e-headless-8:
	git checkout v5.2.1
	composer install
	make e2e8p
	git checkout -- .
	git checkout master --force

e2e8p: e2e-8-prepare
e2e-8-prepare:
	# detaching containers
	docker-compose -f docker-compose.8.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.8.yml ps
	# waits for mysql to load
	/bin/bash .docker/wait-for-container.sh mysql-mollie-8
	# preloads initial data
	make bps8

bps8: build-ps-8
build-ps-8:
	# configuring your prestashop
	docker exec -i prestashop-mollie-8 sh -c "rm -rf /var/www/html/install"
	# configuring base database
	mysql -h 127.0.0.1 -P 9459 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_8.sql
	# chmod all folders
	docker exec -i prestashop-mollie-8 sh -c "chmod -R 777 /var/www/html"

installing-older-module:
	git checkout 5.0.0
	docker exec -i prestashop-mollie-${version} sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"

npm-package-install:
	cd views/assets && npm i && npm run build
