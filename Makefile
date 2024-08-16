UID=$(shell id -u)
GID=$(shell id -g)
DOCKER_PHP_SERVICE=php
DOCKER_DB_SERVICE=mysql
DOCKER_DB_PORT=3306

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
