"use client";

import { useState } from "react";
import { cn } from "@/lib/utils";
import { useStartConversation } from "../hooks/useStartConversation";
import { NewConversationPayload } from "../types";
import { toast } from "sonner";

interface NewConversationModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess?: () => void;
}

export const NewConversationModal = ({
  isOpen,
  onClose,
  onSuccess,
}: NewConversationModalProps) => {
  const [formData, setFormData] = useState({
    type: "hotels",
    subject: "",
    message: "",
  });

  const startConversationMutation = useStartConversation();
  const isLoading = startConversationMutation.isPending;

  const handleInputChange = (
    e: React.ChangeEvent<
      HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement
    >
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!formData.subject.trim()) {
      toast.error("Please enter a subject.", {
        position: "top-center",
      });
      return;
    }

    if (!formData.message.trim()) {
      toast.error("Please enter a message.", {
        position: "top-center",
      });
      return;
    }
    startConversationMutation.mutate(
      {
        type: formData.type,
        subject: formData.subject,
        message: formData.message,
      } as NewConversationPayload,
      {
        onSuccess: () => {
          setFormData({
            type: "support",
            subject: "",
            message: "",
          });
          onClose();
          if (onSuccess) {
            onSuccess();
          }
        },
      }
    );
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 flex items-center justify-center z-50 p-4 bg-black/50">
      <div className="bg-white rounded-[20px] shadow-lg max-w-md w-full p-6">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-xl font-bold text-core">
            Start New Conversation
          </h2>
          <button
            onClick={onClose}
            disabled={isLoading}
            className="text-gray-400 hover:text-gray-600 text-2xl leading-none"
          >
            ×
          </button>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-semibold text-core mb-2">
              Subject
            </label>
            <input
              type="text"
              name="subject"
              value={formData.subject}
              onChange={handleInputChange}
              disabled={isLoading}
              placeholder="Enter subject..."
              className="w-full px-4 py-2 border border-[#C9D0E7] rounded-lg focus:outline-none focus:ring-2 focus:ring-core text-sm placeholder:text-[#9090A0]"
            />
          </div>

          <div>
            <label className="block text-sm font-semibold text-core mb-2">
              Message
            </label>
            <textarea
              name="message"
              value={formData.message}
              onChange={handleInputChange}
              disabled={isLoading}
              placeholder="Write your initial message..."
              rows={4}
              className="w-full px-4 py-2 border border-[#C9D0E7] rounded-lg focus:outline-none focus:ring-2 focus:ring-core text-sm placeholder:text-[#9090A0] resize-none"
            />
          </div>

          <div className="flex gap-3 pt-4">
            <button
              type="button"
              onClick={onClose}
              disabled={isLoading}
              className="flex-1 px-4 py-2 border border-[#C9D0E7] text-core rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed font-semibold text-sm"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={isLoading}
              className={cn(
                "flex-1 px-4 py-2 bg-core text-white rounded-lg font-semibold text-sm",
                isLoading ? "opacity-50 cursor-not-allowed" : "hover:bg-core/90"
              )}
            >
              {isLoading ? "Creating..." : "Start Conversation"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
