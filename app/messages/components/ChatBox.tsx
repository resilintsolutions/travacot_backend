"use client";

import Image from "next/image";
import React from "react";
import contactSupportLogo from "@/assets/images/contact-support-logo.png";
import sendMailLogo from "@/assets/images/send-mail-logo.png";
import { Message } from "../types";

type Props = {
  messages: Message[];
  sendMessage: () => void;
  input: string;
  setInput: React.Dispatch<React.SetStateAction<string>>;
  isLoading: boolean;
  onBack?: () => void;
  conversationSubject?: string;
};

export const ChatBox = ({
  messages,
  sendMessage,
  input,
  setInput,
  isLoading,
  onBack,
  conversationSubject,
}: Props) => {
  return (
    <div className="flex-1 flex flex-col gap-4 bg-[#FAFAFC] rounded-[20px] overflow-hidden sm:mt-14 lg:mt-0">
      <div className="bg-spot text-core py-2.5 px-3 flex items-center gap-3 shadow-md mb-10">
        {onBack && (
          <button
            onClick={onBack}
            className="lg:hidden flex items-center justify-center w-8 h-8 rounded-full hover:bg-white/20 transition-colors"
            aria-label="Back to conversations"
          >
            <svg
              className="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M15 19l-7-7 7-7"
              />
            </svg>
          </button>
        )}
        <div className="flex-1">
          <h3 className="font-bold text-sm lg:text-base">Travacot</h3>
          <h4 className="text-xs lg:text-sm truncate">
            {conversationSubject || "Customer Support"}
          </h4>
        </div>
      </div>

      <div className="flex-1 flex flex-col gap-4 lg:gap-2 overflow-y-auto pr-4 px-4">
        {isLoading ? (
          // Loading skeleton state for messages
          <div className="space-y-4">
            {/* Support message skeleton */}
            <div className="max-w-3/4 lg:max-w-md flex gap-4">
              <div className="shrink-0 flex items-end">
                <div className="w-8 h-8 lg:w-10 lg:h-10 bg-gray-200 rounded-full animate-pulse"></div>
              </div>
              <div className="p-4 bg-gray-200 rounded-tl-xl rounded-tr-xl rounded-br-xl animate-pulse w-full">
                <div className="h-3 bg-gray-300 rounded w-3/4 mb-2"></div>
                <div className="h-3 bg-gray-300 rounded w-1/2"></div>
              </div>
            </div>

            {/* User message skeleton */}
            <div className="max-w-3/4 lg:max-w-md py-4 px-8 bg-gray-200 self-end rounded-bl-xl rounded-tl-xl rounded-tr-xl animate-pulse">
              <div className="h-3 bg-gray-300 rounded w-32"></div>
            </div>

            {/* Support message skeleton */}
            <div className="max-w-3/4 lg:max-w-md flex gap-4">
              <div className="shrink-0 flex items-end">
                <div className="w-10 h-10 bg-gray-200 rounded-full animate-pulse"></div>
              </div>
              <div className="p-4 bg-gray-200 rounded-tl-xl rounded-tr-xl rounded-br-xl animate-pulse w-full">
                <div className="h-3 bg-gray-300 rounded w-full mb-2"></div>
                <div className="h-3 bg-gray-300 rounded w-4/5 mb-2"></div>
                <div className="h-3 bg-gray-300 rounded w-2/3"></div>
              </div>
            </div>
          </div>
        ) : (
          <>
            <div className="max-w-3/4 lg:max-w-md flex gap-4">
              <div className="shrink-0 flex items-end">
                <Image
                  alt="Travacot contact support"
                  src={contactSupportLogo}
                  className="w-8 h-8 lg:w-10 lg:h-10 object-contain"
                />
              </div>
              <div className="p-4 text-xs text-core bg-[#F7F7FF] self-start rounded-tl-xl rounded-tr-xl rounded-br-xl">
                Hello! How can I help?
              </div>
            </div>

            {messages.map((msg, index) =>
              !msg.is_admin ? (
                <div
                  key={index}
                  className="max-w-3/4 lg:w-sm py-4 px-8 text-xs text-white bg-core self-end rounded-bl-xl rounded-tl-xl rounded-tr-xl"
                >
                  {msg.body}
                </div>
              ) : (
                <div
                  key={index}
                  className="max-w-3/4 lg:w-sm flex gap-4 self-start"
                >
                  <div className="shrink-0 flex items-end">
                    <Image
                      alt="Travacot contact support"
                      src={contactSupportLogo}
                      className="w-10 h-10 object-contain"
                    />
                  </div>
                  <div className="w-sm p-4 text-xs text-core bg-[#F7F7FF] rounded-tl-xl rounded-tr-xl rounded-br-xl">
                    {msg.body}
                  </div>
                </div>
              )
            )}
          </>
        )}
      </div>

      <div className="flex gap-4 px-4 pb-4">
        <input
          type="text"
          value={input}
          onChange={(e) => setInput(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === "Enter" && !e.shiftKey) {
              e.preventDefault(); // prevent form submit or newline
              sendMessage(); // call your sendMessage function
            }
          }}
          placeholder="Write your message here ..."
          className="flex-1 text-xs h-10 md:h-[60px] border border-[#C9D0E7] rounded-tl-[30px] rounded-bl-[30px] rounded-tr-2xl px-4 py-2 focus:outline-none focus:ring-0 placeholder:text-[#9090A0]"
        />
        <button
          onClick={sendMessage}
          className="h-10 md:h-[60px] w-10 md:w-[60px] flex items-center justify-center bg-[#ECECF0] rounded-[20px]"
        >
          <Image
            alt="Send mail"
            src={sendMailLogo}
            className="h-4 w-4 md:w-6 md:h-6 object-contain"
          />
        </button>
      </div>
    </div>
  );
};
