"use client";

import { useEffect, useState } from "react";
import Image from "next/image";
import { FaBed } from "react-icons/fa";

import piggyBankIcon from "@/assets/images/piggy-bank-icon.svg";
import forwardShortIcon from "@/assets/images/forward-short-icon.svg";
import { cn } from "@/lib/utils";

const ViewRecommendation = () => {
  const [isOpen, setIsOpen] = useState<boolean>(false);

  const onClose = () => setIsOpen(false);

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
      {/* Floating Button */}
      <div className="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-40">
        <button
          type="button"
          onClick={() => setIsOpen(true)}
          className="bg-[#9863E6] text-white font-medium rounded-full py-2.5 px-3 md:py-3 md:px-6 text-xs md:text-sm shadow-xl"
        >
          View our recommendation
        </button>
      </div>

      {/* Overlay */}
      <div
        className={cn(
          "fixed inset-0 bg-core/50 z-50 transition-opacity duration-300 ease-in-out",
          isOpen ? "opacity-100" : "opacity-0 pointer-events-none"
        )}
        onClick={onClose}
      />

      {/* Sliding Bottom Sheet */}
      <div
        className={cn(
          "fixed left-1/2 bottom-0 z-50 -translate-x-1/2 w-full md:max-w-xl transform transition-transform duration-500 ease-out",
          isOpen ? "translate-y-0" : "translate-y-full"
        )}
      >
        <div
          className={cn(
            "bg-core text-white rounded-t-[20px] md:h-[500px] p-4 md:p-6 border-t-2 border-dashed border-[#8C8CA0]",
            isOpen && "shadow-[0_0_20px_2px_#9863E6]"
          )}
        >
          <div className="flex gap-4 mb-8">
            <div className="size-10">
              <Image
                alt="Save up icon"
                src={piggyBankIcon}
                className="size-full"
              />
            </div>
            <div className="flex flex-col text-[#F3DFFC]">
              <h2 className="font-semibold">Save up!</h2>
              <p className="text-sm">We think you might like this one.</p>
            </div>
          </div>

          <div className="flex items-center gap-4 mb-8">
            <span className="bg-white text-core py-2 px-6 rounded-[20px] text-xs">
              x2
            </span>
            <p className="text-xs font-medium">
              Junior Corner Suite with Lounge Access, 2 King Beds
            </p>
          </div>

          <div className="border-2 border-dashed border-white mb-8" />

          <p className="text-xs md:text-sm mb-4">
            Based on our selection, you can:
          </p>
          <div className="flex text-white text-xs md:text-sm mb-6">
            <p className="flex-none w-[120px] font-medium">
              <span className="font-bold">Fit</span> 4 Adults
            </p>
            <p className="flex-none w-[150px] font-medium flex gap-2">
              <FaBed className="size-5" /> 2 King Beds
            </p>
          </div>

          <div className="w-fit bg-white rounded-lg text-core flex items-center gap-2.5 sm:gap-6 md:gap-10 py-4 px-3 md:px-5 mb-4">
            <div className="flex flex-col">
              <span className="text-[#C64A4A] text-xs">EU€ 645.00</span>
              <span className="text-[#C64A4A] text-sm font-bold">
                Your selection
              </span>
            </div>
            <div className="size-4 sm:size-8">
              <Image
                alt="forward short icon"
                src={forwardShortIcon}
                className="size-full"
              />
            </div>
            <div className="text-xs">
              <div className="flex items-center gap-2">
                <span className="w-fit text-core text-base font-semibold">
                  EU€ 615.00
                </span>
                <span className="w-fit text-[#065F46] border border-[#A5E4C4] bg-white py-1 px-1.5 font-semibold">
                  Save EU€ 30
                </span>
              </div>

              <p className="text-[#595870] text-[10px]">
                Excluding what is due at the property
              </p>
            </div>
          </div>

          <div className="flex items-center gap-4">
            <div className="rounded-[20px] bg-[#9863E6] py-2.5 px-6 text-xs">
              Use this offer -{" "}
              <span className="font-semibold">Save EU€ 30</span>
            </div>

            <button onClick={onClose} className="text-white underline text-xs">
              or <span className="underline">close offer</span>
            </button>
          </div>
          <p className="text-xs text-white px-6">You will not be charged yet</p>
        </div>
      </div>
    </>
  );
};

export default ViewRecommendation;
