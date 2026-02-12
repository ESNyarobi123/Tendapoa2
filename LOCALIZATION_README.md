# Database Localization (Tendapoa)

## Summary

- **Database:** `work_orders` has `title_sw`, `title_en`, `description_sw`, `description_en`; `categories` has `name_sw`, `name_en`.
- **On write:** User sends `title` and `description` in **either** Swahili or English. Backend detects language, stores both, and uses AI to fill the other (optional).
- **On read (API):** Response has a **single** `title` and `description` (and category `name`). Their values depend on the **Accept-Language** header (`en` or `sw`).
- **Static labels** (e.g. distance, status) use Laravel lang files and the same header.

---

## 1. Migrations

Run:

```bash
php artisan migrate
```

This adds and backfills:

- `work_orders`: `title_sw`, `title_en`, `description_sw`, `description_en`
- `categories`: `name_sw`, `name_en`

---

## 2. API Behaviour

- **Set locale:** API middleware reads `Accept-Language` and sets `app()->setLocale()` to `en` or `sw` (default `en`).
- **Job/Category in JSON:** Only `title`, `description`, and category `name` are returned; values are chosen from `_sw` or `_en` based on locale. Raw `_sw`/`_en` columns are hidden.

Example:

```http
GET /api/feed
Accept-Language: en
```

→ Jobs in response have `"title": "Washing clothes"`, `"description": "Laundry services"` (from `title_en`, `description_en`).

```http
GET /api/feed
Accept-Language: sw
```

→ Same jobs have `"title": "Kufua nguo"`, `"description": "Huduma za kufua nguo"` (from `title_sw`, `description_sw`).

Distance labels (e.g. in feed) use `__('distance.far')` etc., so they also switch with the same header (e.g. "Long distance" vs "Umbali mkubwa").

---

## 3. Translation on Write (AI) – Groq (Recommended)

**Groq (LPU)** is used by default for fast auto-translate: user posts in Swahili → save to `title_sw` → Groq translates → save to `title_en` (and same the other way for English → Swahili).

**.env (required for Groq):**

```env
TRANSLATION_DRIVER=groq
GROQ_API_KEY=gsk_your_key_here
# Optional (defaults below)
GROQ_BASE_URL=https://api.groq.com/openai/v1
GROQ_TRANSLATION_MODEL=llama-3.1-8b-instant
```

**Do not commit your real API key.** Keep it only in `.env` (and `.env` in `.gitignore`).

**Other drivers (optional):**

```env
# No translation: store same text in _sw and _en
TRANSLATION_DRIVER=null

# OpenAI
TRANSLATION_DRIVER=openai
OPENAI_API_KEY=sk-...
OPENAI_TRANSLATION_MODEL=gpt-3.5-turbo

# Google Translate
TRANSLATION_DRIVER=google
GOOGLE_TRANSLATE_API_KEY=...
```

- **Swahili input** → saved to `title_sw` / `description_sw`; translation saved to `title_en` / `description_en`.
- **English input** → saved to `title_en` / `description_en`; translation saved to `title_sw` / `description_sw`.

With `TRANSLATION_DRIVER=null`, both columns are filled with the same text (no API calls).

---

## 4. Lang Files (Static Labels)

- **Location:** `lang/en.php`, `lang/sw.php`
- **Keys used:** `distance.*`, `status.*`, `payment.*`, `withdrawal.*`

Example: `__('distance.far')` → "Long distance" (en) or "Umbali mkubwa" (sw).

To add more labels, add keys under the same structure in both files.

---

## 5. Where Localization Is Applied

| Area | Behaviour |
|------|-----------|
| **API job create/update** | Request body has `title` + `description`; backend uses `TranslationService::ensureBothLanguages()` and saves `title_sw`, `title_en`, `description_sw`, `description_en`. |
| **API job/category responses** | Single `title`, `description`, category `name`; value from `_sw` or `_en` by `Accept-Language`. |
| **Feed / dashboard API** | Same as above; distance and other labels use `__()` so they follow locale. |
| **Web (Blade)** | Uses same models; for web you may set locale from session or a selector and call `app()->setLocale()` before rendering. |

---

## 6. Quick Test

1. Create a job via API with body e.g. `"title": "Kufua nguo"`, `"description": "Huduma ya kufua"`.
2. Call `GET /api/jobs/{id}` with `Accept-Language: sw` → response should have that title/description.
3. Call again with `Accept-Language: en` → response should have the English version (from AI if enabled, or same text if driver is `null`).
4. Check feed: `GET /api/feed` with `Accept-Language: en` → job titles and distance labels in English.

---

## 7. Empty translations (fallback)

If Groq fails or is slow, `title_en` / `title_sw` can be empty. The API **never returns blank**:

- If **Accept-Language: en** and `title_en` is empty → the response uses `title_sw`.
- If **Accept-Language: sw** and `title_sw` is empty → the response uses `title_en`.

So the user always sees one of the two languages, not a blank or error.

---

## 8. Legacy jobs (backfill)

To fill `title_sw` / `title_en` (and descriptions) for **existing jobs** that only have the legacy `title` or one language:

```bash
# Preview only (no DB changes)
php artisan tendapoa:translate-legacy-jobs --dry-run

# Run backfill (uses Groq)
php artisan tendapoa:translate-legacy-jobs

# Optional: chunk size and delay between API calls (rate limit)
php artisan tendapoa:translate-legacy-jobs --chunk=30 --delay=200
```

The command finds jobs where `title_sw` or `title_en` is null/empty, uses existing title/description as source, calls Groq, and saves both languages. Run once (or whenever you add legacy data).

### Final polish: verify before full run

Groq (AI) can sometimes **hallucinate** (odd or wrong translations). Before running the command on 100+ jobs:

1. **Run with a small chunk first** and check the database:
   ```bash
   php artisan tendapoa:translate-legacy-jobs --chunk=10 --dry-run   # see what would run
   php artisan tendapoa:translate-legacy-jobs --chunk=10             # run on 10 only
   ```
2. **Check in DB** that `title_sw` / `title_en` (and descriptions) look correct and readable.
3. If all good, run the full backfill (e.g. `--chunk=50` or default).

---

All set.
