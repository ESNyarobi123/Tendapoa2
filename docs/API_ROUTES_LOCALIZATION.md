# API Routes & Localization – Uthibitisho

Hati hii inathibitisha kuwa **routes zote za API** zimeunganishwa na localization na zina **function/method** sahihi.

---

## Jinsi localization inavyofanya kazi

1. **Accept-Language:** Middleware `SetLocaleFromHeader` inaweka `app()->setLocale()` kutoka header `Accept-Language` (en / sw).
2. **Models:** `Job` na `Category` zina accessors (`getTitleAttribute`, `getDescriptionAttribute`, `getNameAttribute`) zinazotoa thamani kulingana na locale; `title`/`description`/`name` katika JSON ni tayari kwa lugha inayotakiwa.
3. **Job create/update:** Zote zinatumia `TranslationService::ensureBothLanguages()` na kuhifadhi `title_sw`, `title_en`, `description_sw`, `description_en`.

Kwa hiyo **kila endpoint** inayorudisha Job au Category (feed, dashboard, my jobs, show, categories, home) inatoa **title/description/name** kwa lugha sahihi bila kubadilisha kila route moja kwa moja.

---

## Muhtasari wa API Routes (muhimu kwa localization)

| Kikundi | Method | URL | Uhandisi | Localization |
|--------|--------|-----|----------|--------------|
| **Public** | GET | `/api/categories` | Closure | ✓ Category::get() → getNameAttribute |
| | GET | `/api/home` | Closure | ✓ categories + stats |
| **Dashboard** | GET/POST | `/api/dashboard/` | Closure | ✓ allJobs, currentJobs, completedJobs → Job accessors |
| | GET | `/api/dashboard/updates` | ApiDashboardController::updates | - |
| **Jobs (Muhitaji)** | POST | `/api/jobs` | Closure | ✓ ensureBothLanguages + create |
| | GET | `/api/jobs/my` | **MyJobsController::apiIndex** | ✓ Job accessors |
| | GET | `/api/jobs/{job}` | **JobViewController::apiShow** | ✓ Job accessor |
| | GET | `/api/jobs/{job}/edit` | **JobController::apiEdit** | ✓ |
| | PUT | `/api/jobs/{job}` | **JobController::apiUpdate** | ✓ ensureBothLanguages + update |
| | GET | `/api/jobs/{job}/poll` | Closure | - |
| | GET | `/api/jobs/{job}/payment-status` | **PaymentController::apiPoll** | - |
| | POST | `/api/jobs/{job}/retry-payment` | **JobController::apiRetryPayment** | - |
| | POST | `/api/jobs/{job}/cancel` | **JobController::apiCancel** | - |
| **Worker jobs** | POST | `/api/worker/jobs` | Closure | ✓ ensureBothLanguages + create (free/wallet/zenopay) |
| | GET | `/api/worker/assigned` | **WorkerActionsController::apiAssigned** | ✓ Job accessors |
| **Feed** | GET/POST | `/api/feed` | **FeedController::apiIndex** | ✓ Job accessors + distance labels via __() |
| | GET/POST | `/api/feed/map` | **FeedController::apiMap** | ✓ Job accessors + distance labels |
| **Chat** | GET | `/api/chat/{job}` | Closure | ✓ $job → accessors |
| **Withdrawal** | GET/POST | `/api/withdrawal/*` | Closures + WithdrawalController | ✓ lang keys (withdrawal.*) |
| **Admin** | GET | `/api/admin/jobs` | Closure | ✓ Job::with() → accessors |

---

## Controllers na methods (zilizo na logic ya job/category)

- **AuthController:** apiRegister, apiLogin, apiLogout, getuser, updateToken, updateProfile
- **JobController:** apiEdit, apiUpdate, apiRetryPayment, apiCancel (store/update web pia zina translation)
- **JobViewController:** apiShow
- **MyJobsController:** apiIndex
- **FeedController:** apiIndex, apiMap (distance labels sasa zinatumia `__('distance.unknown')` n.k.)
- **WorkerActionsController:** apiAssigned, apiAccept, apiDecline, apiComplete
- **PaymentController:** apiPoll, webhook
- **ApiDashboardController:** updates
- **ChatController:** apiSend
- **WithdrawalController / Admin:** withdrawal + admin endpoints

---

## Hitimisho

- **Ndiyo:** APIs routes zote zimeunganishwa na localization:
  - **Job create** (muhitaji na mfanyakazi) zina `TranslationService::ensureBothLanguages()` na kuhifadhi columns za lugha.
  - **Job update** (API) inatumia `JobController::apiUpdate` yenye translation.
  - **Responses** zinazorudisha Job au Category hutumia accessors za model + locale iliyowekwa na middleware; hakuna route maalum inayohitaji mabadiliko ya ziada.
- **Distance labels** katika Feed (apiIndex, apiMap) zimesasishwa kutumia `__('distance.unknown')` badala ya maandishi maalum.

Kuthibitisha kwa mtihani: tumia header `Accept-Language: sw` au `Accept-Language: en` kwenye request na angalia kuwa `title`/`description`/`name` katika JSON ziko kwa lugha inayotakiwa.
