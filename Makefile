# Makefile
start:
	docker-compose up -d

stop:
	docker-compose down

build:
	docker-compose up --build -d

composer-install:
	docker-compose run --rm app composer install

composer-update:
	docker-compose run --rm app composer update

test:
	docker-compose run --rm app vendor/bin/phpunit

ssh:
	docker-compose exec app bash