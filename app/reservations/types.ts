export type ReservationType = "all" | "hotels" | "flights";

export interface Phone {
  phoneNumber: string;
  phoneType: string; // e.g., "mobile", "landline"
}

export interface ReservationSummary {
  id: string;
  guest: {
    email: string;
    name: string;
    guests: number;
  };
  hotel_information: {
    address: string;
    phone: Phone[];
    check_in_time: string; // e.g., "14:00"
    check_out_time: string; // e.g., "12:00"
  };
  reservation: {
    id: string;
    board: string;
    confirmation_number: string;
    created_at: string; // ISO date string
    room_type: string;
    total_price: {
      amount: number;
      currency: string;
      status:
        | "paid"
        | "pending"
        | "refunded"
        | "confirmed"
        | "completed"
        | "cancelled"
        | "pending_payment"
        | "active";
    };
  };
  hotel: {
    name: string;
    check_in: string; // YYYY-MM-DD
    check_out: string; // YYYY-MM-DD
    nights: number;
    status: string;
    image: string;
  };
}

export interface ListReservationsParams {
  type: ReservationType;
  page: number;
  limit: number;
  userId: string; // Required by your API structure
}

export interface PaginatedReservationsResponse {
  page: number;
  limit: number;
  total: number;
  data: ReservationSummary[];
}

export interface ReservationDetails {
  id: string;
  guest: {
    email: string;
    name: string;
    guests: number;
  };
  hotel_information: {
    address: string;
    phone: Phone[];
    check_in_time: string;
    check_out_time: string;
  };
  reservation: {
    id: string;
    board: string;
    confirmation_number: string;
    created_at: string; // ISO date string
    room_type: string;
    total_price: {
      amount: number;
      currency: string;
      status:
        | "paid"
        | "pending"
        | "refunded"
        | "confirmed"
        | "completed"
        | "cancelled"
        | "pending_payment"
        | "active";
    };
  };
  hotel: {
    name: string;
    check_in: string; // YYYY-MM-DD
    check_out: string; // YYYY-MM-DD
    nights: number;
    status: string;
    image: string;
  };
}

export interface ModifyReservationPayload {
  checkIn: string;
  checkOut: string;
  roomType: string;
  guests: string;
}

export type PaxType = "AD" | "CH"; // Adult or Child
export type RateFlowType = "RECHECK" | "BOOKABLE";

export interface Pax {
  type: PaxType;
  age?: number;
}

export interface CheckoutRoomPayload {
  rate_key: string;
  rate_type: RateFlowType;
  paxes: Pax[];
  net?: number;
}

export interface HolderDetails {
  name: string;
  surname: string;
}

export interface CheckoutPayload {
  hotel_id: number;
  rooms: CheckoutRoomPayload[];

  holder: HolderDetails;

  currency: string;
  countryCode: string;
  cityCode: string;

  customer_email?: string;
  check_in?: Date; // Required for RECHECK flow
  check_out?: Date; // Required for RECHECK flow
  client_reference?: string;
  remark?: string;
  channel?: string;
  net?: number; // Total net amount for all rooms
  reservationId?: string; // For modifying existing reservation
}

export interface CheckoutResponse {
  success: boolean;
  reservation_id: number;
  amount: number; // Final selling price
  currency: string;
  client_secret: string; // CRITICAL for Stripe.js
}
