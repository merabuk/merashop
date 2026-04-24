.PHONY: help init up down build app-sh node-sh node-sync-types grumphp php-cs-fixer phpstan node-lint node-type-check markdownlint test
.DEFAULT_GOAL := help
SHELL := /usr/bin/env bash
SCRIPT_DIR := ./scripts

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

init: ## Initialize the project (copy .env, generate secrets, etc.)
	@if [ ! -f .env.local ]; then cp .env .env.local; echo ".env.local created"; fi
	@$(MAKE) build
	@$(MAKE) up -d
	@docker compose exec app composer install
	@docker compose exec app php -r "if (!getenv('APP_SECRET')) echo 'APP_SECRET=' . bin2hex(random_bytes(16)) . PHP_EOL;" >> .env.local
	@echo "APP_SECRET generated in .env.local if it was missing"
	@$(MAKE) migrate

up:
	docker compose up -d

migrate:
	docker compose exec app php bin/console doctrine:migrations:migrate --em=catalog --configuration=config/migrations/catalog.php --no-interaction
	docker compose exec app php bin/console doctrine:migrations:migrate --em=customer --configuration=config/migrations/customer.php --no-interaction
	docker compose exec app php bin/console doctrine:migrations:migrate --em=email_sender --configuration=config/migrations/email_sender.php --no-interaction
	docker compose exec app php bin/console doctrine:migrations:migrate --em=identity_access --configuration=config/migrations/identity_access.php --no-interaction

down:
	docker compose down

build:
	docker compose build

app-sh:
	docker compose exec app sh

node-sh:
	docker compose exec node sh

node-sync-types:
	@echo "Syncing node_modules from container to host..."
	docker cp $$(docker compose ps -q node):/app/node_modules ./
	@echo "Sync complete. IDE index may take a moment to update."

grumphp:
	docker compose exec app php vendor/bin/grumphp run -n

php-cs-fixer:
	docker compose exec app php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --ansi --no-interaction

phpstan:
	docker compose exec app php vendor/bin/phpstan analyse --configuration=phpstan.dist.neon --memory-limit=-1 --no-ansi --no-interaction

node-lint:
	docker compose exec node npm run lint

node-type-check:
	docker compose exec node npm run type-check

markdownlint:
	docker compose exec node ./node_modules/.bin/markdownlint --fix --ignore vendor .

test:
	docker compose exec app vendor/bin/phpunit

