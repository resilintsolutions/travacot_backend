import { apiClient } from "@/lib/apiClient";
import {
  Conversation,
  Message,
  NewConversationPayload,
  Conversation as ConversationSummary,
  ContactSupportPayload,
  SupportTicketResponse, // Renaming for clarity
} from "../types";

export const conversationApi = {
  listConversations: async (): Promise<ConversationSummary[]> => {
    const response = await apiClient.get("/messages/conversations");
    return response.data.data;
  },

  getMessages: async (conversationId: number): Promise<Message[]> => {
    const response = await apiClient.get(
      `/messages/conversations/${conversationId}`
    );
    // Ensure we always return an array, even if the response structure is different
    return response.data?.messages || response.data?.data || [];
  },

  sendMessage: async ({
    conversationId,
    message,
  }: {
    conversationId: number;
    message: string;
  }): Promise<Message> => {
    const response = await apiClient.post(
      `/messages/conversations/${conversationId}/messages`,
      { message }
    );
    return response.data;
  },

  startConversation: async (
    data: NewConversationPayload
  ): Promise<Conversation> => {
    const response = await apiClient.post("/messages/conversations", data);
    return response.data;
  },
  contactSupport: async (
    data: ContactSupportPayload
  ): Promise<SupportTicketResponse> => {
    const response = await apiClient.post("/support/contact", data);
    return response.data;
  },
};
