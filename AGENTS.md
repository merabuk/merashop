# AI Agent Guidelines (AGENTS.md)

This document serves as the primary system instruction for AI Agents (like Junie or others) working on this project. It defines the architectural principles, coding standards, and interaction rules to ensure consistency and quality across the modular monolith.

## 1. Project Philosophy

### Modular Monolith & DDD
The project is built as a **Modular Monolith** following **Domain-Driven Design (DDD)** principles.

The system is divided into high-level modules located in `src/`:
- `Catalog` - products, categories, and attributes in the store.
- `Customer` - customer profiles and related domain logic.
- `EmailSender` - module for sending notifications.
- `IdentityAccess` - user and modules management, registration, and authorization. Token-based authentication. Blacklist management.
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

```bash
src/ModuleName/Domain/ # Core business logic (Pure PHP)
├── Entity             # Domain entities with invariants
├── Enum               # Common enumerations
├── Event              # Domain events (internal)
├── Exception          # Domain exceptions & markers
├── Factory            # Domain factories
│   └── Contracts      # Factory interfaces
├── Repository         # Repository interfaces (definitions only)
├── Service            # Pure domain services (logic only)
└── ValueObject        # Objects grouped by entities

src/ModuleName/Application/ # Use cases and orchestration
├── Command                 # Commands and their Handlers
├── DTO                     # Simple data containers for input/output
├── EventHandler            # Listeners for internal and shared events
├── Exception               # Application-level exceptions
├── Query                   # Queries and their Handlers
└── Service                 # Simple app services (orchestrators)

src/ModuleName/Infrastructure/ # Technical implementations
├── Adapter                    # External API clients, wrappers
├── Persistence                # Database logic
│   └── Doctrine
│       ├── Entity             # ORM mapping entities
│       ├── Mapper             # Domain <=> ORM transformation logic
│       ├── Repository         # Implementation of Domain Repositories
│       └── Type               # Custom DB types (Enums, VOs)
├── Scheduler                  # Cron/Scheduled tasks definitions
└── Service                    # Services with external dependencies

src/ModuleName/Presentation/ # Entry points
├── Console                  # CLI Commands
└── Http                     # Web API
    ├── ApiVersion1          # Public API
    │   ├── Controller       # Controllers
    │   ├── Request          # Validated Request DTOs (MapRequestPayload)
    │   └── Resource         # Response formatters (JsonSerializable)
    ├── AdminApiVersion1     # Admin API
    ├── InternalApiVersion1  # M2M/Internal API
    ├── EventListener        # Request/Response listeners, exception handling
    └── translations         # Translations for the module (exceptions, validation, module-name)
```

Also, every module can have its own specific folders which are not listed above
```bash
src/EmailSender/Infrastructure/
│ ...
├── Mailer           # mailer implementation
├── Resources        # resources for the module
│   ├── templates    # templates for the module (emails)
│   └── translations # translations for the module (emails)
│ ...
```
```bash
src/Shared/Domain/
│ ...
├── Bus           # common bus definitions
├── Criteria      # base criteria for reusing in code
│   ├── Filtering # filtering criteria
│   ├── Listing   # listing criteria
│   ├── Paging    # paging criteria
│   └── Sorting   # sorting criteria
│ ...
```

The translation folder is standardized at `src/<ModuleName>/Presentation/Http/translations/`, except for infrastructure-specific translations (e.g., in `EmailSender`).

Base `assets` folder structure (TypeScript enabled):
```bash
assets/
├── app.ts                         # Global entry point (TS)
├── App.vue                        # Root component (lang="ts")
├── modules/
│   ├── Shared/
│   │   ├── components/            # Shared components 
│   │   ├── i18n/                  # Internationalization
│   │   ├── services/              # Global services
│   │   ├── store/
│   │   │   └── useSessionStore.ts # Global state (User, TraceId, Auth status)
│   │   ├── types/                 # Global TS interfaces/types
│   │   ├── api-client.ts          # Axios wrapper with TraceId integration
│   │   ├── constants.ts           # Global constants (Headers, Route names, etc.)
│   ├── Catalog/
│   │   ├── store/
│   │   │   └── useCatalogStore.ts # Specific state (Filters, last view)
│   │   ├── views/                 # Main catalog views
│   ...
├── shims.d.ts                     # TypeScript type definitions
```

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

### Frontend Layer (Assets)
- **TypeScript**: Mandatory use of strict typing for all frontend components and services.
- **Module Independence**: Every frontend module must be self-contained within `assets/modules/{ModuleName}`.
- **State Management (Pinia)**:
    - Each module must have its own `store/` directory.
    - Direct modification of another module's store state is strictly forbidden.
    - Inter-module communication at the state level should be handled via the `Shared` store or explicit actions.
- **Tracing**: Every API call via `apiClient` must automatically include the `MeraShop-Trace-Id` header retrieved from the initial page state.
- **Type Definitions**:
    - All interfaces and enums must be placed in a `types/` directory within their respective module.
    - Global types used by multiple modules must reside in `assets/modules/Shared/types/`.
    - Do not define business-logic interfaces inside `.vue` or `store.ts` files to prevent circular dependencies.

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

### Listing, Filtering & Pagination (Criteria Pattern)
To ensure consistent data retrieval across all modules, the **Criteria Pattern** must be used.

