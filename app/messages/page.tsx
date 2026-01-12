"use client";

import { useState } from "react";
import { useQueryClient } from "@tanstack/react-query";
import emptyBox from "@/assets/images/empty-box.png";
import { cn } from "@/lib/utils";
import Image from "next/image";
import {
  useConversationsList,
  useMessages,
  useSendMessage,
  conversationKeys,
} from "./hooks/useConversations";
import { formatDate } from "date-fns";
import { ChatBox } from "./components/ChatBox";
import { NewConversationModal } from "./components/NewConversationModal";
import React from "react";

export type ChatMessage = {
  sender: "user" | "support";
  text: string;
};

export default function Page() {
  const [activeTab, setActiveTab] = useState<"all" | "hotels" | "flights">(
    "all"
  );
  const [isNewConversationOpen, setIsNewConversationOpen] = useState(false);
  const queryClient = useQueryClient();
  // const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [input, setInput] = useState("");
  const [conversationId, setConversationId] = useState<number>(0);
  const { data: conversations, isLoading, isError } = useConversationsList();
  const { data: messages, isLoading: messagesLoading } =
    useMessages(conversationId);
  console.log("Selected conversation ID: out", conversationId);
  const sendMessageMutation = useSendMessage(conversationId);
  const handleShowMessages = (newConversationId: number) => {
    console.log("Selected conversation ID:", newConversationId);
    setConversationId(newConversationId);
    // Force a fetch for this conversation's messages (even if cached) when selected
    queryClient.invalidateQueries({
      queryKey: conversationKeys.messages(newConversationId),
    });
  };

  const handleNewConversationSuccess = () => {
    queryClient.invalidateQueries({
      queryKey: conversationKeys.lists(),
    });
  };

  const sendMessage = async () => {
    if (!input) return;
    sendMessageMutation.mutate({ message: input });
    setInput("");
  };

  return (
    <>
      <section className="pt-10">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <div className="flex flex-col lg:flex-row h-auto lg:h-[calc(100vh-190px)] md:gap-6 mb-6 lg:mb-0">
            <div
              className={cn(
                "w-full lg:w-1/3 xl:w-1/4 flex flex-col mb-6 lg:mb-0",
                conversationId > 0 && "hidden lg:flex"
              )}
            >
              <div className="flex items-center justify-between mb-5">
                <h2 className="text-xl font-bold">Messages</h2>
                <button
                  onClick={() => setIsNewConversationOpen(true)}
                  className="px-3 py-1.5 bg-core text-white text-xs font-semibold rounded-lg hover:bg-core/90 transition-colors"
                  title="Start a new conversation"
                >
                  + New
                </button>
              </div>
              <div className="bg-[#F5F6FA] rounded-[20px] flex items-center justify-between gap-2.5 max-w-sm mb-4 p-1 overflow-x-auto lg:overflow-visible">
                <button
                  onClick={() => setActiveTab("all")}
                  className={cn(
                    "border border-transparent rounded-[30px] py-2 px-5 text-core w-24 flex items-center justify-center",
                    activeTab === "all" && "bg-core text-white"
                  )}
                >
                  <span className="font-bold text-xs ">All</span>
                </button>
                <button
                  onClick={() => setActiveTab("hotels")}
                  className={cn(
                    "border border-transparent rounded-[30px] py-2 px-5 text-core w-24 flex items-center justify-center",
                    activeTab === "hotels" && "bg-core text-white"
                  )}
                >
                  <span className="font-bold text-xs">Hotels</span>
                </button>
                <button
                  onClick={() => setActiveTab("flights")}
                  className={cn(
                    "border border-transparent rounded-[30px] py-2 px-5 text-core w-24 flex items-center justify-center",
                    activeTab === "flights" && "bg-core text-white"
                  )}
                >
                  <span className="font-bold text-xs">Flights</span>
                </button>
              </div>
              <div className="flex-1 overflow-y-auto pb-4 lg:pb-0 lg:pr-2">
                {isLoading ? (
                  <div
                    className="flex flex-col lg:bg-[#F5F6FA] rounded-[20px] xl:pt-4 gap-3 lg:gap-0"
                    key={"load"}
                  >
                    {[1, 2, 3].map((item) => (
                      <div
                        key={item}
                        className="w-full flex gap-4 p-4 bg-white lg:bg-transparent border lg:border-none rounded-xl lg:rounded-none shadow-sm lg:shadow-none animate-pulse"
                      >
                        <div className="text-core text-xs w-full space-y-2">
                          <div className="flex justify-between mb-2">
                            <div className="h-4 bg-gray-300 rounded-full w-16"></div>
                          </div>
                          <div className="h-4 bg-gray-300 rounded w-32"></div>
                          <div className="h-3 bg-gray-200 rounded w-full"></div>
                          <div className="h-3 bg-gray-200 rounded w-24"></div>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : isError ? (
                  <div className="flex items-center justify-center h-64 flex-col gap-4">
                    <div className="text-4xl">⚠️</div>
                    <div className="text-center">
                      <p className="font-semibold text-core mb-1">
                        Failed to load conversations
                      </p>
                      <p className="text-sm text-core/70">
                        Please check your connection and try again
                      </p>
                    </div>
                  </div>
                ) : conversations?.length === 0 ? (
                  <div key={"test"} className="flex items-center justify-center h-64 flex-col gap-4">
                    <div className="text-5xl">💬</div>
                    <div className="text-center">
                      <p className="font-semibold text-core mb-1">
                        No conversations yet
                      </p>
                      <p className="text-sm text-core/70">
                        Start a new conversation to get started
                      </p>
                    </div>
                  </div>
                ) : (
                  <div className="flex flex-col lg:bg-[#F5F6FA] rounded-[20px] xl:pt-4 gap-3 lg:gap-0">
                    {conversations?.map((conversation, index) => (
                      <React.Fragment key={conversation.id}>
                        <div
                          onClick={() => handleShowMessages(conversation.id)}
                          className={cn(
                            "w-full flex gap-4 p-4 cursor-pointer transition-colors duration-200 rounded-xl lg:rounded-none",
                            "border lg:border-none shadow-sm lg:shadow-none",
                            conversationId === conversation.id
                              ? "bg-core/10 border-core lg:border-l-4 lg:border-l-core"
                              : "bg-white lg:bg-transparent hover:bg-gray-50 border-gray-200"
                          )}
                        >
                          <div className="text-core text-xs">
                            <div className="flex justify-between mb-2">
                              {/* <span
                            className={cn(
                              "px-1 py-0.5 bg-white rounded-[20px] border border-[#9EE6C1] text-[#065F46]",
                              contact.status === "Case Closed" &&
                                "border-[#E69E9E] text-[#811818]"
                            )}
                          >
                            {contact.status}
                          </span> */}
                              <span>
                                {conversation.updated_at &&
                                  formatDate(
                                    new Date(conversation.updated_at),
                                    "hh:mm"
                                  )}
                              </span>
                            </div>
                            {/* <h3 className="font-bold mb-2">
                          {contact.hotelOrFlightName}, {contact.country}
                        </h3> */}
                            <h3 className="font-bold mb-2">
                              {conversation.subject}
                            </h3>
                            <p>
                              {
                                (conversation?.messages || [])[
                                  (conversation.messages || []).length - 1
                                ]?.body
                              }
                            </p>
                            <p className="opacity-50">
                              {conversation.last_message_at &&
                                formatDate(
                                  new Date(conversation.last_message_at),
                                  "dd MMM - dd MMM yyyy"
                                )}
                            </p>
                          </div>
                        </div>
                        {/* Separator line between conversations - only on desktop */}
                        {index < (conversations?.length || 0) - 1 && (
                          <div className="hidden lg:block border-b border-gray-200" />
                        )}
                      </React.Fragment>
                    ))}
                  </div>
                )}
              </div>
            </div>
            <div
              className={cn(
                "w-full lg:w-2/3 xl:w-3/4 flex",
                conversationId > 0 && "lg:w-2/3 xl:w-3/4"
              )}
            >
              {activeTab === "flights" ? (
                <div className="w-full flex flex-col items-center justify-center text-center px-4 min-h-[350px] sm:min-h-[400px] md:min-h-[500px]">
                  <div className="w-40 sm:w-52 md:w-64 mb-4">
                    <Image
                      alt="Empty box"
                      src={emptyBox}
                      className="w-full h-auto object-contain"
                      priority
                    />
                  </div>

                  <p className="font-semibold text-sm sm:text-xl text-core">
                    Hmm... It seems empty here
                  </p>

                  <p className="text-sm sm:text-base text-core/80">
                    You can start filling up your favorites by liking our
                    offerings!
                  </p>
                </div>
              ) : conversationId > 0 ? (
                <ChatBox
                  messages={messages || []}
                  sendMessage={sendMessage}
                  input={input}
                  setInput={setInput}
                  isLoading={messagesLoading}
                  onBack={() => setConversationId(0)}
                  conversationSubject={
                    conversations?.find((c) => c.id === conversationId)?.subject
                  }
                />
              ) : (
                <div className="flex-1 flex flex-col items-center justify-center text-center px-4">
                  <div className="w-40 sm:w-52 md:w-64 mb-4">
                    <Image
                      alt="Empty box"
                      src={emptyBox}
                      className="w-full h-auto object-contain"
                      priority
                    />
                  </div>

                  <p className="font-semibold text-sm sm:text-xl text-core">
                    Select a Conversation
                  </p>
                  <p className="text-sm sm:text-base text-core/80">
                    Choose a contact from the list to view the messages.
                  </p>
                </div>
              )}
            </div>
          </div>
        </div>
      </section>
      <NewConversationModal
        isOpen={isNewConversationOpen}
        onClose={() => setIsNewConversationOpen(false)}
        onSuccess={handleNewConversationSuccess}
      />
    </>
  );
}
