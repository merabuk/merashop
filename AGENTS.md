# AI Agent Guidelines (AGENTS.md)

This document serves as the primary system instruction for AI Agents (like Junie or others) working on this project. It defines the architectural principles, coding standards, and interaction rules to ensure consistency and quality across the modular monolith.

## 1. Project Philosophy

### Modular Monolith & DDD
The project is built as a **Modular Monolith** following **Domain-Driven Design (DDD)** principles.

The system is divided into high-level modules located in `src/`:
- `Catalog` - products, categories, and attributes in the store.
- `Customer` - customer profiles and related domain logic.
- `EmailSender` - module for sending notifications.
- `IdentityAccess` - user and modules management, registration, and authorization.
- `Shared` - common components used between modules (Domain, Infrastructure, Application).

- **Isolation**:
  - Each module must be self-contained.
  - Direct calls between modules are strictly forbidden.
  - This isolation extends to the database level: each module must have its own database schema (or a separate database) and its own entity manager.
- **Communication**:
  - Inter-module communication is handled exclusively via the `Shared` module or through **Events** (Asynchronous or Synchronous via Symfony Messenger).
- **Enforcement**:
  - **Deptrac** is used to monitor and enforce layer boundaries and dependency rules.
- **Module Independence**:
  - Every module must be independent.
  - The `Shared` layer is the only exception, providing reusable components. However, `Shared` must only contain primitive logic, base interfaces, and cross-cutting concerns (e.g., `TraceId` which is a **UUID v7**, `ValueObjects` used by multiple modules) to maintain strict decoupling.

## 2. Directory Structure

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

## 3. Coding Standards & Constraints

### Domain Layer
- **Pure PHP**: No dependencies on Symfony, Doctrine, or any other framework/library.
- **Value Objects (VO)**: Use Value Objects for all domain properties. Avoid primitives (string, int, array) in Entities.
    - Every property should ideally be a VO (e.g., `EmailAddress`, `ClientId`, `RoleCollection`).
    - Collection properties must be wrapped in a Collection VO (e.g., `ScopeCollection`).
    - **VO Exceptions**: Every VO must have a specific domain exception.
    - **MAX_LENGTH**: For string-based VOs, always define a `public const int MAX_LENGTH` and use it both for validation and in ORM mapping (e.g., `#[ORM\Column(type: Types::STRING, length: Sku::MAX_LENGTH)]`). This ensures a single source of truth for field limits.
    - **Exception Hierarchy**: Each module must implement the following structure:
        1. `AppExceptionInterface` (interface, in `Shared`): Base interface for all application exceptions.
        2. `ServerException` (abstract class, in `Shared`): Base exception with `getErrorCode(): string` method.
        3. `ErrorCodeEnum` (enum, in `Shared/Domain/Enum`): Standardized error codes (e.g., `UnexpectedError`, `ValidationFailed`).
        4. `{Module}ExceptionInterface` (interface): Module marker, inherits from `DomainExceptionInterface`.
        5. `{Module}DomainException` (abstract class): Base module exception, inherits from `LogicException` or `ServerException` and implements `{Module}ExceptionInterface`.
        6. `Invalid{Module}ValueObjectException` (abstract class): Base exception for all VOs, inherits from base module exception and implements `ValueObjectExceptionInterface`.
        7. Specific VO exceptions (e.g., `InvalidUserAccountEmailException`) must inherit from `Invalid{Module}ValueObjectException`.
    - **VO Location**: 
        - Model-specific VO must be placed in a subfolder named after the entity (e.g., `src/IdentityAccess/Domain/ValueObject/UserAccount/EmailAddress.php`).
        - Module-shared VO must be placed in the root `ValueObject` folder of the module.
        - Cross-module VO must be placed in `src/Shared/Domain/ValueObject/`.
    - **VO Validation**: VO must ensure their own validity upon creation. Use existing validators from `Shared` or `Domain` if available.
    - **Strongly Typed ULIDs**: Every entity that uses a ULID MUST have its own specific ULID class inheriting from `App\Shared\Domain\ValueObject\Ulid`. This ensures strong typing and entity-specific validation/exceptions. `App\Shared\Domain\ValueObject\Ulid` remains the base class and is primarily used for cross-module identifiers like User Account IDs.
