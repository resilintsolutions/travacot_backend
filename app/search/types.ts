export interface SearchParams {
  destination: string; // e.g., PMI, a city code, or coordinate
  checkIn: string; // YYYY-MM-DD
  checkOut: string; // YYYY-MM-DD
  guests: {
    adults: number;
    children: number;
  };
}

// Simplified Hotel structure for Search Results
export interface SearchResultHotel {
  id: number;
  name: string;
  rating: number; // e.g., 4.5
  city: string;
  minPrice: number;
  currency: string;
}
export interface PropertySearchQuery {
  query: string; // Hotel name partial search
  country?: string; // e.g., PMI
  limit?: number;
}
export interface HotelDetails {
  hotel: HotelInfo;
  rooms: Room[];
}

export interface HotelInfo {
  id: number;
  name: string;
  categoryCode: string;
  categoryName: string;
  destinationCode: string;
  destinationName: string;
  zoneCode: number;
  zoneName: string;
  latitude: string;
  longitude: string;
  minRate: string;
  maxRate: string;
  currency: string;
  images: string[];
  houseRules: HotelPolicies;
  description: string;
  address: string;
}

export interface Room {
  description: string;
  facilities: string[];
  code: string;
  name: string;
  roomSizeSqm: number | null;
  bedType: string | null;
  remainingRooms: number;
  images: string[];
  lowestRate: number;
  highestRate: number;
  currency: string;
  rateKey: string;
  board: string;
  refundable: boolean;
  cancellation: CancellationRule[];
  hb_raw: HbRaw;
  roomFit: string;
  pricePerNight: number;
  totalPrice: number;
}

export interface CancellationRule {
  amount: string;
  from: string; // ISO date string
}

export type HotelPolicies = {
  checkIn: {
    from: string | null;
    to: string | null;
    description: string[];
  };

  checkOut: {
    from: string | null;
    to: string | null;
    description: string[];
  };

  cancellationPrepayment: string;

  childrenAndBeds: {
    summary: string;
    beds: string[];
    note: string;
  };

  ageRestrictions: string;

  pets: string;

  paymentMethods: Array<"AT_WEB" | string>;
};

export interface HotelListItem {
  code: number;
  name: string;
  countryName: string;
  cityName: string;
  countryCode: string;
  destinationCode: string;
  destinationName: string;
  description: string;
  longitude: string;
  latitude: string;
  city: string;
  destination: string;
  location: string;
  category: string;
  rating: number;
  lowestRate: string;
  highestRate: string;
  currency: string;
  board: string;
  isRefundable: boolean;
  images: string[];
  taxes: unknown[]; // Replace with a proper type if structure is known
  recommended: boolean;
  totalReviews: number;
  noPrepaymentNeeded: boolean;
  freeCancellation: boolean;
  roomsLeftLabel: string;
  nights: number;
  adults: number;
  children: number;
  houseRules: HotelPolicies;
}

export interface HbRaw {
  code: string;
  name: string;
  rates: HbRate[];
}

export interface CancellationPolicy {
  amount: string;
  from: string;
}

export interface HbRate {
  freeCancellation: boolean;
  freeCancellationUntil: string;
  rateKey: string;
  rateClass: string;
  rateType: string;
  net: string;
  allotment: number;
  rateCommentsId: string;
  paymentType: string;
  packaging: boolean;
  boardCode: string;
  boardName: string;
  cancellationPolicies: CancellationPolicy[];
  pricing: {
    effective_min: number;
    final_price: number;
    margin_percent: number;
    margin_source: string;
    msp_scope: string;
    scope_used: string;
    selling_price: number;
    vendor_net: number;
  };
  taxes: TaxesInfo;
  rooms: number;
  adults: number;
  children: number;
  offers: {
    amount: string;
    code: string;
    name: string;
  }[];
}

export interface TaxesInfo {
  taxes: TaxDetail[];
  allIncluded: boolean;
}

export interface TaxDetail {
  included: boolean;
  amount: string;
  currency: string;
  type: string;
  clientAmount: string;
  clientCurrency: string;
  subType: string;
}

// features/hotels/types.ts (Adding or refining types)

// --- Price Quote & Availability ---

// The critical identifier for a specific room/rate combination
export type RateKey = string;

// Payload for POST /rooms/price & POST /rooms/check-availability
export interface RateKeyPayload {
  rateKey: RateKey;
}

export interface PriceQuotePayload extends RateKeyPayload {
  quantity: number;
  net: number;
  currency: string;
}

export interface PriceQuoteResponse {
  rateKey: RateKey;
  totalPrice: number;
  currency: string;
  isAvailable: boolean;
  // Final price guarantee details
}

export interface RoomSelectionPayload {
  checkIn: string;
  checkOut: string;
  rooms: {
    rateKey: RateKey;
    quantity: number;
  }[];
}

export interface RoomSelectionResponse {
  bookingIntentId: string; // The ID to use for the final booking step
  totalPrice: number;
  // ... other confirmed details
}

export interface Pricing {
  currency: string;
  discounted_per_night: number;
  original_total: number;
  discounted_total: number;
  discount_percent: number;
  final_total: number;
  marked_per_night: number;
  marked_total: number;
  markup_percent: number;
  vendor_per_night: number;
  vendor_total: number;
  final_per_night: number;
}

export interface HotelOffer {
  hotel_code: number;
  name: string;
  city: string;
  country: string;
  image_url: string;
  rating: number;
  categoryCode: string;
  nights: number;
  pricing: Pricing;
  urgency_label: string | null;
  allotment: number;
  segments: string[]; // Assuming this is an array of strings; change to any[] if unsure
}

export interface HotelResponse {
  offers: HotelOffer[];
  promotions: HotelOffer[];
  top_rated: HotelOffer[];
  weekend_deals: HotelOffer[];
}

export interface TailoredHotelRecommendations {
  tabs: {
    offers: HotelOffer[];
    top_rated: HotelOffer[];
    weekend_deals: HotelOffer[];
    promotions: HotelOffer[];
  };
}
