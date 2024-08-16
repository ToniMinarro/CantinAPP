UID=$(shell id -u)
GID=$(shell id -g)
DOCKER_PHP_SERVICE=php-fpm
DOCKER_DB_SERVICE=postgres
DOCKER_DOC_SERVICE=node
DOCKER_DB_PORT=5432

init: erase cache-folders build composer-install start

erase:
		docker compose down -v

build:
		docker compose build --no-cache && \
		docker compose pull

cache-folders:
		mkdir -p ~/.composer && chown ${UID}:${GID} ~/.composer

composer-install:
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} composer install

start:
		docker compose up -d

stop:
		docker compose stop

bash:
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} sh

logs:
		docker compose logs -f ${DOCKER_PHP_SERVICE}

db: ## recreate database
		docker compose run -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} sh -lc 'while ! nc -z ${DOCKER_DB_SERVICE} ${DOCKER_DB_PORT}; do echo "Waiting for DB service"; sleep 3; done;'
		docker compose run -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} sh -lc './bin/console pccom:environment:init'

grumphp:
		docker compose exec -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} grumphp run

test:
ifdef SUITE
		docker compose exec -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} behat --config behat.yml.dist --suite $(SUITE)
else
		docker compose exec -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} behat --config behat.yml.dist
endif

.PHONY: docs
docs:
		docker compose run --rm -u ${UID}:${GID} ${DOCKER_DOC_SERVICE} sh -lc 'openapi-merger -i ./docs/asyncapi.yaml  -o ./docs/asyncapi_merged.yaml && openapi-merger -i ./docs/openapi.yaml  -o ./docs/openapi_merged.yaml'
