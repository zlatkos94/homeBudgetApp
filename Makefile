
PHP_CONTAINER=symfony_php

.PHONY: up bash install reset-db fixtures migrate test

up:
	docker-compose up -d --build

bash:
	docker exec -it $(PHP_CONTAINER) bash

install:
	docker exec -it $(PHP_CONTAINER) composer install

reset-db:
	docker exec -it $(PHP_CONTAINER) bash -c "\
		php bin/console doctrine:database:drop --force && \
		php bin/console doctrine:database:create && \
		php bin/console doctrine:migrations:migrate \
	"

fixtures:
	docker exec -it $(PHP_CONTAINER) php bin/console doctrine:fixtures:load --no-interaction

test:
	docker exec -it $(PHP_CONTAINER) php bin/phpunit
