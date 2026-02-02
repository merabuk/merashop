.PHONY: help up down build app-sh grumphp php-cs-fixer phpstan test-prepare test init
.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

init: ## Initialize the project (copy .env, generate secrets, etc.)
	@if [ ! -f .env.local ]; then cp .env .env.local; echo ".env.local created"; fi
	@$(MAKE) build
	@$(MAKE) up -d
	@docker compose exec app composer install
	@docker compose exec app php -r "if (!getenv('APP_SECRET')) echo 'APP_SECRET=' . bin2hex(random_bytes(16)) . PHP_EOL;" >> .env.local
	@echo "APP_SECRET generated in .env.local if it was missing"
	@#docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

app-sh:
	docker compose exec app sh

grumphp:
	docker compose exec app php vendor/bin/grumphp run -n

php-cs-fixer:
	docker compose exec app php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --ansi --no-interaction

phpstan:
	docker compose exec app php vendor/bin/phpstan analyse --configuration=phpstan.dist.neon --memory-limit=-1 --no-ansi --no-interaction

test:
	docker compose exec app vendor/bin/phpunit
