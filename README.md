# MeraShop

Online store on Symfony.

## Table of Contents

- [Project Structure](#project-structure)
- [Databases & Migrations](#databases--migrations)
  - [Create or sync databases](#create-or-sync-databases)
  - [Running migrations](#running-migrations)
  - [Making migrations](#making-migrations)
- [Quick Start](#quick-start)
  - [Preparing the environment](#1-preparing-the-environment)
  - [Project deployment](#2-project-deployment)
  - [Access to the application](#3-access-to-the-application)
- [Development Workflow](#development-workflow)
- [Tests](#tests)
- [Infrastructure & Docker](#infrastructure--docker)
- [Observability](#observability)

## Project structure

The project is organized using modular architecture (Modular Monolith) in the directory `src/`:
- `Catalog` - products, categories, and attributes in the store.
- `Customer` - customer profiles and related domain logic.
- `EmailSender` - module for sending notifications.
- `IdentityAccess` - user and modules management, registration, and authorization.
- `Shared` - common components used between modules (Domain, Infrastructure, Application).

### Domain Structure

Each module follows the principles of DDD (Domain-Driven Design) and has a clear separation of layers:
- `Domain` - business logic and entities.
    - **Value Objects (VO)**: Use VO for all properties to ensure type safety and validation.
        - Model-specific VOs are grouped in subfolders named after their entities (e.g., `ValueObject/UserAccount/EmailAddress.php`).
        - Every VO must have a specific domain exception.
    - **Exceptions**: Each module follows a strict exception hierarchy:
        - `AppExceptionInterface` (interface, in `Shared`) - base interface for all application exceptions.
        - `ServerException` (abstract class, in `Shared`) - base exception with `getErrorCode()` method.
        - `{Module}ExceptionInterface` (interface) - module marker.
        - `{Module}DomainException` (abstract class) - base domain exception, inherits from `LogicException` or `ServerException`.
        - `Invalid{Module}ValueObjectException` (abstract class) - base exception for all VOs in the module.
        - Specific VO exceptions (e.g., `InvalidUserAccountEmailException`) must inherit from the base VO exception.
    - **Exception Handling**: Each module contains its own exception handler (e.g., `src/IdentityAccess/Presentation/Http/EventListener/ApiIdentityAccessExceptionListener.php`) to manage module-specific error responses and maintain independence.
- `Application` - services and commands.
- `Infrastructure` - implementation of interfaces, databases, external APIs.
- `Presentation` - entry points to the module.
    - `Http` - controllers, requests, resources, and HTTP-specific event listeners.
    - `Console` - CLI commands, and Console-specific event listeners.
- `/config/modules/*` - module-specific configurations collected by `Kernel.php`.

### Directory Structure

Every module within `src/` must follow this standardized structure:

- **Domain**: Contains the core business logic.
    - `Entity` - domain entities with business logic.
    - `Enum` - common enumerations
    - `Event` - domain events.
    - `Exception` - domain exceptions and marker-interfaces.
    - `Repository` - interfaces (definitions only).
    - `Service` - domain services (simple implementations without external dependencies, interfaces).
    - `ValueObject` - value objects (primitives).
- **Application**: Contains use cases and orchestration.
    - `Command` - application commands and their handlers.
    - `DTO` (Data Transfer Objects).
    - `EventHandler` - application event listeners.
    - `Query` - application queries and their handlers.
    - `Scheduler` - application schedulers (cron tasks).
    - `Exception` - application exceptions.
    - `Service` - application services (simple implementations without external dependencies)
- **Infrastructure**: External concerns and technical implementations.
    - `Persistence` - database access.
        - `Doctrine` - ORM implementation.
            - `Entity` - ORM entities.
            - `Mapper` - ORM <=> Domain mappers. Explicit field definitions
            - `Migrations` - database migrations.
            - `Repository` - implementations of domain repository interfaces.
            - `Type` - custom DB datatypes.
    - `Scheduler` - scheduler provider with configuration.
    - `Service` - infrastructure services (complex implementations with external dependencies).
    - `Adapter` for external services.
- **Presentation**: Entry points to the module.
    - `Console` - CLI commands, and Console-specific event listeners.
        - `EventListener` - specific event listeners (Console command/response)
    - `Http` - Web API controllers, requests, resources, and HTTP-specific event listeners.
        - `AdminApiVersion<N>` - admin API versioning.
        - `ApiVersion<N>` - API versioning.
            - `Controller` - API controllers.
            - `Request` - API requests and validation.
            - `Resource` - API resources and normalizers.
        - `Web` - Http pages and views.
        - `config` - API routing configuration.
        - `EventListener` - specific event listeners (API request/response, KernelExceptions etc.).

The translation folder can be located in various places (but correct ones) and named `translations`.
Also, every module can have its own specific folders which are not listed above. (e.g. `src/EmailSender/Infrastructure/Resources`, `src/EmailSender/Infrastructure/Mailer`)

- **Shared** module
    - **Domain** layer
        - **Criteria**: Logic for filtering, sorting, and pagination (Filtering, Sorting, Paging).

## Databases & Migrations

The project uses database isolation at the module level. Each module has its own connection and entity manager.

### Create or sync databases

When a new module database is added and the PostgreSQL data volume already exists, the entrypoint init scripts will not run automatically. To create missing databases, run:

```bash
docker compose exec -T pgsql bash /docker-entrypoint-initdb.d/init-db.sh
```

### Remove old module databases

If a module was removed (e.g., `Users`) and its database still exists in the PostgreSQL volume, drop it manually.

1. Check existing databases:
    ```bash
    docker compose exec -T pgsql bash -lc 'psql -U "$POSTGRES_USER" -d postgres -c "\\l"'
    ```

2. Terminate active connections to the target database (replace `users_db` with the real name):
    ```bash
    docker compose exec -T pgsql bash -lc 'psql -U "$POSTGRES_USER" -d postgres -c "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = users_db;"'
    ```

3. Drop the database:
    ```bash
    docker compose exec -T pgsql bash -lc 'psql -U "$POSTGRES_USER" -d postgres -c "DROP DATABASE users_db;"'
    ```

**Windows (PowerShell) note:** `$POSTGRES_USER` is inside the container, so PowerShell will not expand it. Either pass the user explicitly:
```powershell
docker compose exec -T pgsql psql -U merashop_u -d postgres -c "DROP DATABASE users_db;"
```
or use PowerShell verbatim mode to avoid quote parsing:
```powershell
docker compose exec -T pgsql --% bash -lc "psql -U "$POSTGRES_USER" -d postgres -c 'DROP DATABASE users_db;'"
```

If you also removed the module’s configuration, ensure the related `POSTGRES_DB_*` env variable is deleted from `.env` / `.env.local` and from `docker/pgsql/shell/init-db.sh` to prevent recreation.

### Running Migrations

Migrations are run separately for each module using their respective entity managers and configurations:

**Catalog:**
```bash
php bin/console doctrine:migrations:migrate --em=catalog --configuration=config/migrations/catalog.php --no-interaction
```

**Customer:**
```bash
php bin/console doctrine:migrations:migrate --em=customer --configuration=config/migrations/customer.php --no-interaction
```

**EmailSender:**
```bash
php bin/console doctrine:migrations:migrate --em=email_sender --configuration=config/migrations/email_sender.php --no-interaction
```

**IdentityAccess:**
```bash
php bin/console doctrine:migrations:migrate --em=identity_access --configuration=config/migrations/identity_access.php --no-interaction
```

### Making Migrations

Make migration files for each module using their respective entity managers and configurations:

**Catalog:**
```bash
php bin/console doctrine:migrations:diff --em=catalog --configuration=config/migrations/catalog.php --no-interaction
```

**Customer:**
```bash
php bin/console doctrine:migrations:diff --em=customer --configuration=config/migrations/customer.php --no-interaction
```

**EmailSender:**
```bash
php bin/console doctrine:migrations:diff --em=email_sender --configuration=config/migrations/email_sender.php --no-interaction
```

**IdentityAccess:**
```bash
php bin/console doctrine:migrations:diff --em=identity_access --configuration=config/migrations/identity_access.php --no-interaction
```

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
### ORM & Mapping Standards

- **Table Names**: Table names should not include module prefixes (e.g., use `products` instead of `catalog_products`).
  - **Naming Convention for Constraints**:
    - **Indexes**: `idx_{table}_{column}` (e.g., `idx_products_sku`).
    - **Unique Indexes**: `uniq_{table}_{column}` (e.g., `uniq_products_ulid`).
    - **Foreign Keys (FK)**: `fk_{table}_{column}` (e.g., `fk_product_translations_product_id`).
    - **63 Characters Limit**: PostgreSQL has a limit of 63 characters for identifier names. If a constraint name exceeds this limit:
      1. Use table abbreviations (e.g., `identity_access_messages` -> `ia_msg`, `messages` -> `msg`, `translations` -> `trans`, `attribute` -> `attr`).
      2. If still too long, truncate longest parts while maintaining uniqueness.
- **Explicit Names**: Always provide explicit names for all indexes, unique constraints, and foreign keys.
  - For unique constraints: `#[ORM\UniqueConstraint(name: 'uniq_...', columns: [...])]`.
  - For indexes: `#[ORM\Index(name: 'idx_...', columns: [...])]`.
  - **NOTE**: Doctrine migrations currently ignore foreign key names in ORM attributes. You MUST manually set the desired FK name in the migration file.
- **Field Lengths**: Define length limits in Value Objects as `MAX_LENGTH` constants and use them in ORM column definitions (e.g., `length: Sku::MAX_LENGTH`). This ensures a single source of truth for business constraints and database schema.

### Repository Standards

- **Read/Write Separation**: Repository interfaces are split into **Read** and **Write** interfaces (CQRS at the persistence level).
- **Read Repositories**: Must implement a private `checkAndMapToDomain` method to handle ORM-to-Domain mapping with proper type checking and null handling.
- **Write Repositories**: Must use `WriteRepositoryTrait` for standard `save` and `delete` operations to ensure consistency and reduce boilerplate.
- **Base Classes**: All repository implementations must inherit from an entity-specific base class (e.g., `BaseProductRepository`) that encapsulates the `Mapper` and `ManagerRegistry`.
- **Entities**:
    - **Every entity**
        - must have a mapper class implementing `App\Shared\Infrastructure\Persistence\Doctrine\Mapping\EntityMapperInterface`.
        - should reuse existing infrastructure helpers (e.g., `App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait`) to avoid duplication.
    - **Complex entities**
        - (many relations, collections, translations, or special mapping rules)
        - MUST use dependency injection of `App\Shared\Infrastructure\Persistence\Doctrine\Interface\ProxyReferenceProviderInterface`
        - register interface implementation in a config file (e.g. `config/modules/catalog.yaml`).

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

## Tests

The project uses PHPUnit for testing. Tests are organized by module to support the Modular Monolith architecture.

**Automatic Database Preparation:**
The project is configured to automatically migrate all module databases before running tests. This is handled by `tests/bootstrap.php`. When you run `phpunit`, it will:
1. Load the test environment.
2. Run migrations for all entity managers currently configured in `tests/bootstrap.php` (`customer`, `email_sender`, `identity_access`, `catalog`).
3. Ensure the databases are ready for testing.

**Run all tests:**
```bash
php vendor/bin/phpunit
```

**Run tests for a specific module:**
```bash
php vendor/bin/phpunit --testsuite identity_access
```

**Test Structure:**
- `tests/{ModuleName}/Unit` - Logic and Domain tests.
- `tests/{ModuleName}/Integration` - Infrastructure and Persistence tests.
- `tests/{ModuleName}/Functional` - Application and API tests.
- `tests/{ModuleName}/Support` - Test mothers, fixtures, and utilities.

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

#### Logging
To test the production-like logging locally:
1. Ensure the `config/packages/monolog.yaml` file `when@dev.monolog.handlers.main.formatter` option has `monolog.formatter.json` value.
2. Ensure the `filebeat` container is running.
3. Check logs in Kibana. By default, Symfony logs to `var/log/dev.log`, and Filebeat reads it.

#### Writing tests

##### Object Mother Pattern
To minimize edits when changing domain models, "Mother" classes are used(`tests/{Module}/Support/{Entity}Mother.php`):
 - `createWithData()` **(static)**: For unit tests. Does not require Kernel, uses hardcoded valid data.
 - `create()` **(instance)**: For integration/functional tests. Requires DI, uses Faker and factories.

### Features
- **TraceId**: Each request is assigned a unique `TraceId` (**UUID v7**), which is automatically added to all log entries via `TraceIdProcessor`. This allows tracing the entire lifecycle of a request across different modules.
- **JSON Logging**: In the `prod` environment, logs are formatted as JSON for easy ingestion by Filebeat.
- **Dedicated Channels**: Modules use separate logging channels (e.g., `email_sender`) to simplify filtering.
