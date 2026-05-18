# Wallet System Architecture

## Tech Stack
- PHP 8.3
- Laravel 12
- MySQL 8
- Redis
- Laravel Queue
- Pest
- Docker Compose

## Business Assumptions
- Monetary values stored in minor units (integer cents)
- Same-currency transfers only
- Wallet balances can never be negative
- Withdrawals reserve funds until bank confirmation
- Duplicate payroll events must be ignored
- All money movement endpoints are idempotent
- Employee can own multiple wallets
- Employee status can block withdrawals

## Core Design Decisions

### Ledger + Balance Model
Wallet keeps:
- available_balance
- reserved_balance

Transactions table is immutable audit ledger.

Reason:
Fast reads + auditability + reconciliation.

### Concurrency Protection
All money operations use:
- DB transactions
- SELECT ... FOR UPDATE

Reason:
Prevent race conditions and overspending.

### Async Withdrawal Flow
Withdrawal process:
1. reserve funds
2. create pending withdrawal
3. dispatch queue job
4. simulate bank confirmation
5. confirm or rollback

### Payroll Integration
Inbound event endpoint simulates payroll provider.

Duplicate events deduplicated using external event IDs.

### Future Improvements
- Outbox pattern
- Event sourcing
- Kafka integration
- FX conversion support
- Reconciliation jobs
- Double-entry accounting