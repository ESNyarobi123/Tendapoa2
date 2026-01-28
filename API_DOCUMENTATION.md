# API Documentation - Tendapoa

Hii hapa ni orodha kamili ya API endpoints kwa ajili ya Public, Muhitaji (Client), na Mfanyakazi (Worker).
**Base URL:** `https://your-domain.com/api`

---

## 🔐 1. Authentication (Usajili & Kuingia)

### Register User
**Endpoint:** `POST /auth/register`
**Description:** Sajili mtumiaji mpya.

**Request Body:**
```json
{
    "name": "Juma Juma",
    "email": "juma@email.com",
    "phone": "0755123456",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "mfanyakazi", // au "muhitaji"
    "nida": "12345678901234567890", // Optional for muhitaji
    "lat": -6.7924, // Optional
    "lng": 39.2083 // Optional
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Usajili umekamilika!",
    "user": {
        "id": 1,
        "name": "Juma Juma",
        "role": "mfanyakazi",
        ...
    },
    "token": "1|laravel_sanctum_token..."
}
```

### Login
**Endpoint:** `POST /auth/login`

**Request Body:**
```json
{
    "email": "juma@email.com", // au phone
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "token": "1|laravel_sanctum_token...",
    "user": { ... }
}
```

### User Profile
**Endpoint:** `GET /auth/user`
**Headers:** `Authorization: Bearer <token>`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Juma Juma",
        "email": "juma@email.com",
        "role": "mfanyakazi",
        "profile_photo_url": "...",
        "lat": -6.79,
        "lng": 39.20
    }
}
```

---

## 🌍 2. Public Data (No Auth Required)

### Get Categories
**Endpoint:** `GET /categories`

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
        ...
    ]
}
```

### Get Home Data
**Endpoint:** `GET /home`
**Description:** Inarudisha stats na categories kwa ajili ya Home page ya App.

**Response:**
```json
{
    "success": true,
    "data": {
        "categories": [ ... ],
        "stats": {
            "total_workers": 150,
            "completed_jobs": 500,
            "active_users": 1200
        }
    }
}
```

### Nearby Workers (Kwa ajili ya Ramani)
**Endpoint:** `GET /workers/nearby`
**Params:** `lat`, `lng`, `radius` (km)

**Response:**
```json
{
    "success": true,
    "data": {
        "worker_count": 5,
        "status": "workers_found",
        "message": "Kuna wafanyakazi 5 karibu nawe!",
        "by_distance": {
            "within_1km": 2,
            "within_3km": 5
        }
    }
}
```

---

## 📊 3. Dashboard (Auth Required)

### Get Dashboard Data
**Endpoint:** `GET /dashboard`
**Description:** Inarudisha data tofauti kulingana na user role (Muhitaji au Mfanyakazi).

**Response (Mfanyakazi):**
```json
{
    "success": true,
    "data": {
        "role": "mfanyakazi",
        "done": 12,
        "earnTotal": 150000,
        "available": 25000, // Wallet balance
        "currentJobs": [ ... ], // Kazi zinazoendelea
        "earningsHistory": [ ... ]
    }
}
```

**Response (Muhitaji):**
```json
{
    "success": true,
    "data": {
        "role": "muhitaji",
        "posted": 5,
        "completed": 3,
        "allJobs": [ ... ]
    }
}
```

---

## 💼 4. Client Actions (Muhitaji)

### Create Job (Chapisha Kazi)
**Endpoint:** `POST /jobs`
**Content-Type:** `multipart/form-data`

**Request Body:**
```json
{
    "title": "Natafuta Fundi Bomba",
    "category_id": 1,
    "price": 20000,
    "description": "Bomba la sinki limepasuka...",
    "lat": -6.7924,
    "lng": 39.2083,
    "phone": "0755123456",
    "address_text": "Sinza Madukani",
    "image": (File) // Optional
}
```

**Response:**
```json
{
    "success": true,
    "message": "Kazi imeundwa. Fanya malipo.",
    "data": {
        "job": { ... },
        "payment_url": "...", // Ikiwa malipo yanahitajika
        "zenopay_response": { ... }
    }
}
```

### My Jobs (Kazi Zangu)
**Endpoint:** `GET /jobs/my`

**Response:**
```json
{
    "jobs": [
        {
            "id": 10,
            "title": "Fundi Bomba",
            "status": "posted",
            "comments_count": 3,
            "image_url": "http://...",
            "created_at": "..."
        }
    ],
    "status": "success"
}
```

### Accept Worker (Chagua Mfanyakazi)
**Endpoint:** `POST /jobs/{job_id}/accept/{comment_id}`
**Description:** Mteja anamchagua mfanyakazi aliyetuma maombi.

**Response:**
```json
{
    "success": true,
    "message": "Umemchagua mfanyakazi.",
    "data": {
        "id": 10,
        "status": "assigned",
        "completion_code": "123456", // Muhimu: Code ya kumpa mfanyakazi mwishoni
        ...
    }
}
```

