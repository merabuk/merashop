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
- **Observability**: A `TraceId` must be present in all messages and log entries to enable end-to-end request tracking.
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

---
*Note: This file is a living document and should be updated as the project evolves.*
