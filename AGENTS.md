# AI Agent Guidelines (AGENTS.md)

This document serves as the primary system instruction for AI Agents (like Junie or others) working on this project. It defines the architectural principles, coding standards, and interaction rules to ensure consistency and quality across the modular monolith.

## 1. Project Philosophy

### Modular Monolith & DDD
The project is built as a **Modular Monolith** following **Domain-Driven Design (DDD)** principles. The system is divided into high-level modules (e.g., `Users`, `EmailSender`) located in `src/`.

- **Isolation**: Each module must be self-contained. Direct calls between modules are strictly forbidden.
- **Communication**: Inter-module communication is handled exclusively via the `Shared` module or through **Events** (Asynchronous or Synchronous via Symfony Messenger).
- **Enforcement**: **Deptrac** is used to monitor and enforce layer boundaries and dependency rules.
- **Module Independence**: Every module must be independent. The `Shared` layer is the only exception, providing reusable components. However, `Shared` must only contain primitive logic, base interfaces, and cross-cutting concerns (e.g., `TraceId`, `ValueObjects` used by multiple modules) to maintain strict decoupling.

## 2. Directory Structure

Every module within `src/` must follow this standardized structure:

- **Domain/**: Contains the core business logic.
    - `Entities`, `Value Objects`, `Domain Events`.
    - `Repository Interfaces` (definitions only).
- **Application/**: Contains use cases and orchestration.
    - `Commands` / `Queries`.
    - `Handlers` (Command/Query Handlers).
    - `DTOs` (Data Transfer Objects).
- **Infrastructure/**: External concerns and technical implementations.
    - `Persistence/Doctrine/Mapping`: Explicit field definitions (ORM Mapping).
    - `Repository Implementations`.
    - `Adapters` for external services.
- **Presentation/**: Entry points to the module.
    - `Controllers` (Web API).
    - `CLI Commands`.

## 3. Coding Standards & Constraints

### Domain Layer
- **Pure PHP**: No dependencies on Symfony, Doctrine, or any other framework/library.
- **Value Objects (VO)**: Use Value Objects for all domain properties. Avoid primitives (string, int, array) in Entities.
    - Every property should ideally be a VO (e.g., `EmailAddress`, `ClientId`, `RoleCollection`).
    - Collection properties must be wrapped in a Collection VO (e.g., `ScopeCollection`).
    - **VO Exceptions**: Every VO must have a specific domain exception.
    - **Exception Hierarchy**: Each module must implement the following structure:
        1. `Throwable{Module}Exception` (interface): Module marker.
        2. `{Module}DomainException` (abstract class): Base module exception.
        3. `Invalid{Module}ValueObjectException` (abstract class): Base exception for all VOs, inherits from base module exception and implements `ThrowableValueObjectException`.
        4. Specific VO exceptions (e.g., `InvalidUserAccountEmailException`) must inherit from `Invalid{Module}ValueObjectException`.
    - **VO Location**: 
        - Model-specific VO must be placed in a subfolder named after the entity (e.g., `src/IdentityAccess/Domain/ValueObject/UserAccount/EmailAddress.php`).
        - Module-shared VO must be placed in the root `ValueObject` folder of the module.
        - Cross-module VO must be placed in `src/Shared/Domain/ValueObject/`.
    - **VO Validation**: VO must ensure their own validity upon creation. Use existing validators from `Shared` or `Domain` if available.
- **Independence**: The domain must remain agnostic of how it is persisted or triggered.
- **Service Interfaces**:
    - Any service that interacts with infrastructure (API, DB, Mailer, etc.) must have an interface in the `Domain` layer and its implementation in the `Infrastructure` layer.
    - **Exception**: Simple stateless services, pure logic helpers, or validators (e.g., `StringHelper`, `StringValidator`) located in `Shared` or `Domain` may exist as final classes without an interface, provided they have no external dependencies.
    - If a service is likely to be mocked in unit tests of other components, prefer using an interface.

### Persistence & Mapping
- **Mapping Location**: Doctrine mapping must reside strictly within `src/<ModuleName>/Infrastructure/Persistence/.../Mapping` (XML/PHP) OR within Infrastructure-specific entities (e.g., `Orm*` classes) using PHP attributes.
- **Explicit Definitions**: Avoid using attributes or XML inside the Domain layer. Attributes are permitted only in the Infrastructure layer for ORM entities.

### Modern PHP
- **Strict Typing**: `declare(strict_types=1);` is mandatory in every file.
- **PHP 8.4+ Features**: Use `readonly` properties or classes, constructor promotion, and other modern features.

### Messaging (CQRS)
- **Symfony Messenger**: All operations must be split into **Commands** (side effects) and **Queries** (data retrieval).
- **Handlers**: Every Command or Query must have a corresponding Handler.

## 4. Reliability & Patterns

- **Transactional Outbox**: Guaranteed message delivery. Domain events or messages are saved to the database within the same transaction as business changes and then dispatched by a separate process.
- **Config Collection**: The `Kernel.php` is configured to automatically collect configurations from modules. Each module should place its configuration files in `src/<ModuleName>/Infrastructure/Resources/config/modules/`. This ensures module isolation while maintaining a unified application configuration.
- **Observability**: A `TraceId` must be present in all messages and log entries to enable end-to-end request tracking. All logs must be output in JSON format (using `monolog.formatter.json` in production) to ensure compatibility with log collectors like Filebeat.
- **Logging Channels**: Each module should use its own dedicated logging channel (e.g., `email_sender`) to facilitate filtering and analysis in Elasticsearch/Kibana.
- **Idempotency**: Use `TraceId` as an idempotency key to prevent duplicate processing of messages in RabbitMQ/Messenger.

## 5. Testing Strategy

- **Unit Tests**: Focus on the `Domain` layer (logic, value objects, entities).
- **Integration Tests**: Focus on `Infrastructure` (Doctrine mapping, repository implementations, external adapters).
- **Application/Functional Tests**: End-to-end scenario testing via API endpoints or Handlers to verify business use cases.

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
