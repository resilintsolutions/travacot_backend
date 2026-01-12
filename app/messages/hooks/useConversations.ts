import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { conversationApi } from "../api/conversationApi";
import { Message, NewMessagePayload } from "../types";
import { ContactSupportPayload, SupportTicketResponse } from "../types";

export const conversationKeys = {
  all: ["conversations"] as const,
  lists: () => [...conversationKeys.all, "list"] as const,
  messages: (conversationId: number) =>
    [...conversationKeys.all, "messages", conversationId] as const,
};

export const useConversationsList = () => {
  return useQuery({
    queryKey: conversationKeys.lists(),
    queryFn: conversationApi.listConversations,
    staleTime: 1000 * 60, // Stale time of 1 minute
    refetchInterval: 1000 * 30, // 🚨 Poll the list every 30 seconds for updates
  });
};

export const useMessages = (conversationId: number) => {
  return useQuery({
    queryKey: conversationKeys.messages(conversationId),
    queryFn: () => conversationApi.getMessages(conversationId),
    enabled: conversationId > 0, // Only fetch if conversationId is valid
    // Turn off polling to avoid repeated requests; we'll refetch on explicit invalidations
    refetchInterval: false,
    refetchOnWindowFocus: false,
    staleTime: 1000 * 5, // Cache briefly to smooth UI
  });
};

export const useSendMessage = (conversationId: number) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: NewMessagePayload) =>
      conversationApi.sendMessage({ conversationId, message: data.message }),

    // 1. Prepare for optimistic update
    onMutate: async (newMessagePayload) => {
      // Create a temporary ID and a temporary message object
      const tempId = Date.now();
      const tempMessage: Message = {
        id: tempId,
        conversation_id: conversationId,
        sender_id: "current_user_id", // You'd pull this from the session/profile hook
        body: newMessagePayload.message,
        created_at: new Date().toISOString(),
      };

      // Cancel any ongoing refetches to avoid overwriting the optimistic update
      await queryClient.cancelQueries({
        queryKey: conversationKeys.messages(conversationId),
      });

      // Get the existing messages data
      const previousMessages = queryClient.getQueryData(
        conversationKeys.messages(conversationId)
      );

      // Optimistically update the cache by adding the temporary message
      queryClient.setQueryData<Message[]>(
        conversationKeys.messages(conversationId),
        (old) => {
          return [...(old || []), tempMessage];
        }
      );

      // Return context with previous data for rollback
      return { previousMessages, tempMessage };
    },

    // 2. If the API call succeeds
    onSuccess: (confirmedMessage, variables, context) => {
      // Update the cache again, replacing the temporary message with the confirmed one
      queryClient.setQueryData<Message[]>(
        conversationKeys.messages(conversationId),
        (old) => {
          return (
            old?.map((msg) =>
              msg.id === context!.tempMessage.id ? confirmedMessage : msg
            ) || [confirmedMessage]
          );
        }
      );

      // Invalidate the conversations list to update the 'lastMessage' summary
      queryClient.invalidateQueries({ queryKey: conversationKeys.lists() });
      // Trigger a fresh fetch for the messages to replace optimistic data
      queryClient.invalidateQueries({
        queryKey: conversationKeys.messages(conversationId),
      });
    },

    // 3. If the API call fails, roll back the cache
    onError: (err, newMessagePayload, context) => {
      queryClient.setQueryData(
        conversationKeys.messages(conversationId),
        context?.previousMessages
      );
      // Optional: Show error toast to user
    },
  });
};

export const useContactSupport = () => {
  return useMutation<SupportTicketResponse, Error, ContactSupportPayload>({
    mutationFn: conversationApi.contactSupport,

    onSuccess: (data) => {
      // Logic for displaying a successful outcome to the user
      console.log(`Support request submitted for RES-ID ${data.ticketId}`);
    },
    onError: (error) => {
      // Logic for displaying a failure message
      console.error("Failed to submit support request:", error);
    },
  });
};
