Here's your cleaned and well-organized full backend API specification document, formatted for clarity and easy navigation:

---

# FULL BACKEND API SPECIFICATION

_For: Auth, Search, Reservations, Account, Messages, Favorites, Room Selection, Payment Details_

---

## 1. AUTHENTICATION API

### 1.1 Login (Email & Password)

- **POST** `/api/auth/login`

**Request**

```json
{
  "email": "user@example.com",
  "password": "securePassword123"
}
```

**Response (Success)**

```json
{
  "success": true,
  "token": "JWT_TOKEN_HERE",
  "user": {
    "id": 101,
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

**Response (Error)**

```json
{
  "success": false,
  "message": "Invalid email or password"
}
```

---

### 1.2 Google OAuth Login

Backend handles OAuth callback and returns a JWT.

---

### 1.3 Register

- **POST** `/api/auth/register`

**Request**

```json
{
  "email": "newuser@example.com",
  "password": "securePassword123",
  "name": "John Doe"
}
```

---

## 2. SEARCH API

### 2.1 Search Available Stays

- **POST** `/api/search`

**Request**

```json
{
  "location": "Istanbul, Turkiye",
  "checkIn": "2025-12-15",
  "checkOut": "2025-12-20",
  "guests": {
    "adults": 2,
    "children": 2,
    "childrenAges": ["5", "8"]
  }
}
```

**Response (Success)**

```json
{
  "success": true,
  "results": [
    {
      "id": 101,
      "name": "Hotel Blue Istanbul",
      "location": "Istanbul, Turkiye",
      "pricePerNight": 120,
      "availableRooms": 5,
      "imageUrl": "/images/hotel-blue.jpg",
      "rating": 4.5
    }
  ]
}
```

---

### 2.2 Properties Search

- **GET** `/api/properties/search`

**Query Parameters**

| Param        | Type     | Description                  |
| ------------ | -------- | ---------------------------- |
| location     | string   | City                         |
| checkIn      | date     | YYYY-MM-DD                   |
| checkOut     | date     | YYYY-MM-DD                   |
| adults       | number   | Number of adults             |
| children     | number   | Number of children           |
| priceMin     | number   | Minimum price                |
| priceMax     | number   | Maximum price                |
| bedrooms     | number   | Minimum bedrooms             |
| bathrooms    | number   | Minimum bathrooms            |
| propertyType | string[] | Hotel, Apartment, etc.       |
| amenities    | string[] | WiFi, Parking, etc.          |
| ratingMin    | number   | Minimum rating               |
| sortBy       | string   | price_asc, rating_desc, etc. |
| limit        | number   | Pagination limit             |
| offset       | number   | Pagination offset            |

**Response**

```json
{
  "meta": { "total": 123, "limit": 20, "offset": 0 },
  "data": [
    {
      "id": 1,
      "name": "Four Seasons by the Bay Beirut",
      "slug": "four-seasons-by-the-bay-beirut",
      "location": "Lebanon, Beirut, Downtown",
      "imageUrl": "/images/fourseasons-hotel.png",
      "rating": 4.53,
      "reviews": 487,
      "roomType": "Luxury King Room",
      "bedType": "1 Extra-large Double Bed",
      "perks": ["Free Cancellation", "No prepayment needed"],
      "lastChanceText": "Only 5 left at this price!",
      "lowestRate": 450,
      "taxes": 235,
      "recommended": true,
      "bedrooms": 1,
      "bathrooms": 1,
      "propertyType": "Hotel"
    }
  ]
}
```

---

## 3. RESERVATIONS API

### 3.1 List Reservations

- **GET** `/api/reservations`

**Query Parameters**

- `type` = all | hotels | flights
- `page`
- `limit`
- `userId` (required)

**Response**

```json
{
  "page": 1,
  "limit": 10,
  "total": 35,
  "items": [
    {
      "id": "res_123",
      "type": "hotel",
      "name": "Radisson Blu Plaza - Beirut",
      "description": "2 Bedroom Suite City View",
      "imageUrl": "https://...",
      "status": "active"
    }
  ]
}
```

---

### 3.2 Reservation Details

- **GET** `/api/reservations/{id}`

**Response**

```json
{
  "id": "res_123",
  "type": "hotel",
  "hotel": {
    "name": "Four Seasons Hotel - Beirut",
    "roomType": "2 Bedroom Suite Sea View",
    "checkIn": "2025-10-21",
    "checkOut": "2025-10-26",
    "nights": 5,
    "board": "Breakfast Included"
  },
  "guest": {
    "name": "Jamal Chatila",
    "phone": "+1 234 567 890",
    "email": "jshatila97@hotmail.com",
    "guests": "2 Adults and 2 Children"
  },
  "hotelInfo": {
    "address": "Crescent Rd, Palm Jumeirah, Dubai",
    "phone": "+971 4 426 0000",
    "checkInTime": "15:00",
    "checkOutTime": "12:00"
  },
  "pricing": {
    "total": 850,
    "currency": "USD",
    "status": "paid"
  },
  "status": "active"
}
```

---

### 3.3 Modify Reservation

- **PUT** `/api/reservations/{id}`

**Request**

```json
{
  "checkIn": "2026-01-12",
  "checkOut": "2026-01-15",
  "roomType": "Deluxe Ocean View",
  "guests": "2 Adults, 1 Child"
}
```

---

### 3.4 Contact Support

- **POST** `/api/support/contact`

**Request**

```json
{
  "userId": "abc123",
  "reservationId": "res_123",
  "message": "I need help with my reservation."
}
```

---

## 4. ACCOUNT API

### 4.1 Profile

- `GET /account/profile`
- `PUT /account/profile`
- `PUT /account/change-password`

### 4.2 Payment Methods

- `GET /account/payment-methods`
- `POST /account/payment-methods`
- `DELETE /account/payment-methods/{id}`
- `PUT /account/payment-methods/{id}/default`

### 4.3 Travelers & Family

- `GET /account/travelers`
- `POST /account/travelers`
- `PUT /account/travelers/{id}`
- `DELETE /account/travelers/{id}`

### 4.4 Can't Find Your Trip

- **POST** `/trips/request`

**Request**

```json
{
  "email": "user@example.com"
}
```

---

## 5. MESSAGES / SUPPORT API

- `GET /messages/conversations?type=hotels`
- `GET /messages/conversations/{id}`
- `POST /messages/conversations/{id}/messages`
- `POST /messages/conversations`
- WebSocket (optional): `/ws/messages`

---

## 6. FAVORITES API

- `GET /favorites?type=<all|hotels|flights>`
- `POST /favorites`

**Request**

```json
{
  "itemType": "hotel",
  "itemId": "hotel_882"
}
```

- `DELETE /favorites/{favoriteId}`
- `GET /favorites/check?itemType=hotel&itemId=hotel_882` (optional)

---

## 7. ROOM SELECTION API

### 7.1 Get Room Types

- **GET** `/api/hotels/{hotelId}/rooms`

**Optional Query Parameters**

| Param    | Type                | Description                      |
| -------- | ------------------- | -------------------------------- |
| checkIn  | string (YYYY-MM-DD) | Optional; filter by availability |
| checkOut | string (YYYY-MM-DD) | Optional; filter by availability |
| guests   | number              | Optional; filter by occupancy    |

**Response**

```json
{
  "hotelId": "four-seasons-by-the-bay-beirut",
  "rooms": [
    {
      "id": "room_101",
      "name": "Deluxe King Room",
      "description": "Spacious room with city view and king-size bed.",
      "pricePerNight": 220,
      "currency": "USD",
      "maxGuests": 3,
      "bedType": "1 King Bed",
      "size": "40m²",
      "refundable": true,
      "freeCancellationUntil": "2025-12-15",
      "amenities": [
        "Free WiFi",
        "Air Conditioning",
        "Soundproofing",
        "Private Bathroom"
      ],
      "images": ["/rooms/deluxe-king-1.jpg", "/rooms/deluxe-king-2.jpg"],
      "availability": true
    }
  ]
}
```

---

### 7.2 Get Room Pricing Quote

- **POST** `/api/hotels/{hotelId}/rooms/price`

**Request**

```json
{
  "roomId": "room_101",
  "checkIn": "2025-12-15",
  "checkOut": "2025-12-20",
  "quantity": 2
}
```

**Response**

```json
{
  "success": true,
  "roomId": "room_101",
  "quantity": 2,
  "pricePerNight": 220,
  "nights": 5,
  "totalPrice": 2200,
  "currency": "USD"
}
```

---

### 7.3 Validate Room Availability

- **POST** `/api/hotels/{hotelId}/rooms/check-availability`

**Request**

```json
{
  "roomId": "room_101",
  "checkIn": "2025-12-15",
  "checkOut": "2025-12-20",
  "quantity": 3
}
```

**Response (available)**

```json
{
  "roomId": "room_101",
  "isAvailable": true,
  "availableUnits": 5
}
```

**Response (unavailable)**

```json
{
  "roomId": "room_101",
  "isAvailable": false,
  "availableUnits": 1,
  "message": "Only 1 room left for these dates."
}
```

---

### 7.4 Create Temporary Room Selection

- **POST** `/api/hotels/{hotelId}/rooms/selection`

**Request**

```json
{
  "rooms": [
    { "roomId": "room_101", "quantity": 2 },
    { "roomId": "room_204", "quantity": 1 }
  ],
  "checkIn": "2025-12-15",
  "checkOut": "2025-12-20"
}
```

**Response**

```json
{
  "success": true,
  "selectionId": "sel_82hd73jss",
  "expiresIn": 900,
  "redirectTo": "/hotels/four-seasons-by-the-bay-beirut/booking"
}
```

---

### 7.5 Get Room Categories

- **GET** `/api/hotels/{hotelId}/room-categories`

**Response**

```json
{
  "categories": ["All", "Deluxe Room", "Superior Room", "Suite", "Family Room"]
}
```

---

### 7.6 Optional: Room Amenities

- **GET** `/api/hotels/amenities`

---

## 8. PAYMENT DETAILS API

### 8.1 Get User Payment Methods

- **GET** `/api/payment-methods`

**Response**

```json
{
  "paymentMethods": [
    {
      "id": "card_123",
      "cardHolder": "Jamal Chatila",
      "last4": "4581",
      "brand": "visa",
      "expiryMonth": 12,
      "expiryYear": 2025,
      "isDefault": true
    }
  ]
}
```

---

### 8.2 Add a New Card

- **POST** `/api/payment-methods`

**Request**

```json
{
  "cardHolder": "Jamal Chatila",
  "cardNumber": "4242424242424242",
  "expiryMonth": 12,
  "expiryYear": 2025,
  "cvv": "123",
  "saveCard": true
}
```

**Response**

```json
{
  "success": true,
  "paymentMethod": {
    "id": "card_456",
    "cardHolder": "Jamal Chatila",
    "last4": "4242",
    "brand": "visa",
    "expiryMonth": 12,
    "expiryYear": 2025,
    "isDefault": false
  }
}
```

---

### 8.3 Update Existing Card

- **PATCH** `/api/payment-methods/:id`

**Request**

```json
{
  "cardHolder": "Jamal Chatila",
  "isDefault": true
}
```

**Response**

```json
{
  "success": true,
  "paymentMethod": {
    "id": "card_123",
    "cardHolder": "Jamal Chatila",
    "last4": "4581",
    "brand": "visa",
    "expiryMonth": 12,
    "expiryYear": 2025,
    "isDefault": true
  }
}
```

---

### 8.4 Delete a Card

- **DELETE** `/api/payment-methods/:id`

**Response**

```json
{
  "success": true,
  "message": "Card deleted successfully."
}
```

---

### 8.5 Fetch Payment Availability for Property

- **GET** `/api/properties/:propertyId/payment-options`

**Response**

```json
{
  "cashAvailable": false,
  "cardAvailable": true
}
```

---

> **Note:** Payment method and payment API specs are still under discussion and subject to finalization.

---

# Rewards Carousel API Specification

### Endpoint

- `GET /api/rewards`

### Description

Fetches a list of reward cards (hotels) for the ongoing deals carousel.

---

### Request Query Parameters (optional)

| Parameter | Type   | Description                        |
| --------- | ------ | ---------------------------------- |
| limit     | number | Maximum number of rewards returned |
| offset    | number | Offset for pagination              |
| location  | string | Filter rewards by hotel location   |

---

### Response (200 OK)

```json
{
  "data": [
    {
      "id": "string",
      "name": "Marriott Bay",
      "description": "Promo name...",
      "location": "Beirut, Lebanon",
      "rating": 0,
      "discount": "30%",
      "bgCard": "#F9FCFB",
      "bgBadge": "#E9F4F0",
      "border": "#2A7157",
      "imageUrl": "https://..."
    }
  ],
  "meta": {
    "total": 50,
    "limit": 10,
    "offset": 0
  }
}
```

---

# Hotel Deals API Specification

### Endpoint

- `GET /api/hotels`

### Description

Fetches a list of hotels for various deal categories including:

- `offers`
- `weekend_deals`
- `top_rated`
- `promotions`

---

### Request Query Parameters

| Parameter | Type   | Description                                                 |
| --------- | ------ | ----------------------------------------------------------- |
| category  | string | One of `offers`, `weekend_deals`, `top_rated`, `promotions` |
| limit     | number | Max number of hotels to return (default: 10)                |
| offset    | number | Pagination offset (default: 0)                              |
| location  | string | Optional city or country filter                             |

---

### Response (200 OK)

```json
{
  "data": [
    {
      "id": "string",
      "name": "Hilton Downtown",
      "location": "Paris, France",
      "rating": 4.2,
      "reviews": "900",
      "price": 1200,
      "originalPrice": 1400,
      "imageUrl": "https://...",
      "category": "offers"
    }
  ],
  "meta": {
    "total": 50,
    "limit": 10,
    "offset": 0
  }
}
```

---

# Travel Destinations API Specification

### Endpoint

- `GET /api/travel-destinations`

### Description

Fetches popular travel destinations filtered by traveler's origin country.

---

### Request Query Parameters

| Parameter | Type   | Description                                           |
| --------- | ------ | ----------------------------------------------------- |
| country   | string | Filter by traveler's origin country (e.g., `Lebanon`) |
| limit     | number | Max destinations to return (default: 10)              |
| offset    | number | Pagination offset (default: 0)                        |

---

### Response (200 OK)

```json
{
  "data": [
    {
      "id": "string",
      "country": "Qatar",
      "city": "Lusail",
      "flagUrl": "https://flagcdn.com/w40/qa.png"
    }
  ],
  "meta": {
    "total": 50,
    "limit": 10,
    "offset": 0
  }
}
```

---

# Accommodations API Specification

### Endpoint

- `GET /api/accommodations`

### Description

Fetches accommodations of different types (Hotels, Apartments, Resorts, Villas).

---

### Request Query Parameters

| Parameter | Type   | Description                                                             |
| --------- | ------ | ----------------------------------------------------------------------- |
| type      | string | Accommodation type filter (`hotels`, `apartments`, `resorts`, `villas`) |
| limit     | number | Max items to return (default: 10)                                       |
| offset    | number | Pagination offset (default: 0)                                          |
| country   | string | Optional country filter                                                 |

| city | string | Optional city filter |

---

### Response (200 OK)

```json
{
  "data": [
    {
      "id": "string",
      "name": "Radisson Blu Airport",
      "type": "hotels",
      "rating": 4.5,
      "reviews": "1.2k",
      "location": "Spain, Madrid, Main St.",
      "price": 1360,
      "originalPrice": 1500,
      "imageUrl": "https://via.placeholder.com/400x300"
    }
  ],
  "meta": {
    "total": 50,
    "limit": 10,
    "offset": 0
  }
}
```

---

# BOOKINGS API

## **1. Create Booking (Start the Booking Process)**

**POST** `/api/bookings`

**Request**

```json
{
  "userId": "user_123",
  "hotelId": "hotel_101",
  "rooms": [{ "roomId": "room_101", "quantity": 2 }],
  "checkIn": "2025-12-15",
  "checkOut": "2025-12-20",
  "guests": {
    "adults": 2,
    "children": 2,
    "childrenAges": ["5", "8"]
  },
  "specialRequests": "High floor room if possible"
}
```

**Response**

```json
{
  "success": true,
  "bookingId": "booking_123",
  "status": "pending",
  "totalPrice": 2200,
  "currency": "USD",
  "redirectToPayment": "/bookings/booking_123/payment"
}
```

---

## **2. Get Booking Details**

**GET** `/api/bookings/{bookingId}`

**Response**

```json
{
  "bookingId": "booking_123",
  "status": "pending",
  "hotel": {
    "id": "hotel_101",
    "name": "Four Seasons Hotel",
    "location": "Beirut, Lebanon"
  },
  "rooms": [
    {
      "roomId": "room_101",
      "name": "Deluxe King Room",
      "quantity": 2,
      "pricePerNight": 220
    }
  ],
  "guests": {
    "adults": 2,
    "children": 2
  },
  "totalPrice": 2200,
  "currency": "USD",
  "paymentStatus": "unpaid"
}
```

---

## **3. Update Guest or Booking Info**

**PUT** `/api/bookings/{bookingId}`

**Request**

```json
{
  "guests": {
    "adults": 2,
    "children": 1
  },
  "specialRequests": "Late check-in requested"
}
```

**Response**

```json
{
  "success": true,
  "message": "Booking updated successfully"
}
```

---

## **4. Cancel Booking**

**DELETE** `/api/bookings/{bookingId}`

**Response**

```json
{
  "success": true,
  "message": "Booking canceled successfully"
}
```

---

## **5. Payment for Booking**

**POST** `/api/bookings/{bookingId}/payment`

**Request**

```json
{
  "paymentMethodId": "card_123",
  "amount": 2200,
  "currency": "USD"
}
```

**Response (Success)**

```json
{
  "success": true,
  "status": "confirmed",
  "confirmationNumber": "CONF123456"
}
```

**Response (Failure)**

```json
{
  "success": false,
  "status": "payment_failed",
  "message": "Payment was declined"
}
```

---

## **6. Optional: Booking History**

**GET** `/api/bookings?userId=user_123&status=all`

**Response**

```json
{
  "data": [
    {
      "bookingId": "booking_123",
      "hotelName": "Four Seasons Hotel",
      "checkIn": "2025-12-15",
      "checkOut": "2025-12-20",
      "status": "confirmed",
      "totalPrice": 2200,
      "currency": "USD"
    }
  ]
}
```

---

### **Flow Summary**

1. User selects rooms → **POST `/bookings`**
2. Review guest info → **GET `/bookings/{id}`**
3. Update guest info if needed → **PUT `/bookings/{id}`**
4. Proceed to payment → **POST `/bookings/{id}/payment`**
5. Confirm booking and generate **confirmation number**
6. Users can **view history** or **cancel booking**

---
