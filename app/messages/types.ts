type Sender = {
  id: string;
  avatar: string;
  name: string;
  email: string;
};

export interface Message {
  id: number;
  conversation_id: number;
  sender_id: string;
  body: string;
  created_at: string;
  is_admin?: boolean;
  updated_at?: string;
  sender?: Sender;
}

export interface Conversation {
  id: number;
  user_id: string;
  subject: string; // e.g., "Inquiry about Hotel ABC"
  messages: Message[];
  lastTimestamp: string;
  updated_at: string;
  last_message_at: string;
  created_at: string;
  type: "hotel" | "airline" | "support";
}

export interface NewMessagePayload {
  message: string;
}

export interface NewConversationPayload {
  type: "hotels" | "airlines" | "support";
  subject: string;
  message: string;
}

// features/support/types.ts

// Payload for POST /support/contact
export interface ContactSupportPayload {
  reservationId: string; // The specific booking ID the user needs help with
  message: string;
}

// Response after sending the request
export interface SupportTicketResponse {
  success: boolean;
  ticketId: string; // ID returned by the backend for reference
  // You might also get a status message or estimated response time
}
