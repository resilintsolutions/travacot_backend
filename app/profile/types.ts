export interface UserProfile {
  id: string;
  name: string;
  email: string;
  gender?: string;
  phone_number?: string;
  avatar_url?: string;
}

export interface UpdateProfilePayload {
  name?: string;
  email?: string;
  phone_number?: string;
}

export interface ChangePasswordPayload {
  current_password: string;
  password: string;
  password_confirmation: string;
}

export interface Traveler {
  id: number;
  full_name: string;
  dob: string; // YYYY-MM-DD format
  passport_number: string;
  nationality: string;
  gender?: string;
}

export interface CreateTravelerPayload {
  full_name: string;
  dob: string;
  passport_number: string;
  nationality: string;
}

export interface UpdateTravelerPayload {
  full_name?: string;
  dob?: string;
  passport_number?: string;
  nationality?: string;
}

export interface SavedPaymentMethod {
  id: number;
  user_id?: number;
  brand: string; // e.g., "visa", "mastercard", "amex"
  last4: string;
  // Either a combined expiry or split month/year, depending on backend
  expiry?: string; // MM/YYYY
  expiry_month?: number;
  expiry_year?: number;
  holder_name: string;
  gateway?: string; // e.g., "manual", "stripe"
  gateway_reference?: string; // Stripe PaymentMethod id (pm_...)
  is_default: boolean;
  token_id?: string; // Legacy or alternate processor token
  created_at?: string;
  updated_at?: string;
}

export interface AddPaymentMethodPayload {
  holder_name: string;
  card_number: string;
  expiry_month: number;
  expiry_year: number;
  cvv: string;
  saveCard: boolean; // Flag to indicate if the user wants to save it
}

export interface UpdatePaymentMethodPayload {
  is_default: boolean; // Assuming this is the main update action
}

export interface PropertyPaymentOption {
  method_id: string;
  name: string; // e.g., "Pay in Full," "Pay Later," "Credit Card"
  icon_url?: string;
}
