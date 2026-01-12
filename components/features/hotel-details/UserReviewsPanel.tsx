"use client";

import React, { useEffect, useState } from "react";
import Image from "next/image";
import { IoIosArrowDown } from "react-icons/io";
import { IoClose } from "react-icons/io5";

import { cn } from "@/lib/utils";
import personPlaceholder from "@/assets/images/person-placeholder.png";

interface UserReviewsPanelProps {
  isOpen: boolean;
  onClose: () => void;
  reviews: string[];
}

const exampleReviews = [
  {
    id: 1,
    userName: "Issam Tabbara",
    location: "Lebanon",
    date: "08/07/2022",
    rating: 4.5,
    avatarUrl: undefined, // or put image url here
    title: "Soundproof Room",
    text: "Had a great stay in Raddison Blu! My wife and I were busy the whole time in the room doing our magical intercourse with no disturbance thanks to the soundproof walls! You don’t get to find this option in properties very often nowadays...",
    suiteInfo: "2 Bedroom Suite",
  },
  {
    id: 2,
    userName: "Jamal Chatila",
    location: "Lebanon",
    date: "08/07/2022",
    rating: 3.5,
    avatarUrl: undefined,
    title: "Exceptional Staff",
    text: "Had a great stay in Raddison Blu! My wife and I were busy the whole time in the room doing our magical intercourse with no disturbance thanks to the soundproof walls! You don’t get to find this option in properties very often nowadays...",
    suiteInfo: "2 Bedroom Suite",
  },
  {
    id: 3,
    userName: "Ahmad Chatila",
    location: "Lebanon",
    date: "08/07/2022",
    rating: 4.5,
    avatarUrl: undefined,
    title: "Location",
    text: "Had a great stay in Raddison Blu! My wife and I were busy the whole time in the room doing our magical intercourse with no disturbance thanks to the soundproof walls! You don’t get to find this option in properties very often nowadays...",
    suiteInfo: "2 Bedroom Suite",
  },
  {
    id: 4,
    userName: "Tarek Harb",
    location: "Lebanon",
    date: "08/07/2022",
    rating: 4.5,
    avatarUrl: undefined,
    title: "Location",
    text: "Had a great stay in Raddison Blu! My wife and I were busy the whole time in the room doing our magical intercourse with no disturbance thanks to the soundproof walls! You don’t get to find this option in properties very often nowadays...",
    suiteInfo: "2 Bedroom Suite",
  },
];