### Check Payment Status
**Endpoint:** `GET /jobs/{job_id}/payment-status`

**Response:**
```json
{
    "done": true,
    "status": "completed",
    "payment": { "amount": 20000, "status": "COMPLETED" }
}
```

### Cancel Job
**Endpoint:** `POST /jobs/{job_id}/cancel`

**Response:**
```json
{
    "success": true,
    "message": "Kazi imefutwa na pesa imerudishwa kwenye wallet yako."
}
```

---

## 🛠️ 5. Worker Actions (Mfanyakazi)

### Get Assigned Jobs (Kazi Nilizopewa)
**Endpoint:** `GET /worker/assigned`

**Response:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 15,
                "title": "Kazi ya Usafi",
                "status": "assigned", // au 'in_progress', 'ready_for_confirmation'
                "muhitaji": { "name": "Client Name", "phone": "..." },
                "price": 10000
            }
        ]
    }
}
```

### Accept Assigned Job (Kubali Kazi)
**Endpoint:** `POST /worker/jobs/{job_id}/accept`

**Response:**
```json
{
    "success": true,
    "message": "Umeikubali kazi.",
    "completion_code": "458921" // Hii ni code ya uthibitisho (ingawa client ndiye anatoa code yake)
}
```

### Complete Job (Maliza Kazi na Code)
**Endpoint:** `POST /worker/jobs/{job_id}/complete`
**Description:** Mfanyakazi anaingiza code aliyopewa na mteja ili kupata malipo.

**Request Body:**
```json
{
    "code": "123456" // Code kutoka kwa mteja
}
```

**Response:**
```json
{
    "success": true,
    "message": "Kazi imethibitishwa! Utapokea malipo yako.",
    "amount_earned": 18000 // Baada ya makato
}
```

### Post Service (Chapisha Huduma/Kazi kama Mfanyakazi)
**Endpoint:** `POST /worker/jobs`
**Description:** Mfanyakazi anachapisha 'Service' yake ili ionekane kwenye feed.

**Request Body:**
```json
{
    "title": "Nafanya Usafi wa Bustani",
    "category_id": 3,
    "description": "Nina vifaa vya kisasa...",
    "price": 30000,
    "lat": -6.7,
    "lng": 39.2,
    "phone": "0755..."
}
```

**Response:**
```json
{
    "success": true,
    "message": "Kazi imechapishwa kwa mafanikio!",
    "payment_method": "wallet" // au 'zenopay' ikiwa salio halitoshi
}
```

---

## 📡 6. Feed & Interactions (Shared)

### Browse Jobs (Feed)
**Endpoint:** `GET /feed`
**Params:** `category` (slug), `distance` (km)

**Response:**
```json
{
    "status": "success",
    "jobs": [
        {
            "id": 20,
            "title": "Fix TV",
            "price": 15000,
            "distance_info": {
                "distance": 2.5,
                "label": "Karibu"
            },
            "image_url": "..."
        }
    ],
    "pagination": { ... }
}
```

### Get Job Details
**Endpoint:** `GET /jobs/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 20,
        "title": "Fix TV",
        "description": "...",
        "muhitaji": { ... },
        "comments": [ ... ] // Maombi/Comments za wafanyakazi
    }
}
```

### Post Comment / Apply (Tuma Maombi)
**Endpoint:** `POST /jobs/{job_id}/comment`
**Description:** Mfanyakazi anaomba kazi kwa kuweka comment/bid.

**Request Body:**
```json
{
    "message": "Naweza kufanya hii kazi. Nina uzoefu.",
    "bid_amount": 15000, // Optional
    "is_application": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "Maoni yamewekwa."
}
```

---

## 💬 7. Chat & Notifications

### Get Notifications
**Endpoint:** `GET /notifications`

**Response:**
```json
{
    "success": true,
    "unread_count": 2,
    "notifications": {
        "data": [
            {
                "id": "uuid...",
                "data": {
                    "title": "Malipo yameingia",
                    "message": "Umepokea TZS 15,000",
                    "type": "payment_received"
                },
                "read_at": null
            }
        ]
    }
}
```

### Mark All Read
**Endpoint:** `POST /notifications/read-all`

---

### Get Chat List
**Endpoint:** `GET /chat`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "job": { "title": "Fix TV" },
            "other_user": { "name": "Fundi John" },
            "last_message_at": "...",
            "unread_count": 1
        }
    ]
}
```

### Get Messages (Specific Job)
**Endpoint:** `GET /chat/{job_id}`

**Response (List of messages):**
```json
[
    {
        "id": 1,
        "message": "Habari, uko wapi?",
        "sender_id": 5,
        "is_read": 1,
        "created_at": "..."
    }
]
```

### Send Message
**Endpoint:** `POST /jobs/{job_id}/send`

**Request Body:**
```json
{
    "message": "Niko njiani nakuja.",
    "receiver_id": 5 // ID ya anayepokea (mfanyakazi au muhitaji)
}
```

**Response:**
```json
{
    "success": true,
    "message": "Imetumwa"
}
```
