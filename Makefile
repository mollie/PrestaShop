ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

VERSION1785 ?= 1785

# target: fix-lint			- Launch php cs fixer
fix-lint:
	docker-compose run --rm php sh -c "vendor/bin/php-cs-fixer fix --using-cache=no"


#PS1785 for local machine docker build with PS autoinstall
e2eh$(VERSION1785)_local:
	# detaching containers
	docker-compose -f docker-compose.$(VERSION1785).yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.$(VERSION1785).yml ps
	# waiting for app containers to build up
	/bin/bash .docker/wait-loader.sh 8002
	# seeding the customized settings for PS
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_$(VERSION1785)_2.sql
	# installing module
	docker exec -i prestashop-mollie-$(VERSION1785) sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# uninstalling module
	docker exec -i prestashop-mollie-$(VERSION1785) sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall mollie"
	# installing the module again
	docker exec -i prestashop-mollie-$(VERSION1785) sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# enabling the module
	docker exec -i prestashop-mollie-$(VERSION1785) sh -c "cd /var/www/html && php  bin/console prestashop:module enable mollie"
	# chmod all folders
	docker exec -i prestashop-mollie-$(VERSION1785) sh -c "chmod -R 777 /var/www/html"

#PS1785 for CI build with PS autoinstall
e2eh1785:
	# detaching containers
	docker-compose -f docker-compose.1785.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.1785.yml ps
	# waiting for app containers to build up
	sleep 90s
	# configuring base database
	mysql -h 127.0.0.1 -P 9002 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_1785_2.sql
	# installing module
	docker exec -i prestashop-mollie-1785 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# uninstalling module
	docker exec -i prestashop-mollie-1785 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall mollie"
	# installing the module again
	docker exec -i prestashop-mollie-1785 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# enabling the module
	docker exec -i prestashop-mollie-1785 sh -c "cd /var/www/html && php  bin/console prestashop:module enable mollie"
	# chmod all folders
	docker exec -i prestashop-mollie-1785 sh -c "chmod -R 777 /var/www/html"

#PS8 for local machine docker build with PS autoinstall
e2eh8_local:
	# detaching containers
	docker-compose -f docker-compose.8.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.8.yml ps
	# waiting for app containers to build up
	/bin/bash .docker/wait-loader.sh 8142
	# seeding the customized settings for PS
	mysql -h 127.0.0.1 -P 9459 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_8.sql
	# installing module
	docker exec -i prestashop-mollie-8 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# uninstalling module
	docker exec -i prestashop-mollie-8 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall mollie"
	# installing the module again
	docker exec -i prestashop-mollie-8 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# enabling the module
	docker exec -i prestashop-mollie-8 sh -c "cd /var/www/html && php  bin/console prestashop:module enable mollie"
	# chmod all folders
	docker exec -i prestashop-mollie-8 sh -c "chmod -R 777 /var/www/html"

#PS8 for CI build with PS autoinstall
e2eh8:
	# detaching containers
	docker-compose -f docker-compose.8.yml up -d --force-recreate
	# sees what containers are running
	docker-compose -f docker-compose.8.yml ps
	# waiting for app containers to build up
	sleep 90s
	# seeding the customized settings for PS
	mysql -h 127.0.0.1 -P 9459 --protocol=tcp -u root -pprestashop prestashop < ${PWD}/tests/seed/database/prestashop_8.sql
	# installing module
	docker exec -i prestashop-mollie-8 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# uninstalling module
	docker exec -i prestashop-mollie-8 sh -c "cd /var/www/html && php  bin/console prestashop:module uninstall mollie"
	# installing the module again
	docker exec -i prestashop-mollie-8 sh -c "cd /var/www/html && php  bin/console prestashop:module install mollie"
	# enabling the module
	docker exec -i prestashop-mollie-8 sh -c "cd /var/www/html && php  bin/console prestashop:module enable mollie"
	# chmod all folders
	docker exec -i prestashop-mollie-8 sh -c "chmod -R 777 /var/www/html"

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
