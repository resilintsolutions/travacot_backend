````markdown
# Travacot – Hotelbeds Integration (Frontend API Guide)

This document describes how your frontend should talk to your Laravel API to:

- Search hotels
- Show room types and rates
- Re-check rates (CheckRate)
- Create Stripe checkouts
- Complete Hotelbeds bookings (via backend webhooks)
- Cancel bookings
- Fetch voucher data (JSON)

---

## 1. Search Hotels (Availability)

### Endpoint

```http
POST /api/search
```
````

### Request Body

```json
{
  "destination": "BCN", // OR: "hotelIds": "311,456"
  "checkIn": "2025-03-10",
  "checkOut": "2025-03-12",
  "guests": {
    "adults": 2,
    "children": 1,
    "childrenAges": [8]
  },
  "currency": "EUR",
  "returnDailyRate": true // optional: ask Hotelbeds to include dailyRate
}
```

- Send **either** `destination` (Hotelbeds destination code)
  **or** `hotelIds` (comma-separated Hotelbeds hotel codes).
- `returnDailyRate` controls Hotelbeds `dailyRate` flag (boolean).

### Response (Simplified)

```json
{
  "success": true,
  "results": [
    {
      "code": 311,
      "name": "Example Hotel Barcelona",

      "countryName": "Spain",
      "cityName": "Barcelona",
      "countryCode": "ES",
      "cityCode": "BCN",
      "destinationName": "Barcelona",
      "location": "Some street 123, Barcelona",

      "category": "4 STARS",
      "rating": 4,

      "lowestRateNet": 200.0, // Hotelbeds net (whole stay)
      "lowestRate": 230.0, // Travacot selling price (after MSP / margin)
      "highestRateNet": 400.0,
      "highestRate": 460.0,
      "currency": "EUR",
      "marginPercent": 15.0,
      "msp": 180.0,

      "roomName": "Double Standard",
      "boardName": "Breakfast included",
      "paymentType": "AT_HOTEL", // or "AT_WEB"
      "rateType": "RECHECK", // or "BOOKABLE"
      "allotment": 3,
      "roomsLeftLabel": "Only 3 left at this price on our site!",
      "freeCancellation": true,
      "freeCancellationUntil": "2025-03-08T23:59:00",
      "noPrepaymentNeeded": true,

      "taxesTotal": 18.0,
      "nights": 2,
      "adults": 2,
      "children": 1,

      "images": ["https://photos.hotelbeds.com/giata/...", "..."],

      "recommended": false,
      "totalReviews": 0
    }
  ]
}
```

### Frontend Use

- Render your **hotel list** from `results[]`.
- For room-level details and `rateKey`, call **Get Room Types** (next section).

---

## 2. Get Room Types for a Hotel

### Endpoint

```http
GET /api/hotels/{hotelCode}/rooms
```

### Example

```http
GET /api/hotels/311/rooms?checkIn=2025-03-10&checkOut=2025-03-12&adults=2&children=1
```

### Response (Simplified)