- **Domain Layer (Shared/Domain/Criteria)**:
    - All listing operations must use the `Criteria` object, which encapsulates:
        - `Paging\Cursor`: Cursor-based data (`lastSeenIdentifier`, `perPage`).
        - `Sorting\Sort`: Sorting rules (`field`, `direction`).
        - `Filtering\Filters`: A collection of filter parameters.
- **Application Layer**:
    - Queries for lists (e.g., `GetAttributeListQuery`) must accept a `Criteria` object instead of primitive types.
- **Infrastructure Layer**:
    - **Repository**: Use `ReadRepositoryTrait->_paginate()` to implement cursor-based pagination.
    - **Stability**: For stable sorting, the `_paginate` method must always append a unique field (like `id` or `ulid`) as a secondary sort key if the primary key is not unique.
    - **Search**: Use `_prepareSearchValue()` for consistent `LIKE` query formatting and protection against special characters.
    - **Contracts**: Domain entities included in listings should implement `App\Shared\Domain\Entity\HasIdInterface`.
- **Presentation Layer**:
    - **Request**: Use `App\Shared\Presentation\Http\Request\PaginationRequest` as a controller argument. It is automatically populated via `PaginationRequestResolver`.
    - **Query Format**: Flat query parameters only. **JSON in query strings is forbidden.**
        - Filters: `?filter[field]=value`
        - Sorting: `?sortField=name&sortDir=ASC`
        - Pagination: `?lastSeenId=XYZ&perPage=20`
    - **Response**: Use `PaginatedResponseTrait->createPaginatedResponse()` to standardize:
        - `Content-Range`: Header in format `<unit> <count>/<totalCount>`.
        - `X-Next-Cursor`: Header containing the identifier for the next page.

### Value Resolvers

- **Consistency with Symfony Native Resolvers**:
    - When manual validation is performed within a custom Value Resolver (like `PaginationRequestResolver`), use the same exception pattern as Symfony's `#[MapRequestPayload]`.
    - Throw an `HttpException` with status **422** and pass a `ValidationFailedException` (containing the violations) as the **previous exception**.
    - This ensures that the `ApiExceptionListener` provides a unified error response structure across the entire API.

### Translations
- **Standard Locations**:
    - Most modules: `src/<ModuleName>/Presentation/Http/translations/`.
    - `EmailSender` module: `src/EmailSender/Infrastructure/Resources/translations/`.
- **Naming Convention**: 
    - Files must follow the format `{domain}+intl-icu.{locale}.{extension}` (e.g., `catalog+intl-icu.en.yaml`).
    - **ICU Format**: All translation files MUST use the `+intl-icu` suffix to support ICU message formatting.
- **Domains**:
    - `{module_name}`: Main domain for general module messages (e.g., success messages, entity names). Use YAML for these.
    - `{module_name}_exceptions`: Domain for exception messages. Use PHP for these to map `ErrorCodeEnum` values directly.
    - `validators`: Domain for request validation messages. Use YAML for these.
        - Content format: `[context].[group].[item]` (e.g., `catalog.attribute.status_invalid`).
    - `email_sender` (`EmailSender` only): Email-specific messages.
        - Content format: `[email_type].[template_name].[part]` (e.g., `public_email.user_registered.subject`).
- **Exception Translations**: Every `ServerException` must implement `getTranslationDomain()` which defaults to `exceptions`. Override it in module-specific base exceptions (e.g., returning `catalog_exceptions`).
- **Success Messages**: Use `App\Shared\Presentation\Http\Helper\Traits\ResponseMessageTrait` to create standardized success messages.
    - Example: `common.messages.create_success` with an `{entity}` parameter.
    - Entity names should be defined under `common.<entity>.entityName` in the module's main translation domain.

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
- `mapToExistingOrm`: Correct update of an existing ORM entity without losing data. *Note: These tests should use Object Mothers / Data Builders (close to real data) to ensure no field is forgotten.*

### 5.1. Naming Conventions

- **Unit Tests (Logic & Value Objects)**: Use the `testIt` prefix to describe the expected behavior.
    - **Example**: testItTrimsSpaces(), testItCreatesValidCollection().
- **Infrastructure Tests (Mappers & Repositories)**: Use the direct name of the method being tested.
    - **Example**: testToDoctrineOrm(), testFindById().
- **Functional Tests (API/Handlers)**: Use a narrative style [testIt] + [Action] + [Expected Result/Context].
    - **Example**: testItSuccessfullyUpdatesAttribute(), testItReturns409OnConcurrencyError().
- **Exception Testing**: Use the format testThrowsExceptionOn[Condition].
    - **Example**: testThrowsExceptionOnInvalidLocale().

## 6. AI Interaction Rules

Before implementing any changes, the AI must:
1. **Verify Boundaries**: Check if the proposed solution violates module boundaries or Deptrac rules.
2. **Architecture Check**: Ensure a clear separation between Command and Query.
3. **Technical Rigor**: Ensure the implementation is compatible with **Symfony 7.4** and follows the strict typing requirements.
4. **Traceability**: Always consider how `TraceId` will be propagated in new workflows.
5. **Listing Standard**: When implementing any list endpoint, verify that it uses the `Criteria` pattern, `PaginationRequest`, and returns correct `Content-Range` headers as defined in the standards.

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