- **Independence**: The domain must remain agnostic of how it is persisted or triggered.
- **Service Interfaces**:
    - Any service that interacts with infrastructure (API, DB, Mailer, etc.) must have an interface in the `Domain` layer and its implementation in the `Infrastructure` layer.
    - **Exception**: Simple stateless services, pure logic helpers, or validators (e.g., `StringHelper`, `StringValidator`) located in `Shared` or `Domain` may exist as final classes without an interface, provided they have no external dependencies.
    - If a service is likely to be mocked in unit tests of other components, prefer using an interface.

### Persistence & Mapping
- **Database Isolation**: Each module MUST use its own dedicated connection and entity manager. Cross-module database queries are strictly forbidden.
- **Mapping Location**: Doctrine mapping must reside strictly within `src/<ModuleName>/Infrastructure/Persistence/Doctrine/Mapping` (XML/PHP) OR within Infrastructure-specific entities (e.g., `Orm*` classes) using PHP attributes.
- **Explicit Definitions**: Avoid using attributes or XML inside the Domain layer. Attributes are permitted only in the Infrastructure layer for ORM entities.
    - **Table Names**: Table names should not include module prefixes. Since each module uses its own database schema or separate database, prefixing is redundant.
    - **Naming Convention for Constraints**:
        - **Indexes**: `idx_{table}_{column}` (e.g., `idx_products_sku`).
        - **Unique Indexes**: `uniq_{table}_{column}` (e.g., `uniq_products_ulid`).
        - **Foreign Keys (FK)**: `fk_{table}_{column}` (e.g., `fk_product_translations_product_id`).
        - **63 Characters Limit**: PostgreSQL has a limit of 63 characters for identifier names. If a constraint name exceeds this limit:
            1. Use table abbreviations. For example:
                - `identity_access_messages` -> `ia_msg`
                - `messages` -> `msg`
                - `translations` -> `trans`
                - `attribute` -> `attr`
            2. If the name is still too long, truncate the longest parts (table or column names) while maintaining uniqueness and readability.
    - **Explicit Names**: Always provide explicit names for all indexes, unique constraints, and foreign keys. 
        - For unique constraints: `#[ORM\UniqueConstraint(name: 'uniq_...', columns: [...])]`.
        - For indexes: `#[ORM\Index(name: 'idx_...', columns: [...])]`.
        - **NOTE**: Foreign key names in ORM attributes (e.g., `options: ['foreignKey' => ['name' => 'fk_...']]`) are currently ignored by Doctrine migrations. You MUST manually set the desired FK name in the migration file.
- **Entities**:
    - **Every entity**
        - must have a mapper class implementing `App\Shared\Infrastructure\Persistence\Doctrine\Mapping\EntityMapperInterface`.
        - should reuse existing infrastructure helpers (e.g., `App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait`) to avoid duplication.
    - **Complex entities**
        - (many relations, collections, translations, or special mapping rules)
        - MUST use dependency injection of `App\Shared\Infrastructure\Persistence\Doctrine\Interface\ProxyReferenceProviderInterface`
        - register interface implementation in a config file (e.g. `config/modules/catalog.yaml`).
- **Repository Pattern**:
    - Each module should split repository interfaces into **Read** and **Write** repositories (e.g., `ProductReadRepositoryInterface` and `ProductWriteRepositoryInterface`).
    - Read repositories should contain methods for data retrieval (`findById`, `findByUlid`, `findReadyToProcess`, etc.).
    - Read repositories MUST implement a private `checkAndMapToDomain(?object $orm): ?DomainEntity` method to ensure type safety and centralized mapping from ORM entities to domain objects.
    - Write repositories should contain methods for persistence (`save`, `delete`).
    - This ensures a cleaner separation of concerns and follows the CQRS principle within the module.
    - **Base Classes**: Implementations of these interfaces MUST inherit from an entity-specific base repository class (e.g., `BaseProductRepository`) which extends `BaseEntityRepository`. This base class should handle common dependencies like the `Mapper` and `ManagerRegistry` to ensure consistency.
    - **Write Trait**: Implementations of write repositories MUST use `App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait` to handle standard `save` and `delete` operations, avoiding code duplication.
