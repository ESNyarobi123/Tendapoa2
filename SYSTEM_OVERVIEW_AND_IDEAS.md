# Tendapoa — Muhtasari wa Mfumo na Maoni (Ideas)

## 1. Nimeelewa Nini (Understanding)

### Backend (Laravel)
- **Wajibu:** Mfumo wa kazi/usafi — **Muhitaji** (mteja) anachapisha kazi, **Mfanyakazi** (worker) anatafuta/omba kazi, malipo kupitia **ZenoPay** na **wallet**, **Admin** anaendesha kila kitu.
- **Flow kuu:**
  - Muhitaji: Register → Dashboard → Chapisha Kazi → Lipa (ZenoPay) → Subiri maoni → Chagua mfanyakazi → Chat → Mfanyakazi amaliza → Mteja athibitishwa (completion code) → Malipo yanaenda wallet ya mfanyakazi.
  - Mfanyakazi: Register → Dashboard → Tafuta Kazi (Feed) → Omba (comment/bid) → Akichaguliwa → Accept/Decline → Fanya kazi → Ingiza code ya mteja → Kazi imekamilika, pesa kwenye wallet → Withdraw.
- **API:** Sanctum, JSON APIs kwa app (Flutter?), dashboard, jobs, feed, chat, withdrawal, admin.
- **Models:** User, Job (work_orders), JobComment, Payment, Wallet, WalletTransaction, Withdrawal, Category, PrivateMessage, Setting, Notifications.

### UI (Web)
- **Landing:** `welcome.blade.php` (Laravel default) na `home.blade.php` (halisi) — route `/` inatumia **home** (Download App, WhatsApp, Huduma, Maeneo, CTA).
- **Baada ya login:** Role-based redirect → **Admin** (admin.dashboard), **Muhitaji** (muhitaji.dashboard), **Mfanyakazi** (mfanyakazi.dashboard).
- **Layout:** `layouts/app` — header inafichwa kwa muhitaji/mfanyakazi (wana **sidebar** peke yao). Wengine wana header na nav (Nyumbani, Kazi Zote, Chapisha Kazi, Dashboard, Mazungumzo, Admin, Logout).
- **Sidebar (user-sidebar):** Kwa muhitaji (Chapisha Kazi, Kazi Zangu, Malipo Yanayosubiri, Toa Pesa) na mfanyakazi (Tafuta Kazi, Kazi Zangu, Chapisha Huduma, Toa Pesa). Pia: Dashibodi, Taarifa, Mazungumzo, Nyumbani, Toka.
- **Pages:** Jobs (create, edit, wait, show), Feed (list), Chat (index, show + poll), My Jobs (muhitaji), Mfanyakazi Assigned, Withdraw, Profile, Notifications, Policy (fees, terms), Admin (dashboard, users, jobs, chats, commissions, analytics, withdrawals, categories, system settings, logs, broadcast, impersonation).

### Nani Anatumia Nini (User vs System)
- **Muhitaji:** Anaelewa “Nina hitaji la usafi → ninachapisha kazi → nalipa → nachagua mfanyakazi → naongea naye → namthibitishia kazi → nimemlipa.”
- **Mfanyakazi:** Anaelewa “Ninaomba kazi → ninakubali → nafanya kazi → naingiza code ya mteja → napata pesa → natoa kwenye simu.”
- **Admin:** Anaelewa “Naona wote, naweza kuthibitisha withdrawals, kuforce complete/cancel, kuchambua, kuset settings.”

---

## 2. Ideas (Mapendekezo ya Kuboresha)

### UI/UX
1. **Uthibitisho wa flow (onboarding)**  
   Baada ya kwanza kuingia: kichupo cha kwanza (tooltip/card) kinachoonyesha “Hatua 1: Chapisha kazi” / “Hatua 1: Tafuta kazi kwenye Feed” — ili mtumiaji mpya aelewe flow.

2. **Huduma za malipo (payment)**  
   Kwenye ukurasa wa “subiri malipo” (wait): onyesha wazi “Umepata link ya malipo? Bonyeza hapa” na status inayojengwa (polling) — “Inasubiri… / Imethibitishwa” na ujumbe wa matumizi (kwa mfanyakazi karibu, nk).

3. **Chat na unread**  
   Onyesha wazi idadi ya ujumbe usiosomwa kwenye sidebar na kwenye list ya mazungumzo; badge wazi na uwezekano wa “mark all read”.

4. **Notifications (taarifa)**  
   Sidebar ina “Taarifa” — ongeza ujumbe wa wazi kwa kila aina (kazi imechapishwa, umechaguliwa, kazi imekamilika, withdrawal imethibitishwa) na link moja kwa moja kwenye kazi/chat.

