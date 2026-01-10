# MeraShop

Online store on Symfony.

## Project structure

The project is organized using modular architecture (Modular Monolith) in the directory `src/`:
- `EmailSender` - module for sending notifications.
- `Users` - user management module (registration, authorization).
- `Shared` - common components used between modules (Domain, Infrastructure, Application).

### Domain Structure

Each module follows the principles of DDD (Domain-Driven Design) and has a clear separation of layers:
- `Domain` - business logic and entities.
    - **Value Objects (VO)**: Use VO for all properties to ensure type safety and validation.
        - Model-specific VOs are grouped in subfolders named after their entities (e.g., `ValueObject/UserAccount/EmailAddress.php`).
        - Every VO must have a specific domain exception.
    - **Exceptions**: Each module follows a strict exception hierarchy:
        - `Throwable{Module}Exception` (interface) - module marker.
        - `{Module}DomainException` (abstract class) - base domain exception.
        - `Invalid{Module}ValueObjectException` (abstract class) - base exception for all VOs in the module.
        - Specific VO exceptions (e.g., `InvalidUserAccountEmailException`) must inherit from the base VO exception.
- `Application` - services and commands.
- `Infrastructure` - implementation of interfaces, databases, external APIs.
- `Presentation` - controllers and CLI commands.
- `/config/modules/*` - module-specific configurations collected by `Kernel.php`.

## Table of Contents

- [Quick Start](#quick-start)
    - [Preparing the environment](#1-preparing-the-environment)
    - [Project deployment](#2-project-deployment)
    - [Access to the application](#3-access-to-the-application)
- [Development Workflow](#development-workflow)
- [Infrastructure & Docker](#infrastructure--docker)
- [Observability](#observability)

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

## Infrastructure & Docker

The project uses custom Docker images for key services to ensure consistency across different environments and to fix specific infrastructure issues (like permissions or pre-bundled configs).

### Custom Images Principles
- **Versioning**: We use fixed versions for base images to prevent "it works on my machine" issues.
- **Caching**: Dockerfiles are optimized for build speed by layering dependencies separately from the application code.
- **Portability**: All configuration files required for a service to run are bundled within the image or managed via environment variables.

For more details on infrastructure standards, see [AGENTS.md](./AGENTS.md#7-infrastructure--docker).

## Observability

The project uses ELK stack (Elasticsearch, Logstash/Filebeat, Kibana) for logging.

### Access to logs
- Kibana: [localhost:5601](http://localhost:5601)

### Local Development and Testing
To test the production-like logging locally:
1. Ensure the `config/packages/monolog.yaml` file `when@dev.monolog.handlers.main.formatter` option has `monolog.formatter.json` value.
2. Ensure the `filebeat` container is running.
3. Check logs in Kibana. By default, Symfony logs to `var/log/dev.log`, and Filebeat reads it.

### Features
- **TraceId**: Each request is assigned a unique `TraceId`, which is automatically added to all log entries via `TraceIdProcessor`. This allows tracing the entire lifecycle of a request across different modules.
- **JSON Logging**: In the `prod` environment, logs are formatted as JSON for easy ingestion by Filebeat.
- **Dedicated Channels**: Modules use separate logging channels (e.g., `email_sender`) to simplify filtering.
