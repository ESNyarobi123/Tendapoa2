# 📘 Tendapoa - API Documentation (Complete & Verified)

Hii ni orodha kamili (Extensive List) ya APIs zote zinazotumika kwenye mfumo wa Tendapoa. Kila API ina mfano wa data ya kutuma (Request) na data inayorudi (Response).

**Base URL:** `https://your-domain.com/api`

---

## 🔐 1. Authentication (Usajili na Kuingia)

### Register (Sajili Mtumiaji)
**Method:** `POST`
**Endpoint:** `/auth/register`
**Description:** Sajili user mpya. Kwa mfanyakazi, tuma `lat` na `lng` ili aonekana kwenye ramani.

**Request Body:**
```json
{
    "name": "Juma Fundi",
    "email": "juma@email.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "mfanyakazi", // Au "muhitaji"
    "phone": "0755123456",
    "lat": -6.7924, // Optional (muhimu kwa worker)
    "lng": 39.2083  // Optional (muhimu kwa worker)
}
```

**Response Success:**
```json
{
    "success": true,
    "message": "Akaunti imeundwa kwa mafanikio!",
    "user": {
        "id": 1,
        "name": "Juma Fundi",
        "email": "juma@email.com",
        "role": "mfanyakazi",
        "phone": "0755123456"
    }
}
```

### Login (Ingia)
**Method:** `POST`
**Endpoint:** `/auth/login`

**Request Body:**
```json
{
    "email": "juma@email.com",
    "password": "password123"
}
```

**Response Success:**
```json
{
    "success": true,
    "message": "Umeingia kwa mafanikio!",
    "token": "1|laravel_sanctum_tokenz...", // <--- HII TOKEN TUNZA (SAVE LOCALLY)
    "user": {
        "id": 1,
        "name": "Juma Fundi",
        "email": "juma@email.com",
        "role": "mfanyakazi"
    }
}
```

### Get User Profile
**Method:** `GET`
**Endpoint:** `/auth/getuser`
**Headers:** `Authorization: Bearer <token>`

**Response:**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "name": "Juma Fundi",
        "email": "juma@email.com",
        "role": "mfanyakazi",
        "profile_photo_url": "http://domain.com/storage/profile-photos/image.jpg"
    }
}
```

### Update Profile (Badili Picha au Data)
**Method:** `POST`
**Endpoint:** `/auth/profile/update`
**Description:** Badili jina, simu, au picha.

**Request Body (Multipart/Form-Data):**
*   `name`: "Juma Kapanya" (Optional)
*   `phone`: "0655..." (Optional)
*   `photo`: (File/Image) (Optional)

**Response:**
```json
{
    "success": true,
    "message": "Wasifu umesasishwa kikamilifu.",
    "photo_url": "http://domain.com/storage/..."
}
```

### Update FCM Token (Notifications)
**Method:** `POST`
**Endpoint:** `/auth/fcm-token`

**Request Body:**
```json
{
    "token": "fcm_registration_token_from_firebase..."
}
```

### Logout
**Method:** `POST`
**Endpoint:** `/auth/logout`
**Response:** `{ "success": true, "message": "Umetoka kwa mafanikio!" }`

---

## 🌍 2. Public Data & Home

### Get Categories
**Method:** `GET`
**Endpoint:** `/categories`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Ujenzi",
            "slug": "ujenzi",
            "icon": "🏗️"
        },
        {
            "id": 2,
            "name": "Usafi",
            "slug": "usafi",
            "icon": "🧹"
        }
    ]
}
```

### Get Home Stats
**Method:** `GET`
**Endpoint:** `/home`

**Response:**
```json
{
    "success": true,
    "data": {
        "categories": [...],
        "stats": {
            "total_workers": 120,
            "completed_jobs": 450
        }
    }
}
```

### Check Nearby Workers (Kabla ya Kupost)
**Method:** `GET`
**Endpoint:** `/workers/nearby`
**Description:** Angalia kama kuna mafundi karibu na eneo la mteja.
**Params:** `?lat=-6.7&lng=39.2&radius=5`

**Response:**
```json
{
    "success": true,
    "data": {
        "worker_count": 3,
        "status": "workers_found",
        "message": "Kuna wafanyakazi 3 karibu nawe!"
    }
}
```

---

## 📡 3. Feed & Map (Kutafuta Kazi)

### Job Feed (List View)
**Method:** `GET`
**Endpoint:** `/feed`
**Params:**
*   `category`: `ujenzi` (Optional)
*   `distance`: `10` (km - Optional)