5. **Empty states**  
   Mahali popote palipo tupu (kazi zangu, feed, assigned, withdrawals): onyesha picha/icon na ujumbe mfupi na kitone kimoja cha hatua inayofuata (mfano: “Hakuna kazi bado — Chapisha kazi ya kwanza”).

6. **Mobile**  
   Sidebar tayari ina mobile (collapse/overlay). Hakikisha matumbo (buttons, form fields) yana touch targets kubwa na kuepuka text ndogo sana; kwenye withdraw/form za kazi, angalia zoom na keyboard (number inputs).

7. **Error/success messages**  
   Badala ya `alert()` au flash tu: komponenti ya toast/notification (juu au chini) inayojulikana kwa success (kijani) na error (nyekundu) ili mtumiaji aone wazi matokeo.

8. **Consistency ya lugha**  
   Baadhi “Dashboard”, “Logout”; nyingine “Dashibodi”, “Toka”. Chagua lugha moja (Sw/En) kwa labels za kawaida na uzingatie hiyo kila mahali.

### Backend / Flow
9. **Completion code kwa mteja**  
   Muhitaji anapaswa kuona wazi “Code ya kuthibitisha kazi: 123456” mahali rahisi (ukurasa wa kazi, chat, au email/taarifa) ili mfanyakazi aombe na aingize — flow iwe wazi kwa wote wawili.

10. **Retry payment**  
    Kwenye kazi zenye `pending_payment`: button ya “Jaribu tena malipo” inayopeleka kwenye ZenoPay tena (tayari una retry-payment route — hakikisha UI inaonyesha hii wazi).

11. **Status ya kazi (labels)**  
    Onyesha status kwa Kiswahili kwa mtumiaji: posted = “Imetangazwa”, assigned = “Mfanyakazi amechaguliwa”, in_progress = “Inaendelea”, ready_for_confirmation = “Inasubiri uthibitisho”, completed = “Imekamilika”, cancelled = “Imefutwa”. Hii inasaidia UX.

12. **Validation na ujumbe**  
    Form validation: ujumbe wa makosa uwe kwa Kiswahili (au lugha iliyochaguliwa) ili mtumiaji asome kwa urahisi.

### Technical (kwa polish)
13. **Routes**  
    Ondoa routes zilizorudiwa kwenye `web.php` (mfanyakazi assigned/accept/decline/complete; admin force-complete mara mbili).

14. **Feed view**  
    Ipo file `feed/\`show.blade.php` (typo — backtick). Irename kwa `feed/show.blade.php` au iunganishe na `jobs.show` ikiwa hutumii view tofauti kwa feed.

15. **AJAX complete (mfanyakazi)**  
    Dashboard ya mfanyakazi inatumia `fetch()` kwa completion code. Hakikisha request ina header `Accept: application/json` (au `X-Requested-With: XMLHttpRequest`) ili Laravel irudishe JSON na modal isome response vizuri.

16. **Dashboard “You’re logged in!”**  
    Ukurasa `dashboard.blade.php` (x-app-layout) unatumika tu kama fallback; kwa muhitaji/mfanyakazi redirect tayari inaenda kwenye role dashboards. Unaweza kuficha au kuondoa content huo ili kuepuka machafuko.

---

## 3. Muhtasari wa Mfumo (Quick Reference)

| Sehemu        | Inafanya nini |
|---------------|----------------|
| **/ (home)**  | Landing: huduma, stats, CTA, Download App, WhatsApp. |
| **Login/Register** | Auth; baada ya login → dashboard kwa role. |
| **Dashboard**  | Admin / Muhitaji / Mfanyakazi — stats, shortcuts, recent activity. |
| **Muhitaji**   | Chapisha kazi → lipa (ZenoPay) → chagua mfanyakazi (comments) → chat → thibitisha (code). |
| **Mfanyakazi** | Feed → omba (comment) → akichaguliwa → accept → fanya kazi → ingiza code → pesa wallet → withdraw. |
| **Chat**       | Mazungumzo kwa kila kazi (muhitaji ↔ mfanyakazi); poll messages. |
| **Wallet/Withdraw** | Salio, omba withdraw; admin inaapprove. |
| **Admin**      | Users, jobs, chats, commissions, analytics, withdrawals, categories, settings, logs, impersonate. |
| **API**        | Auth (Sanctum), categories, home, workers/nearby, settings; dashboard, jobs, feed, chat, withdrawal, admin — zote kwa JSON. |

---

Ili “uboreshe” kwa mtumiaji: anza na **ideas 1, 2, 3, 4, 7, 9, 11** (onboarding, payment UX, chat/notifications, empty states, messages, completion code, status labels). Kisha **13, 14, 15** (cleanup routes, feed file, AJAX complete) kwa technical polish.