- **Migrations**: Each module has its own migration configuration in `config/migrations/<module_name>.php`. Migrations must be run separately for each module using the `--em` and `--configuration` options.
- **Testing Isolation**: For testing, all module databases are automatically migrated and prepared by `tests/bootstrap.php` when running PHPUnit. This ensures a clean and isolated state for each module's database during joint testing.
- **Exception Handling**: Each module should contain its own exception handler (like `src/IdentityAccess/Presentation/Http/EventListener/ApiIdentityAccessExceptionListener.php`). The structure in such handlers might be module-specific (e.g., to comply with OAuth2 requirements).

### Modern PHP
- **Strict Typing**: `declare(strict_types=1);` is mandatory in every file.
- **PHP 8.4+ Features**: Use `readonly` properties or classes, constructor promotion, and other modern features.

### Messaging (CQRS)
- **Symfony Messenger**: All operations must be split into **Commands** (side effects) and **Queries** (data retrieval).
- **Handlers**: Every Command or Query must have a corresponding Handler.

## 4. Reliability & Patterns

- **Transactional Outbox**: Guaranteed message delivery. Domain events or messages are saved to the database within the same transaction as business changes and then dispatched by a separate process.
- **Config Collection**: The `Kernel.php` is configured to automatically collect configurations from modules. Each module should place its configuration files in the root `config/modules/` directory (e.g., `config/modules/identity_access.yaml`). This ensures module-specific settings are organized while maintaining a unified application configuration.
- **Observability**: A `TraceId` (standardized as **UUID v7**) must be present in all messages and log entries to enable end-to-end request tracking. All logs must be output in JSON format (using `monolog.formatter.json` in production) to ensure compatibility with log collectors like Filebeat.
- **Logging Channels**: Each module should use its own dedicated logging channel (e.g., `email_sender`) to facilitate filtering and analysis in Elasticsearch/Kibana.
- **Idempotency**: Use `TraceId` as an idempotency key to prevent duplicate processing of messages in RabbitMQ/Messenger.

## 5. Testing Strategy

- **Structure**: Tests are grouped by **Module** and then by **Test Type**. The standard structure is `tests/{ModuleName}/{TestType}/{OptionalSubPath}` (e.g., `tests/IdentityAccess/Unit/`).
- **Unit Tests**: Focus on the `Domain` layer (logic, value objects, entities).
- **Integration Tests**: Focus on `Infrastructure` (Doctrine mapping, repository implementations, external adapters).
- **Functional Tests**: End-to-end scenario testing via API endpoints or Handlers to verify business use cases.
- **Support**: Common test utilities, fixtures, and mothers for a module are located in `tests/{ModuleName}/Support/`.

**Mapper Testing**: Every Mapper class in the `Infrastructure` layer must have an `Integration Test`. This test must verify:
- `toDoctrineOrm`: Correct conversion of all Domain fields to ORM properties.
- `fromDoctrineOrm`: Correct restoration of the Domain object (including VO) from the ORM state
- `mapToExistingOrm`: Correct update of an existing ORM entity without losing data. *Note: These tests should use real data to ensure no field is forgotten.*

## 6. AI Interaction Rules

Before implementing any changes, the AI must:
1. **Verify Boundaries**: Check if the proposed solution violates module boundaries or Deptrac rules.
2. **Architecture Check**: Ensure a clear separation between Command and Query.
3. **Technical Rigor**: Ensure the implementation is compatible with **Symfony 7.3** and follows the strict typing requirements.
4. **Traceability**: Always consider how `TraceId` will be propagated in new workflows.

## 7. Infrastructure & Docker

### Custom Docker Images
When creating or modifying custom Docker images (e.g., `app`, `nginx`, `filebeat`), follow these rules:

1.  **Versioning**: Always use specific tags for base images (e.g., `alpine:3.21`, `php:8.4-fpm-alpine3.21`) instead of `latest` to ensure build reproducibility.
2.  **Layer Optimization (Caching)**:
    - Order operations from least frequent to most frequent changes.
    - Copy dependency files (`composer.json`, `package.json`, etc.) and install dependencies before copying the rest of the source code.
    - Use multi-stage builds to keep production images lean.
    - Use cache mounts (`--mount=type=cache`) for package managers (apk, composer, pecl) where supported.
3.  **Rationale**: Custom images should be used when:
    - Specific OS-level permissions are required (e.g., `chmod` for Filebeat configs).
    - Pre-bundled configurations or scripts are needed for the service to start correctly.
    - Environment-specific optimizations are necessary (multi-stage builds).

---
*Note: This file is a living document and should be updated as the project evolves.*
