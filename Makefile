ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

# target: fix-lint			- Launch php cs fixer
fix-lint:
	docker-compose run --rm php sh -c "vendor/bin/php-cs-fixer fix --using-cache=no"

# All the commands required to build prestashop-17 version locally
bps17: build-ps-17
build-ps-17:
	# configuring your prestashop
	docker exec -i prestashop-17 sh -c "rm -rf /var/www/html/install"
	# configuring base database
	mysql -h 127.0.0.1 -P 9001 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_17.sql
	# installing module
	docker exec -i prestashop-17 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# uninstalling module
	docker exec -i prestashop-17 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall mollie"
	# installing the module again
	docker exec -i prestashop-17 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# chmod all folders
	docker exec -i prestashop-17 sh -c "chmod -R 777 /var/www/html"

# Preparing prestashop-17 for e2e tests - this actually launched an app in background. You can access it already!
e2e17p: e2e-17-prepare
e2e-17-prepare:
	# detaching containers
	docker-compose up -d --force-recreate
	# sees what containers are running
	docker-compose ps
	# waits for mysql to load
	/bin/bash .docker/wait-for-container.sh mollie-mysql
	# preloads initial data
	make bps17
	/bin/bash .docker/wait-for-container.sh prestashop-17

# Run e2e tests in headless way.
e2eh: test-e2e-headless
test-e2e-headless:
	make e2e17p

# Run e2e tests with graphical interface ( usually you can skip building since its likely you already done, only execute docker-compose command below )
e2eg: test-e2e-gui
test-e2e-gui:
	make e2e17p
	# this should work out of the box for all linux users.
	docker-compose -f docker-compose.e2e.yml -f docker-compose.e2e.local.yml up --force-recreate --exit-code-from cypress

run-wiremock-local:
	docker run -ti --rm -v $(ROOT_DIR)/wiremock:/home/wiremock -p 8443:8080 wiremock/wiremock

############ PS1784 ############################

# All the commands required to build prestashop-1784 version locally
bps1784: build-ps-1784
build-ps-1784:
	# configuring your prestashop
	docker exec -i prestashop-1784 sh -c "rm -rf /var/www/html/install"
	# configuring base database
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_1784_2.sql
	# installing module
	docker exec -i prestashop-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# uninstalling module
	docker exec -i prestashop-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall mollie"
	# installing the module again
	docker exec -i prestashop-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# chmod all folders
	docker exec -i prestashop-1784 sh -c "chmod -R 777 /var/www/html"

# Preparing prestashop-1784 for e2e tests - this actually launched an app in background. You can access it already!
e2e1784p: e2e-1784-prepare
e2e-1784-prepare:
	# detaching containers
	docker-compose -f docker-compose.1784.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.1784.yml ps
	# waits for mysql to load
	/bin/bash .docker/wait-for-container.sh mollie-mysql-1784
	# preloads initial data
	make bps1784
	/bin/bash .docker/wait-for-container.sh prestashop-1784

# Run e2e tests in headless way.
e2eh1784: test-e2e-headless-1784
test-e2e-headless-1784:
	make e2e1784p