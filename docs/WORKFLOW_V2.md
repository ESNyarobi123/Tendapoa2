# Tendapoa Workflow V2 — Escrow-Based Marketplace

## Overview

The upgraded workflow replaces the old **pay-before-post** model with a modern **free-post → escrow** marketplace:

```
Client posts job (FREE) → Workers apply → Client selects → Client funds escrow
→ Worker accepts → Work in progress → Worker submits → Client confirms → Funds released
```

## Status Flow

```
open → awaiting_payment → funded → in_progress → submitted → completed
  │         │                │          │             │
  └→cancelled └→cancelled     └→open     └→disputed    └→in_progress (revision)
              └→open(reset)   └→disputed               └→disputed
                              └→cancelled
```

### Terminal States
- **completed** — Work done, funds released to worker (minus commission)
- **cancelled** — Job cancelled, escrow refunded if applicable
- **expired** — Job expired without applications
- **refunded** — Dispute resolved with full client refund

## Statuses

| Status | Description |
|--------|-------------|
| `open` | Job posted, accepting applications |
| `awaiting_payment` | Worker selected, waiting for client to fund escrow |
| `funded` | Escrow funded, waiting for worker to accept |
| `in_progress` | Worker accepted and is working |
| `submitted` | Worker submitted completion, waiting for client review |
| `completed` | Client confirmed, funds released to worker |
| `disputed` | Dispute opened, escrow frozen |
| `cancelled` | Job cancelled by client |
| `expired` | Job expired |
| `refunded` | Dispute resolved with full client refund |

## Key Flows

### 1. Job Creation
- Client posts job with title, description, price, category, location
- Job status: `open`, `published_at` set
- **No payment required at posting time**

### 2. Application Flow
- Worker applies with `proposed_amount`, `message`, optional `eta_text`
- Client can: **shortlist**, **reject**, **counter** (with `counter_amount`), or **select**
- Worker can: **withdraw**, **accept counter**

### 3. Selection & Funding
- Client selects a worker → job moves to `awaiting_payment`
- `agreed_amount` = worker's proposed amount (or counter amount if accepted)
- Client funds from wallet → escrow `held_balance` increases
- Job moves to `funded`

### 4. Worker Response
- Worker **accepts** → `in_progress`
- Worker **declines** → escrow refunded, job reverts to `open`

### 5. Completion
- Worker submits → `submitted`
- Client can:
  - **Confirm** → `completed`, funds released (minus commission)
  - **Request revision** → back to `in_progress`
  - **Open dispute** → `disputed`

### 6. Dispute Resolution (Admin)
- **Full worker release** — Worker receives funds minus commission
- **Full client refund** — Client gets full refund
- **Split** — Custom split between worker and client

## Financial Flow

```
Client wallet.balance → wallet.held_balance (escrow hold)
                     → Worker wallet.balance (release, minus commission)
                     → Platform fee (commission)
```

### Commission
- Configured via `Setting::get('commission_rate', 10)` (default 10%)
- Deducted from escrow amount on release
- Example: 50,000 escrow → 5,000 fee → 45,000 to worker

### Escrow Ledger
All fund movements are auditable via `escrow_ledger` table:
- `hold` — Funds moved to escrow
- `release` — Funds released to worker
- `refund` — Funds returned to client
- `platform_fee` — Commission deducted
- `split_release` / `split_refund` — Dispute split amounts

## Database Tables

### New Tables
- `job_applications` — Worker applications with status tracking
- `escrow_ledger` — All escrow fund movements
- `job_status_logs` — Audit trail of all status changes
- `disputes` / `dispute_messages` — Dispute lifecycle
- `reviews` — Two-sided job reviews

### Modified Tables
- `work_orders` — Added: `agreed_amount`, `escrow_amount`, `platform_fee_amount`, `release_amount`, `selected_worker_id`, `funded_at`, `submitted_at`, `confirmed_at`, `disputed_at`, `auto_release_at`, `application_count`, `cancel_reason`, `urgency`
- `wallets` — Added: `held_balance`, `total_earned`, `total_spent`, `total_withdrawn`

## State Machine

Valid transitions are enforced in `Job::transitionStatus()` via the `TRANSITIONS` constant.
Invalid transitions throw `RuntimeException`. Use `force: true` for admin/migration overrides.

## Legacy Compatibility

- Old statuses (`pending_payment`, `posted`, `assigned`, `ready_for_confirmation`) have defined transition paths to new statuses
- `CommentController` / `JobComment` model preserved for backward compat with existing comments data
- `PaymentController` preserved for mfanyakazi posting fee flow
- Cancel logic handles both old and new statuses

## Key Services

| Service | Purpose |
|---------|---------|
| `EscrowService` | Hold/release/refund/split escrow funds |
| `CompletionService` | Worker submit, client confirm/revision |
| `DisputeService` | Open disputes, admin resolution |
| `WalletService` | Credit/debit wallet balances |
| `ClickPesaService` | Payment gateway integration |

## Key Controllers

| Controller | Purpose |
|------------|---------|
| `ApplicationController` | Worker apply, client shortlist/reject/counter/select |
| `FundingController` | Client fund job from wallet or mobile money |
| `CompletionController` | Worker accept/decline/submit, client confirm/revision/dispute |
| `JobController` | Job CRUD, cancel, legacy flows |
