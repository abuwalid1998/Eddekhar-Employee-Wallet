# Bug Audit & Fix Recommendations

## 1) Payroll request/service field mismatch (critical)
- **Where:** `app/Http/Requests/PayrollEventRequest.php` vs `app/Services/PayrollService.php`.
- **Bug:** Validator accepts `external_event_id` and `employee_external_id`, but service reads `$payload['event_id']` and `$payload['employee_id']`.
- **Impact:** Runtime `Undefined array key` errors and payroll processing fails for valid requests.
- **Suggested fix:** Normalize naming in one place. Preferred: update `PayrollService::processSalaryEvent()` to read `external_event_id` and `employee_external_id`, then resolve employee by `external_employee_id`.

## 2) Transfer endpoint is not idempotent (high)
- **Where:** `app/Http/Controllers/Api/TransferController.php`, `app/Services/TransferService.php`, `app/Services/IdempotencyService.php`.
- **Bug:** Repeated client retries can execute duplicate money movement.
- **Impact:** Double debits/credits when retries happen due to network timeouts.
- **Suggested fix:** Require idempotency key header for transfer and withdrawal writes. Persist request hash + response before returning.

## 3) Withdrawal confirmation/failure can be re-applied (high)
- **Where:** `app/Services/WithdrawalService.php`.
- **Bug:** `confirmWithdrawal()` and `failWithdrawal()` do not guard current status and can run multiple times.
- **Impact:** Reserved/available balances can be decremented repeatedly, causing negative reserved balances and ledger drift.
- **Suggested fix:** Enforce state transition checks (`pending -> confirmed|failed` only) and lock `BankWithdrawal` + wallet rows with `lockForUpdate()`.

## 4) Missing wallet active check in reserve/release flows (medium)
- **Where:** `app/Services/WalletService.php` (`reserveFunds`, `releaseFunds`).
- **Bug:** `credit()`/`debit()` enforce active wallets, but reserve/release do not.
- **Impact:** Inactive wallets can still be used in withdrawal workflow.
- **Suggested fix:** Call `assertWalletActive($wallet)` in `reserveFunds()` and `releaseFunds()` after lock.

## 5) No guard against negative reserved balance (medium)
- **Where:** `app/Services/WalletService.php` (`releaseFunds`) and `app/Services/WithdrawalService.php`.
- **Bug:** Code subtracts from `reserved_balance` without validating there are enough reserved funds.
- **Impact:** Data corruption (`reserved_balance < 0`) under duplicate processing or manual calls.
- **Suggested fix:** Add invariant checks before subtraction; throw domain exception when reserved funds are insufficient.

## 6) Nested transactions can increase deadlock/retry complexity (medium)
- **Where:** `TransferService::transfer()` wraps `WalletService::debit()` and `WalletService::credit()`, each of which starts its own transaction.
- **Bug:** Laravel uses savepoints, but nested transaction scopes with lock acquisition order across wallets can still create deadlock risk.
- **Impact:** Intermittent failed transfers under concurrency.
- **Suggested fix:** Expose lower-level non-transactional debit/credit helpers and keep one outer transaction with deterministic lock ordering (e.g., lock lower wallet id first).

## 7) Payroll endpoint always returns 201 even for duplicates (low)
- **Where:** `app/Http/Controllers/Api/PayrollController.php`.
- **Bug:** Duplicate event path in service returns existing record, but controller still returns `201 Created`.
- **Impact:** Misleading API semantics for idempotent replay.
- **Suggested fix:** Return `200 OK` when existing event was reused, keep `201` only when newly created.

## 8) Test execution currently broken in this environment (operational)
- **Where:** project setup.
- **Bug:** `vendor/autoload.php` missing, so tests cannot run.
- **Impact:** No automated verification gate before deploy.
- **Suggested fix:** Run `composer install` in CI and enforce `php artisan test` as required check.
