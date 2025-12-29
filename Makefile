.PHONY: help up down build app-sh grumphp php-cs-fixer phpstan test-prepare test
.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

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

test-prepare:
	docker compose exec app php bin/console doctrine:database:drop --env=test --force --if-exists
	docker compose exec app php bin/console doctrine:database:create --env=test
	docker compose exec app php bin/console doctrine:migrations:migrate --env=test --no-interaction

test:
	docker compose exec app vendor/bin/phpunit