**Response:**
```json
{
    "status": "success",
    "jobs": [
        {
            "id": 10,
            "title": "Kurekebisha Bomba",
            "price": 20000,
            "image_url": "http://...",
            "created_at": "2024-02-01T10:00:00Z",
            "distance_info": {
                "distance": 2.4,
                "label": "Karibu"
            },
            "category": { "name": "Ufundi", "icon": "🛠️" }
        }
    ],
    "pagination": { "current_page": 1, "total": 50 }
}
```

### Job Map (Map View)
**Method:** `GET`
**Endpoint:** `/feed/map`

**Response (List for Map Markers):**
```json
{
    "status": "success",
    "user_location": { "lat": -6.77, "lng": 39.22 },
    "jobs": [
        {
            "id": 10,
            "title": "Kurekebisha Bomba",
            "lat": -6.7924,
            "lng": 39.2083,
            "image_url": "http://...",
            "category": { "icon": "🛠️" }
        }
    ]
}
```

---

## 💼 4. Job Management (Client / Muhitaji)

### Post Job (Unda Kazi)
**Method:** `POST`
**Endpoint:** `/jobs`

**Request Body:**
```json
{
    "title": "Nataka Fundi Rangi",
    "category_id": 2,
    "price": 50000,
    "description": "Chumba kimoja...",
    "lat": -6.7924,
    "lng": 39.2083,
    "phone": "0755000111",
    "address_text": "Sinza Kijiweni",
    "image": (File) // Optional
}
```

**Response:**
```json
{
    "success": true,
    "message": "Kazi imeundwa. Fanya malipo.",
    "data": {
        "job": { "id": 15, "status": "pending_payment" },
        "payment": { "amount": 50000 },
        "zenopay_response": { ... } // Data za malipo
    }
}
```

### Poll Payment Status
**Method:** `GET`
**Endpoint:** `/jobs/{id}/poll`
**Description:** Check kama malipo yamekamilika.

**Response:**
```json
{
    "success": true,
    "done": true, // Ikiwa true, malipo yamepita
    "status": "COMPLETED"
}
```

### My Jobs (Kazi Zangu)
**Method:** `GET`
**Endpoint:** `/jobs/my`

**Response:**
```json
[
    {
        "id": 15,
        "title": "Fundi Rangi",
        "status": "posted", // au 'assigned', 'completed'
        "comments_count": 2, // Idadi ya maombi
        "image_url": "..."
    }
]
```

### Get Job Details (Na Comments za Wafanyakazi)
**Method:** `GET`
**Endpoint:** `/jobs/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 15,
        "title": "Fundi Rangi",
        "status": "posted",
        "comments": [ // Maombi ya wafanyakazi
            {
                "id": 5, // Comment ID
                "message": "Niko tayari kufanya kazi hii",
                "user": { "id": 9, "name": "Fundi Chapkazi" }
            }
        ]
    }
}
```

### Select Worker (Kubali Mfanyakazi)
**Method:** `POST`
**Endpoint:** `/jobs/{jobId}/accept/{commentId}`

**Response:**
```json
{
    "success": true,
    "message": "Umemchagua mfanyakazi.",
    "data": {
        "id": 15,
        "status": "assigned",
        "completion_code": "123456" // CODE MUHIMU
    }
}
```
*Note: Hii pia inafungua chat na kutuma meseji ya kwanza.*

### Edit Job (Badili Kazi)
**Method:** `PUT`
**Endpoint:** `/jobs/{id}`

**Request Body:**
```json
{
    "title": "Nataka Fundi Rangi na Marekebisho",
    "category_id": 2,
    "price": 60000, // Imeongezeka
    "lat": -6.7,
    "lng": 39.2
}
```

**Response:**
```json
{
    "message": "Kazi imebadilishwa! Malipo ya ziada yanahitajika.",
    "payment_required": true,
    "payment_amount": 10000
}
```

### Cancel Job
**Method:** `POST`
**Endpoint:** `/jobs/{id}/cancel`

**Response:**
```json
{
    "success": true,
    "message": "Kazi imefutwa na pesa imerudishwa kwenye wallet yako."
}
```

---

## 🛠️ 5. Worker Actions (Mfanyakazi)

### Post Service (Jitangaze)
**Method:** `POST`
**Endpoint:** `/worker/jobs`

**Request Body:**
```json
{
    "title": "Narekebisha Friji",
    "category_id": 3,
    "description": "Friji aina zote...",
    "price": 25000,
    "lat": -6.7,
    "lng": 39.2,
    "phone": "0655..."
}
```

### Get Assigned Jobs (Kazi Nilizopewa)
**Method:** `GET`
**Endpoint:** `/worker/assigned`

