"use client";
import React from "react";
import Link from "next/link";

type Props = {
  title?: string;
  message?: string;
  showRetry?: boolean;
  onRetry?: () => void;
  className?: string;
};

export default function HotelErrorPage({
  title = "We couldn't load hotels",
  message = "Something went wrong while fetching hotels. You can try again or return to search.",
  showRetry = true,
  onRetry,
  className = "",
}: Props) {
  return (
    <div className={`w-full flex items-center justify-center py-16 px-4 ${className}`}>
      <div className="max-w-3xl w-full bg-white rounded-2xl shadow-lg overflow-hidden">
        <div className="p-8 md:p-12 flex flex-col md:flex-row items-center gap-8">
          <div className="flex-none w-full md:w-1/2 flex items-center justify-center">
            <div className="w-56 h-56 bg-linear-to-br from-[#FEE2E2] to-[#FFF7ED] rounded-2xl flex items-center justify-center">
              <svg width="160" height="160" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
                <rect x="2" y="2" width="20" height="20" rx="6" fill="#FFEDD5" />
                <path d="M7 10c0-2.761 2.239-5 5-5s5 2.239 5 5-2.239 5-5 5c-.824 0-1.596-.204-2.28-.566" stroke="#FF6B6B" strokeWidth="1.2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M12 7v5" stroke="#9B2C2C" strokeWidth="1.4" strokeLinecap="round"/>
                <path d="M11 16l-3 3" stroke="#9B2C2C" strokeWidth="1.4" strokeLinecap="round"/>
                <path d="M13 16l3 3" stroke="#9B2C2C" strokeWidth="1.4" strokeLinecap="round"/>
              </svg>
            </div>
          </div>

          <div className="flex-1 text-center md:text-left">
            <h3 className="text-2xl md:text-3xl font-semibold text-[#1F2937] mb-2">{title}</h3>
            <p className="text-sm md:text-base text-[#6B7280] mb-6">{message}</p>

            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-start gap-3">
              {showRetry && (
                <button
                  onClick={() => onRetry && onRetry()}
                  className="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full bg-core text-white text-sm font-medium hover:brightness-95 transition"
                >
                  Retry
                </button>
              )}

              <Link
                href="/search"
                className="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full border border-[#E6E8F2] text-sm text-core bg-white hover:bg-[#F7F8FB] transition"
              >
                Back to search
              </Link>

              <a
                href="mailto:support@travacot.example"
                className="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full text-sm text-[#374151] hover:underline"
              >
                Contact support
              </a>
            </div>

            <div className="mt-6 text-xs text-[#9CA3AF]">
              <p>If the problem persists, try clearing your cache or check your network connection.</p>
            </div>
          </div>
        </div>
        <div className="w-full bg-[#F8FAFF] border-t border-[#F1F5F9] p-4 text-center text-xs text-[#64748B]">Error code: HOTEL_FETCH_FAILED</div>
      </div>
    </div>
  );
}
