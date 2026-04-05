# Testing Notes — Workflow V2

## Test Suite

**File:** `tests/Feature/WorkflowLifecycleTest.php`
**Framework:** Pest PHP (Laravel feature tests)
**Database:** In-memory SQLite via `RefreshDatabase`
**Results:** 43 tests, 127 assertions — all passing

## Running Tests

```bash
# Run all workflow tests
php artisan test --filter=WorkflowLifecycleTest

# Run a specific test
php artisan test --filter="client can create a job for free"

# Run full suite
php artisan test
```

## Test Coverage by Section

### A. Job Creation (3 tests)
- Client creates job for free (status = open, no payment)
- No payment record created at posting time
- Unauthenticated user redirected to login

### B. Application Flow (7 tests)
- Worker can apply to open job
- Worker cannot apply twice to same job
- Client can shortlist, reject, counter applications
- Worker can withdraw, accept counter offer

### C. Selection Flow (2 tests)
- Client selects worker → job moves to `awaiting_payment`
- Unauthorized user cannot select worker on another's job

### D. Funding Flow (3 tests)
- Client funds from wallet → escrow hold created
- Insufficient balance blocks funding
- Funds go to escrow `held_balance`, not directly to worker

### E. Worker Response (3 tests)
- Selected worker accepts → `in_progress`
- Selected worker declines → escrow refunded, job reverts to `open`
- Non-selected worker cannot accept

### F. Completion Flow (3 tests)
- Worker submits → `submitted`
- Client confirms → `completed`, funds released (commission deducted)
- Client requests revision → back to `in_progress`

### G. Dispute Flow (4 tests)
- Client opens dispute → `disputed`, escrow frozen
- Disputed job blocks confirm
- Admin resolve: full worker, full client refund, split

### H. Wallet / Ledger (4 tests)
- `available_balance` = `balance` - `held_balance`
- Escrow hold creates wallet transaction
- Escrow release creates worker wallet transaction
- Refund restores client available balance

### I. Authorization / Security (3 tests)
- Client cannot manage another client's job applications
- Worker cannot alter other workers' applications
- Unauthorized user cannot confirm completion

### J. Backward Compatibility (2 tests)
- Legacy completed jobs are viewable
- Open jobs appear in feed

### K. Status Log Audit (1 test)
- Status transitions create `job_status_logs` entries

### K2. State Machine Hardening (6 tests)
- Invalid transition is blocked (throws RuntimeException)
- Invalid transition can be forced (admin bypass)
- Completed job cannot transition further
- Cancelled job cannot transition further
- All valid forward transitions pass
- `isValidTransition()` helper correctness

### L. End-to-End Lifecycle (1 test)
- Full flow: post → apply → select → fund → accept → submit → confirm
- Verifies financial correctness (commission calculation, wallet balances)

## SQLite Compatibility Fixes

Three migrations were patched for SQLite in-memory testing:

1. **`2025_10_14_091821_fix_mfanyakazi_response_column_size.php`** — Replaced `SHOW COLUMNS` with `Schema::hasColumn()`
2. **`2025_10_14_090205_increase_mfanyakazi_response_column_size.php`** — Wrapped `->change()` in try-catch
3. **`2025_10_22_140051_add_confirmed_price_status_to_quotes_table.php`** — Wrapped `ALTER TABLE MODIFY ENUM` in try-catch
4. **`2025_10_16_142627_add_poster_type_to_jobs_table.php`** — Added `work_orders` table support alongside `jobs`

## Factories

| Factory | File | States |
|---------|------|--------|
| `UserFactory` | `database/factories/UserFactory.php` | `muhitaji()`, `mfanyakazi()`, `admin()` |
| `CategoryFactory` | `database/factories/CategoryFactory.php` | default |
| `JobFactory` | `database/factories/JobFactory.php` | `open()`, `awaitingPayment()`, `funded()`, `inProgress()`, `submitted()`, `completed()` |