**Response:**
```json
{
    "success": true,
    "data": {
        "data": [
            {
                "id": 15,
                "title": "Fundi Rangi",
                "status": "assigned",
                "muhitaji": { "name": "Mteja", "phone": "..." }
            }
        ]
    }
}
```

### Accept Job (Kubali Kazi)
**Method:** `POST`
**Endpoint:** `/worker/jobs/{id}/accept`

**Response:** `{ "success": true, "message": "Umeikubali kazi." }`

### Decline Job (Kataa Kazi)
**Method:** `POST`
**Endpoint:** `/worker/jobs/{id}/decline`

**Response:** `{ "success": true, "message": "Umeikataa kazi." }`

### Post Comment / Apply (Omba Kazi)
**Method:** `POST`
**Endpoint:** `/jobs/{id}/comment`

**Request Body:**
```json
{
    "message": "Naomba kazi hii nipo karibu.",
    "is_application": true
}
```

### Complete Job (Maliza Kazi na Code)
**Method:** `POST`
**Endpoint:** `/worker/jobs/{id}/complete`
**Description:** Hakiki code uliyopewa na mteja ili kupokea malipo.

**Request Body:**
```json
{
    "code": "123456"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Kazi imethibitishwa! Utapokea malipo yako.",
    "amount_earned": 22500
}
```

---

## 💬 6. Communication (Chat)

### Get Conversations (Inbox)
**Method:** `GET`
**Endpoint:** `/chat`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "job": { "id": 15, "title": "Fundi Rangi" },
            "other_user": { "name": "Mteja" },
            "unread_count": 2,
            "last_message_at": "..."
        }
    ]
}
```

### Get Messages (Chat Room)
**Method:** `GET`
**Endpoint:** `/chat/{jobId}`

**Response:**
```json
{
    "success": true,
    "data": {
        "messages": [
            {
                "id": 101,
                "message": "Habari, nakuja sasa.",
                "sender_id": 9,
                "is_read": 1,
                "created_at": "..."
            }
        ]
    }
}
```

### Send Message
**Method:** `POST`
**Endpoint:** `/chat/{jobId}/send`

**Request Body:**
```json
{
    "message": "Sawa, nimefika getini.",
    "receiver_id": 1
}
```

**Response:** `{ "success": true, "message": "Imetumwa" }`

### Poll Messages (Real-time update)
**Method:** `GET`
**Endpoint:** `/chat/{jobId}/poll`
**Params:** `?last_id=101`

**Response:**
```json
{
    "success": true,
    "data": {
        "messages": [ { "id": 102, "message": "Karibu ndani.", ... } ],
        "count": 1
    }
}
```

### Unread Count Global
**Method:** `GET`
**Endpoint:** `/chat/unread-count`
**Response:** `{ "success": true, "count": 2 }`

---

## 💰 7. Dashboard & Wallet

### Get Dashboard Data
**Method:** `GET`
**Endpoint:** `/dashboard`

**Response (Mfanyakazi):**
```json
{
    "success": true,
    "data": {
        "role": "mfanyakazi",
        "available": 22500, // SALIO
        "earnTotal": 150000,
        "done": 10,
        "currentJobs": [...],
        "earningsHistory": [
            { "amount": 22500, "description": "Malipo ya Kazi #15", "type": "EARN" }
        ]
    }
}
```

### Check Wallet Balance
**Method:** `GET`
**Endpoint:** `/withdrawal/wallet`
**Response:** `{ "success": true, "data": { "balance": 22500 } }`

### Request Withdrawal (Toa Pesa)
**Method:** `POST`
**Endpoint:** `/withdrawal/submit`

**Request Body:**
```json
{
    "amount": 10000,
    "phone_number": "0755123456",
    "network_type": "vodacom",
    "method": "mobile_money", // au "bank"
    "registered_name": "Juma Fundi"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Withdrawal imewasilishwa."
}
```

### Withdrawal History
**Method:** `GET`
**Endpoint:** `/withdrawal/history`

---

## 🔔 8. Notifications

### Get Notifications
**Method:** `GET`
**Endpoint:** `/jobs/notifications`

### Mark as Read
**Method:** `POST`
**Endpoint:** `/jobs/notifications/{id}/read`

### Mark All as Read
**Method:** `POST`
**Endpoint:** `/jobs/notifications/read-all`

---

## ⚙️ 9. Settings

### Get Public Settings
**Method:** `GET`
**Endpoint:** `/settings`
**Response:**
```json
{
    "success": true,
    "data": {
        "commission_rate": "10",
        "min_withdrawal": "5000",
        "system_currency": "TZS"
    }
}
```

### System Health
**Method:** `GET`
**Endpoint:** `/health`
**Response:** `{ "status": "ok" }`
