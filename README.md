# MeraShop

Online store on Symfony.

## Структура проекта

The project is organized using modular architecture (Modular Monolith) in the directory `src/`:
- `EmailSender` - module for sending notifications.
- `Users` - user management module (registration, authorization).
- `Shared` - common components used between modules (Domain, Infrastructure, Application).

Each module follows the principles of DDD (Domain-Driven Design) and has a clear separation of layers:
- `Domain` - business logic and entities.
- `Application` - services and commands.
- `Infrastructure` - implementation of interfaces, databases, external APIs.
- `Presentation` - controllers and CLI commands.

## Table of Contents

- [Quick Start](#quick-start)
  - [Preparing the environment](#1-preparing-the-environment) 
  - [Project deployment](#2-project-deployment)
  - [Access to the application](#3-access-to-the-application)
- [Development Workflow](#development-workflow)

## Quick Start

### 1. Preparing the environment
Add local domains to your file `hosts` (`/etc/hosts` on Linux/macOS or `C:\Windows\System32\drivers\etc\hosts` on Windows):

```bash
127.0.0.1 merashop.test
127.0.0.1 api.merashop.test
127.0.0.1 admin-api.merashop.test
127.0.0.1 sources.merashop.test
```

### 2. Project deployment
For quick project initialization, use `Makefile`:

```bash
make init
```

This command:
- Copy `.env` to `.env.local` (if not exists).
- Will assemble and launch Docker containers.
- Install dependencies via Composer.
- Generate `APP_SECRET` in `.env.local`.
- ~~Perform database migrations.~~

### 3. Access to the application
- Web: [merashop.test](http://merashop.test)
- Public API: [api.merashop.test](http://api.merashop.test)
- Admin API: [admin-api.merashop.test](http://admin-api.merashop.test)
- Sources: [sources.merashop.test](http://sources.merashop.test)


## Development Workflow
### Code Quality Tools

**Enabled GrumPHP tasks:**

1. GrumPHP [:octocat: GitHub](https://github.com/phpro/grumphp)
2. [CS Fixer](https://cs.symfony.com/) [:octocat: GitHub](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)
3. [PHPStan](https://phpstan.org/user-guide/getting-started) [:octocat: GitHub](https://github.com/phpstan/phpstan)
4. [Deptrac](https://deptrac.github.io/deptrac/) [:octocat: GitHub](https://github.com/deptrac/deptrac)

**Manual execution examples in container:**

- **All tasks**:

  ```bash
  php vendor/bin/grumphp run -n
  ```

- **CS task** with the progress bar:

  ```bash
  php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --ansi --no-interaction
  ```

- **PHPStan task** with the progress bar:

  ```bash
  php vendor/bin/phpstan analyse --configuration=phpstan.dist.neon --memory-limit=-1 --no-ansi --no-interaction
  ```

- **Deptrac tasks** with the progress bar:

  ```bash
  php vendor/bin/deptrac analyse --config-file=deptrac.yaml
  php vendor/bin/deptrac analyse --config-file=deptrac-modules.yaml
  ```