const UserReviewsPanel: React.FC<UserReviewsPanelProps> = ({
  isOpen,
  onClose,
  reviews,
}) => {
  const [isMobile, setIsMobile] = useState<boolean | null>(null);

  useEffect(() => {
    const handleResize = () => {
      setIsMobile(window.innerWidth < 768); // Tailwind's `md` breakpoint
    };

    handleResize(); // run once on mount

    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  // ✨ Disable body scroll when open
  useEffect(() => {
    const scrollBarWidth =
      window.innerWidth - document.documentElement.clientWidth;

    if (isOpen) {
      document.body.classList.add("overflow-hidden");
      document.body.style.paddingRight = `${scrollBarWidth}px`;
    } else {
      document.body.classList.remove("overflow-hidden");
      document.body.style.paddingRight = "";
    }

    return () => {
      document.body.classList.remove("overflow-hidden");
      document.body.style.paddingRight = "";
    };
  }, [isOpen]);

  return (
    <>
      {/* Overlay */}
      <div
        className={cn(
          "fixed inset-0 bg-core/30 z-50 transition-opacity duration-300 ease-in-out",
          isOpen ? "opacity-100" : "opacity-0 pointer-events-none"
        )}
        onClick={onClose}
      ></div>

      {/* Sliding panel */}
      <div
        className={cn(
          "fixed top-2 sm:top-0 right-0 h-[calc(100vh-0.5rem)] sm:h-screen w-full sm:w-1/2 bg-white z-50 transform transition-all duration-500 ease-in-out rounded-t-2xl sm:rounded-none",
          isOpen ? "opacity-100" : "opacity-0",
          isOpen
            ? isMobile
              ? "translate-y-0" // mobile open (vertical)
              : "translate-x-0" // desktop open (horizontal)
            : isMobile
              ? "translate-y-full" // mobile closed (vertical)
              : "translate-x-full" // desktop closed (horizontal)
        )}
      >
        {/* header */}
        <div className="sticky top-0 flex items-center justify-between px-6 py-2 sm:py-4 border-b border-gray-200 rounded-t-2xl sm:rounded-none bg-white z-10">
          <div>
            <h2 className="text-base font-semibold text-core">Guest Reviews</h2>
            <p className="hidden sm:block text-xs">
              Our reviews are only shown when the guest books and completes
              their stay.
            </p>
          </div>

          <button
            onClick={onClose}
            className="p-2 rounded-full hover:bg-gray-100 transition-colors"
            aria-label="Close reviews panel"
          >
            <IoClose className="text-core size-8" />
          </button>
        </div>

        <div className="px-5 py-5 overflow-y-auto h-[calc(100vh-81px)]">
          <div className="flex flex-col text-core">
            <div className="mb-4 flex flex-col lg:flex-row items-start lg:items-center gap-2 lg:gap-4">
              <div className="w-fit py-2 px-3 flex gap-2.5 items-center border border-[#A48B05] bg-[#F6F2DA] rounded-full">
                <div className="size-5 border border-[#A48B05] bg-[#FFD700] rounded-full"></div>
                <span className="text-sm">
                  Overall Rating <span className="font-medium">4.5</span>
                </span>
              </div>

              <div className="flex flex-col">
                <p className="text-sm text-core">How much is that out of 10?</p>
                <span className="font-bold text-base text-[#065F46]">
                  9.0/10
                </span>
                <span className="text-xs text-[#065F46]">Exceptional</span>
              </div>
            </div>

            {/* filters */}
            <div>
              <h3 className="font-semibold text-xs mb-1.5">Filters</h3>
              <div className="flex gap-2 overflow-x-auto scrollbar-none scroll-smooth py-2.5 lg:flex-wrap lg:overflow-visible">
                {["Families", "Reviews", "Languages", "Time of the year"].map(
                  (label) => (
                    <div
                      key={label}
                      className="flex-none py-2.5 px-3 rounded-full bg-[#F8F8FF] flex items-center gap-2.5 text-xs whitespace-nowrap"
                    >
                      <span>{label}</span>
                      <IoIosArrowDown className="size-4 shrink-0" />
                    </div>
                  )
                )}
              </div>
            </div>

            <div className="mb-4">
              {exampleReviews.map((review, i) => (
                <div
                  key={review.id}
                  className="flex flex-col lg:flex-row items-start lg:items-center gap-4 py-4 border-b-3 border-[#E6E8F2]"
                >
                  <div className="flex lg:flex-col gap-4 items-center text-center lg:text-left">
                    <div className="flex items-start gap-4">
                      <div className="w-fit flex items-center justify-center">
                        <div className="size-10 rounded-full bg-gray-300 overflow-hidden flex items-center justify-center">
                          <Image
                            alt="person"
                            src={personPlaceholder}
                            className="size-5 object-contain"
                          />
                        </div>
                      </div>
                      <div className="flex flex-col items-start">
                        <h4 className="font-medium text-xs text-core">
                          {review.userName}
                        </h4>
                        <p className="text-xs text-core mb-2">{review.date}</p>

                        <div className="flex items-center gap-3">
                          <div className="w-fit flex gap-1 py-0.5 px-1.5 items-center border border-[#A48B05] bg-[#F6F2DA] rounded-full">
                            <div className="size-3 border border-[#A48B05] bg-[#FFD700] rounded-full"></div>
                            <span className="text-xs font-medium">4.53</span>
                          </div>

                          <p className="lg:hidden text-xs text-core">
                            Review from booking.com
                          </p>
                        </div>
                      </div>
                    </div>
                    <p className="hidden lg:block text-xs text-core">
                      Review from booking.com
                    </p>
                  </div>

                  <div className="flex-1 max-w-3xl text-xs text-core flex flex-col gap-4">
                    <h4 className="font-bold">{review.title}</h4>
                    <p className="line-clamp-4 lg:line-clamp-3">
                      {review.text}
                    </p>
                    <span className="w-fit py-2 px-4 rounded-full bg-[#F8F8FF]">
                      Four Seasons Beirut replied!
                    </span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default UserReviewsPanel;