```json
{
  "success": true,
  "hotel": {
    "id": 311,
    "name": "Example Hotel Barcelona",
    "description": "...",
    "address": "Some street 123, Barcelona",
    "categoryCode": "4ST",
    "categoryName": "4 STARS",
    "destinationCode": "BCN",
    "destinationName": "Barcelona",
    "latitude": 41.38,
    "longitude": 2.17,
    "currency": "EUR",
    "images": ["..."],
    "houseRules": {
      "checkIn": {
        "from": "14:00:00",
        "to": "23:00:00",
        "description": ["Check-in from 14:00"]
      },
      "checkOut": {
        "from": "07:00:00",
        "to": "12:00:00",
        "description": ["Check-out until 12:00"]
      },
      "cancellationPrepayment": "Some policy text ...",
      "childrenAndBeds": {
        "summary": "Children above 18 will be charged as adults...",
        "beds": ["Extra beds on demand"],
        "note": "Cots/Extra Bed are NOT included in the total price..."
      },
      "ageRestrictions": "No age restriction",
      "pets": "Pets are not allowed.",
      "paymentMethods": ["VISA", "MASTERCARD", "AMERICAN EXPRESS"]
    }
  },
  "rooms": [
    {
      "code": "DBT.ST-1",
      "name": "Double Standard",
      "roomSizeSqm": 20,
      "bedType": "Queen Bed",
      "roomFit": "2 adults, 1 children",
      "remainingRooms": 3,
      "images": ["..."],

      "totalPrice": 200.0, // Hotelbeds net for whole stay
      "pricePerNight": 100.0,
      "nights": 2,
      "currency": "EUR",

      "rateKey": "20250310|20250312|W|1|311|DBT.ST|NET|RO||1~2~1||N@ABCD",
      "rateType": "RECHECK", // *** VERY IMPORTANT ***
      "board": "Breakfast included",
      "refundable": true,
      "cancellation": [
        {
          "amount": 0,
          "from": "2025-03-08T23:59:00"
        }
      ],
      "taxes": [],
      "taxesTotal": 18.0,

      "hb_raw": { "...": "full supplier room node" }
    }
  ]
}
```

### Frontend Use

Render the room list on the hotel page from `rooms[]`.

For each selected room you will need:

- `rateKey`
- `rateType` (`BOOKABLE` or `RECHECK`)
- `totalPrice` (net) → required later for `BOOKABLE`
- `currency`, `board`, `refundable`, etc. for UI display

---

## 3. Optional: Check Availability for a Specific Rate (Per Room)

Use this if the user waits a long time on checkout or you want an “are you still available?” check **per room**.

### Endpoint

```http
POST /api/hotels/{hotelCode}/rooms/check-availability
```

### Request Body

```json
{
  "rateKey": "20250310|20250312|W|1|311|DBT.ST|NET|RO||1~2~1||N@ABCD"
}
```

### Response

```json
{
  "success": true,
  "rateKey": "UPDATED_RATE_KEY_FROM_HOTELBEDS",
  "isAvailable": true,
  "availableUnits": 3,
  "net": 200.0,
  "currency": "EUR",
  "cancellation": [],
  "raw": { "...": "full Hotelbeds checkrates response" }
}
```

If `success = false`, show an error like:

> “This room is no longer available. Please choose another option.”

---

## 4. Hotelbeds CheckRate (Multi-room, Protected)

This is the **global CheckRate operation**, aligned with Hotelbeds docs.

### Endpoint

```http
POST /api/hotelbeds/checkrate      // protected (auth:sanctum)
```

### Request Body

Single rate:

```json
{
  "rate_key": "20250310|20250312|W|1|311|DBT.ST|NET|RO||1~2~1||N@ABCD"
}
```

Multiple rates:

```json
{
  "rate_keys": ["KEY_FOR_ROOM_1", "KEY_FOR_ROOM_2"]
}
```

### Response (Simplified)

```json
{
  "success": true,
  "data": {
    "hotel_code": 311,
    "hotel_name": "Example Hotel Barcelona",
    "check_in": "2025-03-10",
    "check_out": "2025-03-12",
    "currency": "EUR",
    "total_net": 400.0,
    "rooms": [
      {
        "room_index": 0,
        "room_name": "Double Standard",
        "board": "Breakfast included",
        "rate_key": "UPDATED_KEY_1",
        "net": 200.0,
        "selling_rate": 200.0,
        "cancellation": []
      }
    ],
    "raw": { "...": "full checkrates response" }
  }
}
```

The backend will update `rate_key` values if Hotelbeds returns new ones.

---

## 5. Stripe Checkout (Reservation + PaymentIntent, No Booking Yet)

After the user selects room(s) and fills guest details, call **checkout**.

### Endpoint

