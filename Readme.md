# Employee Wallet System

A backend technical assessment implementation for a **Backend & Integrations Engineer** role.

This project implements a financial wallet platform that synchronizes employee accounts with simulated payroll and banking providers while maintaining transactional integrity, asynchronous processing, and auditability.

---

# Overview

The system provides:

- Employee account management
- Multi-wallet support per employee
- Multi-currency wallet balances
- Internal wallet-to-wallet transfers
- Payroll event synchronization
- Asynchronous bank withdrawals
- Transaction ledger / audit trail
- Health monitoring
- Dockerized portable environment
- API documentation via Postman collection
- Automated test coverage

The architecture focuses on fintech backend requirements such as:

- transactional consistency
- concurrency-safe balance operations
- asynchronous external integrations
- prevention of negative balances
- clear service-layer separation
- reliable queue-based processing

---

# Technology Stack

## Backend
- PHP 8.3
- Laravel 11

## Database
- MySQL 8

## Queue / Cache
- Redis

## Web Server
- Nginx

## Containerization
- Docker
- Docker Compose

## API Testing
- Postman

## Automated Testing
- PHPUnit

---

# Architecture

The application follows a layered service-oriented architecture.

## Controllers
Responsible for:

- request validation
- resource responses
- HTTP orchestration

Controllers remain thin and delegate business logic to services.

---

## Services

### WalletService
Core balance operations:

- credit wallet
- debit wallet
- reserve funds
- release funds
- transaction creation

Implements concurrency-safe balance updates using database transactions and row locking.

---

### TransferService
Responsible for:

- internal wallet transfers
- debit/credit coordination
- currency validation

---

### PayrollService
Responsible for:

- processing payroll events
- simulating external payroll synchronization
- wallet salary crediting
- payroll event persistence

---

### WithdrawalService
Responsible for:

- withdrawal initiation
- fund reservation
- pending transaction creation
- async job dispatch
- success/failure lifecycle handling

---

### MockBankProviderService
Simulates an external banking provider.

Used to demonstrate:

- integration abstraction
- async provider processing
- eventual consistency workflows

---

### IdempotencyService
Handles duplicate request protection for integration-safe operations.

---

# Async Withdrawal Flow

Withdrawal processing is intentionally asynchronous to simulate real-world banking integrations.

Flow:

```text
Client requests withdrawal
        ↓
Validate wallet + balance
        ↓
Reserve wallet funds
        ↓
Create pending transaction
        ↓
Create pending bank withdrawal
        ↓
Dispatch Redis queue job
        ↓
Mock bank provider processes request
        ↓
SUCCESS:
    reserved funds consumed
    transaction completed
    withdrawal confirmed

FAILURE:
    reserved funds released
    transaction failed
    withdrawal failed
```

This demonstrates eventual consistency and resilient integration design.

---

# Database Design

Core entities:

- employees
- wallets
- transactions
- payroll_events
- bank_withdrawals
- idempotency_keys

## Key Design Decisions

### Transactions Table
Maintains a complete audit ledger for:

- transfers
- payroll credits
- withdrawals

Provides traceability and financial audit support.

---

### Wallet Balances
Wallets maintain:

- available_balance
- reserved_balance

This prevents overspending during asynchronous operations.

---

### External Reference IDs
External systems use identifiers such as:

- external_employee_id
- external_event_id

to simulate real provider synchronization.

---

# API Endpoints

## Health
```http
GET /api/health
```

---

## Employees
```http
GET    /api/employees
POST   /api/employees
GET    /api/employees/{id}
```

---

## Wallets
```http
GET    /api/wallets
POST   /api/wallets
GET    /api/wallets/{id}
GET    /api/wallets/{id}/transactions
```

---

## Transfers
```http
POST /api/transfers
```

---

## Payroll
```http
POST /api/payroll/events
```

---

## Withdrawals
```http
POST /api/withdrawals
```

---

# Validation

Request validation is implemented using Laravel Form Requests:

- CreateEmployeeRequest
- CreateWalletRequest
- TransferRequest
- PayrollEventRequest
- WithdrawalRequest

Validation covers:

- required fields
- existence checks
- uniqueness
- transfer constraints
- amount validation
- currency format validation

---

# API Responses

Laravel API Resources are used for consistent response formatting:

- EmployeeResource
- WalletResource
- TransactionResource
- BankWithdrawalResource

---

# Docker Setup

## Start Environment

```bash
docker compose up -d --build
```

---

## Run Migrations

```bash
docker compose exec wallet-app php artisan migrate
```

---

## Seed Demo Data (optional)

```bash
docker compose exec wallet-app php artisan db:seed
```

---

## Queue Worker

Queue worker runs in dedicated container:

```bash
wallet-worker
```

Redis queue backend:

```env
QUEUE_CONNECTION=redis
```

---

# Health Check

```bash
curl http://localhost:8080/api/health
```

Expected:

```json
{
  "status": "healthy",
  "checks": {
    "database": true,
    "redis": true
  }
}
```

---

# Testing

Run all tests:

```bash
docker compose exec wallet-app php artisan test
```

Run specific test suite:

```bash
docker compose exec wallet-app php artisan test --filter=WithdrawalFeatureTest
```

---

# Postman Collection

Included in:

```text
/postman
```

Files:

```text
Employee-Wallet-API.postman_collection.json
Employee-Wallet-Local.postman_environment.json
```

Recommended execution order:

```text
Health
Employees
Wallets
Transfers
Payroll
Withdrawals
```

---

# Engineering Decisions

## Why Service Layer?
Separates business logic from controllers.

Benefits:

- maintainability
- testability
- cleaner controllers
- easier domain evolution

---

## Why Async Withdrawals?
Real banking integrations are rarely synchronous.

Async processing allows:

- realistic provider simulation
- resilient integration behavior
- eventual consistency
- non-blocking API requests

---

## Why Reserved Balances?
Prevents negative balances during delayed external processing.

Without reservations:

- concurrent withdrawals could overspend

---

## Why Redis Queue?
Provides lightweight reliable async processing suitable for this assessment.

---

## Why Mock Providers?
The assessment requires simulated integrations.

Mock providers preserve architecture realism without external dependencies.

---

# Assumptions

Assumptions made:

- employee emails are unique
- wallet currency cannot change after creation
- payroll events are immutable
- withdrawals are processed asynchronously
- mock banking provider randomly succeeds/fails
- all monetary values are stored as integers (minor units)

---

# Future Improvements

Potential production enhancements:

- authentication / authorization
- request idempotency middleware
- webhook callbacks for provider integrations
- retry/backoff policies
- distributed locking
- observability / metrics
- API versioning
- OpenAPI documentation
- stricter currency domain modeling
- integration contract testing

---

# Author

## Amjad Khaliliah

Backend & Integrations Engineer Technical Assessment Submission

This project was designed and implemented as a production-oriented backend solution for a fintech wallet platform, emphasizing:

- transactional consistency
- asynchronous provider integrations
- service-oriented architecture
- Dockerized portability
- automated testing
- API documentation via Postman

GitHub Repository: *(insert repository link here)*