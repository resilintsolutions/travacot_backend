import { useMutation, useQueryClient } from "@tanstack/react-query";
import { conversationApi } from "../api/conversationApi";
import { NewConversationPayload, Conversation } from "../types";
import { conversationKeys } from "./useConversations";
import { toast } from "sonner";

export const useStartConversation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: NewConversationPayload) =>
      conversationApi.startConversation(data),

    // 1. Optimistic update - add new conversation to list immediately
    onMutate: async (newConversationData) => {
      // Cancel any ongoing refetches for the conversations list
      await queryClient.cancelQueries({
        queryKey: conversationKeys.lists(),
      });

      // Get the existing conversations list
      const previousConversations = queryClient.getQueryData(
        conversationKeys.lists()
      );

      // Create a temporary conversation object with placeholder data
      const tempConversation: Conversation = {
        id: -1, // Temporary ID
        user_id: "current_user",
        subject: newConversationData.subject,
        messages: [
          {
            id: -1,
            conversation_id: -1,
            sender_id: "current_user",
            body: newConversationData.message,
            created_at: new Date().toISOString(),
            is_admin: false,
          },
        ],
        lastTimestamp: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        last_message_at: new Date().toISOString(),
        created_at: new Date().toISOString(),
        type: newConversationData.type as "hotel" | "airline" | "support",
      };

      // Optimistically add to the conversations list
      queryClient.setQueryData<Conversation[]>(
        conversationKeys.lists(),
        (old) => {
          return [tempConversation, ...(old || [])];
        }
      );

      return { previousConversations, tempConversation };
    },

    // 2. If the API call succeeds
    onSuccess: (confirmedConversation, variables, context) => {
      // Update the cache with the real conversation from the server
      queryClient.setQueryData<Conversation[]>(
        conversationKeys.lists(),
        (old) => {
          return (
            old?.map((conv) =>
              conv.id === context!.tempConversation.id
                ? confirmedConversation
                : conv
            ) || [confirmedConversation]
          );
        }
      );

      // Show success toast
      toast.success("Conversation started!");
    },

    // 3. If the API call fails, roll back
    onError: (err, newConversationData, context) => {
      queryClient.setQueryData(
        conversationKeys.lists(),
        context?.previousConversations
      );
      toast.error("Failed to start conversation. Please try again.");
    },
  });
};