```http
POST /api/reservations/checkout        // protected (auth:sanctum)
```

---

### 5.1 Single-room Example – `rate_type = "RECHECK"`

The backend will call Hotelbeds **CheckRates** internally for this room.

```json
{
  "hotel_id": 311,

  "rooms": [
    {
      "rate_key": "20250310|20250312|W|1|311|DBT.ST|NET|RO||1~2~1||N@ABCD",
      "rate_type": "RECHECK",
      "paxes": [
        { "type": "AD", "age": 35 },
        { "type": "AD", "age": 33 },
        { "type": "CH", "age": 8 }
      ]
    }
  ],

  "holder": {
    "name": "John",
    "surname": "Doe"
  },

  "currency": "EUR",

  "countryCode": "ES",
  "cityCode": "BCN",

  "client_reference": "WEB-20250301-0001",
  "remark": "Non-smoking room, late arrival",
  "channel": "Website",

  "customer_email": "john@example.com",
  "check_in": "2025-03-10",
  "check_out": "2025-03-12"
}
```

---

### 5.2 Single-room Example – `rate_type = "BOOKABLE"`

The backend **won’t** call CheckRates; it trusts your `net` price.

```json
{
  "hotel_id": 311,

  "rooms": [
    {
      "rate_key": "20250310|20250312|W|1|311|DBT.ST|NET|RO||1~2~1||N@ABCD",
      "rate_type": "BOOKABLE",
      "net": 200.0, // from rooms[].totalPrice
      "paxes": [
        { "type": "AD", "age": 35 },
        { "type": "AD", "age": 33 },
        { "type": "CH", "age": 8 }
      ]
    }
  ],

  "holder": {
    "name": "John",
    "surname": "Doe"
  },

  "currency": "EUR",
  "countryCode": "ES",
  "cityCode": "BCN"
}
```

> ⚠️ For `BOOKABLE`, the field **`net` is required**.
> If missing, the API will return **422**.

---

### 5.3 Multi-room Example (2 Rooms)

```json
{
  "hotel_id": 311,

  "rooms": [
    {
      "rate_key": "KEY_ROOM_1",
      "rate_type": "RECHECK",
      "paxes": [
        { "type": "AD", "age": 35 },
        { "type": "AD", "age": 33 }
      ]
    },
    {
      "rate_key": "KEY_ROOM_2",
      "rate_type": "RECHECK",
      "paxes": [
        { "type": "AD", "age": 30 },
        { "type": "CH", "age": 8 }
      ]
    }
  ],

  "holder": {
    "name": "John",
    "surname": "Doe"
  },

  "currency": "EUR",
  "countryCode": "ES",
  "cityCode": "BCN",
  "customer_email": "john@example.com"
}
```

---

### 5.4 Response from Checkout

```json
{
  "success": true,
  "reservation_id": 123,
  "amount": 252.5, // final selling price (after MSP + margin)
  "currency": "EUR",
  "client_secret": "pi_12345_secret_67890"
}
```

### Frontend Steps After This

1. Use `client_secret` with **Stripe.js** to confirm the PaymentIntent.
2. Stripe will send webhooks to your backend when:
   - `payment_intent.succeeded` → backend calls Hotelbeds `/bookings`.
   - `payment_intent.payment_failed` → backend marks reservation as `payment_failed`.

3. After success, frontend can poll:

   ```http
   GET /api/reservations/{reservation_id}
   ```

   until `status` is `"confirmed"` and `confirmation_number` is set.

---

## 6. Booking Cancellation

### Endpoint

```http
DELETE /api/reservations/{reservationId}     // protected (auth:sanctum)
```

Backend behaviour:

- If the reservation has a **Hotelbeds reference** (`supplier_reference`):
  - Calls Hotelbeds **BookingCancellation**:

    ```http
    DELETE /hotel-api/1.0/bookings/{bookingId}?cancellationFlag=CANCELLATION&language=ENG
    ```

  - If Hotelbeds cancellation **succeeds** → marks reservation as `cancelled`.
  - If Hotelbeds cancellation **fails** → returns error, does **not** cancel locally.

