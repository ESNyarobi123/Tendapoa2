# Tendapoa2 Workflow Upgrade Plan

## Phase 1: AUDIT ✅ COMPLETE

### Current State Summary
- **DB Table**: `work_orders` (aliased as `Job` model)
- **Old Flow**: `pending_payment → posted → assigned → in_progress → completed`
- **Payment**: ClickPesa USSD Push (mobile money)
- **Wallet**: Single `balance` field, no escrow/held concept
- **Frontend**: Plain Blade + inline CSS, no component library
- **Stack**: Laravel 13 + Sanctum + Tailwind 3 + Alpine.js + DaisyUI

### Key Problems Identified
1. Client must pay BEFORE job is visible — kills conversion
2. No proper application/bidding system (uses `work_order_comments`)
3. No escrow — money goes direct, no safety net
4. Single wallet balance — no held/available separation
5. No two-sided completion (worker code = instant pay)
6. No dispute system
7. No job_status_logs audit trail
8. Frontend is raw HTML/CSS, not modern UI

---

## Phase 2: DOMAIN REFACTOR

### New Job Lifecycle
```
open → (workers apply) → awaiting_payment → funded → in_progress → submitted → completed
                                                                              ↗ disputed
                        → cancelled (at any point before in_progress)
                        → expired (timeout)
                        → refunded (after cancellation/dispute resolution)
```

### New Tables
1. `job_applications` — proper application entity (replaces comment-based applications)
2. `escrow_ledger` — tracks held funds per job
3. `job_status_logs` — audit trail for all status changes
4. `disputes` — dispute cases
5. `reviews` — two-sided reviews after completion

### Modified Tables
1. `work_orders` — add: funded_at, submitted_at, disputed_at, escrow_amount, agreed_amount, platform_fee_amount, auto_release_at
2. `wallets` — add: held_balance (separate from available balance)
3. `wallet_transactions` — expand types: job_funding, escrow_hold, escrow_release, refund, platform_fee, earning_release
4. `payments` — change status from ENUM to string, add purpose field

### New Services
- `JobCreationService` — free posting logic
- `JobApplicationService` — apply/shortlist/reject/select
- `EscrowService` — hold/release/refund funds
- `CompletionService` — submit/confirm/auto-release
- `DisputeService` — open/resolve disputes

---

## Phase 3: DATABASE (migrations)
## Phase 4: BACKEND (services + controllers)
## Phase 5: FRONTEND (Flux UI / Livewire pages)
## Phase 6: TESTS
## Phase 7: CLEANUP
