DC = docker-compose
DC_RUN = $(DC) run --rm app
DC_EXEC = $(DC) exec app

.PHONY: start stop build composer-install composer-update test ssh

# Starts the containers in the background
start:
	$(DC) up -d

# Stops and removes the containers
stop:
	$(DC) down

# Builds and starts the containers in the background
build:
	$(DC) up --build -d

# Installs dependencies using Composer
composer-install:
	$(DC_RUN) composer install

# Updates dependencies using Composer
composer-update:
	$(DC_RUN) composer update

# Runs PHPUnit tests
test:
	$(DC_RUN) vendor/bin/phpunit

# Connects to the app container via SSH
ssh:
	$(DC_EXEC) bash