- If no supplier reference → just marks reservation as `cancelled` locally.

### Response (success)

```json
{
  "success": true,
  "hotelbeds": {
    "...": "raw Hotelbeds cancellation response (if any)"
  }
}
```

If supplier cancellation fails:

```json
{
  "success": false,
  "stage": "supplier-cancel",
  "error": { "...": "Hotelbeds error object" },
  "raw": "..."
}
```

---

## 7. Voucher (JSON)

Voucher is provided as **JSON** so frontend can render the booking confirmation page.

### Endpoint

```http
GET /api/reservations/{reservationId}/voucher    // protected (auth:sanctum)
```

### Response (Example)

```json
{
  "success": true,
  "voucher": {
    "reservation_id": 123,
    "confirmation_number": "52-1274417",
    "supplier_reference": "52-1274417",
    "status": "confirmed",
    "currency": "EUR",
    "total_price": 252.5,

    "hotel": {
      "id": 45,
      "vendor": "hotelbeds",
      "vendor_id": "311",
      "name": "Example Hotel Barcelona",
      "category": "4 STARS",
      "address": "Some street 123",
      "city": "Barcelona",
      "country": "Spain",
      "phone": "+34 000 000 000",
      "destinationCode": "BCN",
      "countryCode": "ES"
    },

    "stay": {
      "check_in": "2025-03-10",
      "check_out": "2025-03-12",
      "nights": 2
    },

    "holder": {
      "name": "John",
      "surname": "Doe"
    },

    "paxes": [
      {
        "roomId": 1,
        "type": "AD",
        "age": 35,
        "name": "John",
        "surname": "Doe"
      },
      {
        "roomId": 1,
        "type": "AD",
        "age": 33,
        "name": "Jane",
        "surname": "Doe"
      },
      { "roomId": 1, "type": "CH", "age": 8, "name": "Child", "surname": "Doe" }
    ],

    "client_reference": "WEB-20250301-0001",
    "remark": "Non-smoking room, late arrival",

    "supplier_raw": {
      "...": "optional raw Hotelbeds booking data if available"
    }
  }
}
```

### Frontend Use

- Use this to render the **voucher / booking confirmation page**.
- Can also be used to generate an email template or PDF on your side.

---

## 8. Frontend Flow Summary (B2C with Stripe)

1. **Search page**
   - `POST /api/search`
   - Show hotels from `results[]`.

2. **Hotel details page**
   - `GET /api/hotels/{code}/rooms?...`
   - Show room types and prices.
   - Let user pick room(s).

3. **(Optional) CheckRate**
   - `POST /api/hotelbeds/checkrate` (for multi-room)
     or `POST /api/hotels/{code}/rooms/check-availability` (per room)
   - Use when `rateType = "RECHECK"` or when the user takes long to confirm.

4. **Checkout page**
   - Build `rooms[]` with:
     - `rate_key` (from room)
     - `rate_type` (`BOOKABLE` or `RECHECK`)
     - `net` (for `BOOKABLE`)
     - `paxes[]` (type + age per pax)

   - `POST /api/reservations/checkout`
   - Use `client_secret` with Stripe.js to pay.

5. **After payment**
   - Backend (via Stripe webhook) calls Hotelbeds `/bookings`.
   - Frontend polls `GET /api/reservations/{id}` until:
     - `status = "confirmed"`
     - `confirmation_number` is present.

6. **Confirmation page**
   - `GET /api/reservations/{id}/voucher`
   - Render all booking details (hotel info, pax, dates, reference).

7. **Cancellation (if allowed)**
   - `DELETE /api/reservations/{id}`
   - Backend calls Hotelbeds BookingCancellation and updates reservation.

---

```

```
