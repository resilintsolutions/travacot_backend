"use client";

import { useState } from "react";
import Image from "next/image";
import logoDarkNew from "@/assets/images/travacot-logo-dark-new.svg";
import { cn } from "@/lib/utils";
import { IoIosArrowDown } from "react-icons/io";

const sections = [
  {
    title: "About",
    links: ["About Travacot", "How We Work", "Blog", "Careers"],
  },
  {
    title: "Partnership",
    links: [
      "Become a Partner",
      "Travacot Link",
      "Affiliate Program",
      "Affiliate Program",
    ],
  },
  {
    title: "Legal & Trust",
    links: [
      "Terms and Conditions",
      "Privacy Policy",
      "Cookie Policy",
      "Security and Compliance",
      "Trust & Safety",
    ],
  },
  {
    title: "Support",
    links: [
      "Help Center",
      "FAQs",
      "Manage Booking",
      "Payment Options",
      "Cancellation & Refunds",
      "Customer Service",
    ],
  },
];

export default function MainFooter({ className }: { className?: string }) {
  const [openIndex, setOpenIndex] = useState<number | null>(null);

  const toggle = (index: number) => {
    setOpenIndex(openIndex === index ? null : index);
  };

  return (
    <footer className={cn("bg-[#FAFAFC] text-core", className)}>
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 pt-6 md:pt-16 md:pb-10">
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 md:text-center md:gap-8 mb-5 md:mb-10">
          {sections.map((sec, index) => (
            <div key={index} className="text-xs">
              {/* Header button on mobile */}
              <button
                className="w-full md:cursor-default flex items-center justify-between md:block text-center md:text-left"
                onClick={() => toggle(index)}
              >
                <h3 className="font-bold mb-4 md:text-center">{sec.title}</h3>

                {/* arrow only on mobile */}
                <span
                  className={cn(
                    "md:hidden",
                    openIndex === index && "rotate-180"
                  )}
                >
                  <IoIosArrowDown className="size-4" />
                </span>
              </button>

              {/* Dropdown content (hidden on mobile unless active) */}
              <ul
                className={cn(
                  "space-y-6 overflow-hidden transition-all duration-300",
                  openIndex === index
                    ? "max-h-96 opacity-100"
                    : "max-h-0 opacity-0 md:max-h-none md:opacity-100"
                )}
              >
                {sec.links.map((link, i) => (
                  <li key={i}>{link}</li>
                ))}
              </ul>
            </div>
          ))}
        </div>

        <div className="hidden md:block border-t border-gray-300 pb-6 pt-6 md:pb-0 space-y-4 text-center text-[#808093]">
          <div className="flex flex-col gap-4 text-xs">
            <p className="tracking-tight">
              Savings percentage claims are based on Express Deal bookings
              compared to Travacot's lowest retail rate for the same itinerary.
            </p>
            <p className="tracking-tight">
              Package Savings based on itineraries booked as a package, compared
              to the price of the same itinerary booked separately.
            </p>
            <p className="tracking-tight">
              Strike-through price discounts compare discounted price to most
              recent actual retail price.
            </p>
            <p className="tracking-tight">
              © 2025 travacot.com LLC. All rights reserved.
            </p>
          </div>
        </div>
      </div>

      <div className="bg-[#F9F4FF] py-4 sm:py-8">
        <div className="flex items-center justify-center gap-3">
          <Image
            alt="travacot logo"
            src={logoDarkNew}
            className="w-7 md:w-9 h-auto"
          />
          <div className="flex flex-col items-start text-core">
            <span className="font-bold text-lg md:text-[25px] leading-tight">
              Travacot
            </span>
            <span className="text-xs leading-tight">
              The revenue engine to the hospitality industry
            </span>
          </div>
        </div>
      </div>
    </footer>
  );
}
