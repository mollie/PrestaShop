ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

# target: fix-lint			- Launch php cs fixer
fix-lint:
	docker-compose run --rm php sh -c "vendor/bin/php-cs-fixer fix --using-cache=no"

#PS1784
e2eh1784:
	# detaching containers
	docker-compose -f docker-compose.1784.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.1784.yml ps
	# waits for mysql to load
	/bin/bash .docker/wait-for-container.sh mysql-mollie-1784
	# configuring your prestashop
	docker exec -i prestashop-mollie-1784 sh -c "rm -rf /var/www/html/install"
	# configuring base database
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_1784_2.sql
	# installing module
	docker exec -i prestashop-mollie-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# uninstalling module
	docker exec -i prestashop-mollie-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall mollie"
	# installing the module again
	docker exec -i prestashop-mollie-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# chmod all folders
	docker exec -i prestashop-mollie-1784 sh -c "chmod -R 777 /var/www/html"

#PS8
e2eh8:
	# detaching containers
	docker-compose -f docker-compose.8.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.8.yml ps
	# waits for mysql to load
	/bin/bash .docker/wait-for-container.sh mysql-mollie-8
	# configuring your prestashop
	docker exec -i prestashop-mollie-8 sh -c "rm -rf /var/www/html/install"
	# configuring base database
	mysql -h 127.0.0.1 -P 9459 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_8.sql
	# installing module
	docker exec -i prestashop-mollie-8 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# uninstalling module
	docker exec -i prestashop-mollie-8 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall mollie"
	# installing the module again
	docker exec -i prestashop-mollie-8 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# enabling the module
	docker exec -i prestashop-mollie-8 sh -c "cd /var/www/html && php  bin/console prestashop:module enable mollie --id_shop=2"
	# chmod all folders
	docker exec -i prestashop-mollie-8 sh -c "chmod -R 777 /var/www/html"

npm-package-install:
	cd views/assets && npm i && npm run build

run-e2e-tests-locally:
	npm install -D cypress
	npm ci
	npx cypress run

# checking the module upgrading - installs older module then installs from master branch
upgrading-module-test-1784:
	git fetch
	git checkout v5.2.0 .
	composer install
	# installing 5.2.0 module
	docker exec -i prestashop-mollie-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# installing develop branch module
	git checkout -- .
	git checkout develop --force
	docker exec -i prestashop-mollie-1784 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
