"use client";

import { useEffect, useRef, useState } from "react";
import { IoAdd, IoRemove } from "react-icons/io5";
import { cn } from "@/lib/utils";
import { SearchParams } from "../../../app/search/types";
import { Button } from "@/components/ui/button";
import searchIcon from "@/assets/images/search.svg";
import guestIcon from "@/assets/images/guest-icon.svg";
import Image from "next/image";

interface Props {
  className?: string;
  buttonText?: string;
  searchData: SearchParams;
  onSearchDataChange: (data: SearchParams) => void;
  handleHotelSearch: () => void;
  isOtherPageSearch?: boolean;
}

export const SelectNumberOfGuests = ({
  className,
  buttonText,
  searchData,
  onSearchDataChange,
  handleHotelSearch,
  isOtherPageSearch = false,
}: Props) => {
  const [isOpen, setIsOpen] = useState<boolean>(false);
  const [numberOfAdults, setNumberOfAdults] = useState<number>(
    searchData.guests?.adults || 2
  );
  const [numberOfChildren, setNumberOfChildren] = useState<number>(
    searchData.guests?.children || 0
  );
  // const [childrenAges, setChildrenAges] = useState<string[]>([]);

  const wrapperRef = useRef<HTMLDivElement>(null);

  const guest = `${numberOfAdults} Adults, ${numberOfChildren} Children`;

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        wrapperRef.current &&
        !wrapperRef.current.contains(event.target as Node)
      ) {
        setIsOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  // useEffect(() => {
  //   setChildrenAges((prev) => {
  //     const newAges = [...prev];
  //     if (numberOfChildren > prev.length) {
  //       for (let i = prev.length; i < numberOfChildren; i++) {
  //         newAges.push("");
  //       }
  //     } else if (numberOfChildren < prev.length) {
  //       newAges.length = numberOfChildren;
  //     }
  //     return newAges;
  //   });
  // }, [numberOfChildren]);

  // const handleAgeChange = (index: number, value: string) => {
  //   setChildrenAges((prev) => {
  //     const updated = [...prev];
  //     updated[index] = value;
  //     return updated;
  //   });
  // };

  const handleConfirm = () => {
    // if (childrenAges.some((age) => age === "")) {
    //   alert("Please select an age for all children");
    //   return;
    // }
    setIsOpen(false);
  };

  return (
    <div
      ref={wrapperRef}
      onClick={() => setIsOpen((prev) => !prev)}
      className={cn(
        "w-full md:w-[280px] flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 md:gap-0 px-0.5 py-4 md:py-0 bg-white text-core rounded-[20px] md:rounded-[0px_45px_45px_0px] relative",
        className,
        isOtherPageSearch &&
          "md:rounded-[0px_0px_0px_0px] w-[260px] p-2.5 opacity-100"
      )}
    >
      {isOtherPageSearch ? (
        <div className="cursor-pointer">
          <p className="font-bold text-[13px]">Guests</p>
          <p className="text-[13px] text-[#595870] whitespace-nowrap truncate">
            {numberOfAdults + numberOfChildren > 0 ? guest : "Add your guests"}
          </p>
        </div>
      ) : (
        <div className="cursor-pointer ml-3 flex items-center gap-2.5">
          <Image src={guestIcon} width={26} height={18} alt="Guests" />
          <p className="text-[13px] text-[#595870] whitespace-nowrap truncate">
            {numberOfAdults + numberOfChildren > 0 ? guest : "Add your guests"}
          </p>
        </div>
      )}

      <Button
        type="button"
        onClick={(e) => {
          e.stopPropagation();
          handleHotelSearch();
        }}
        onMouseEnter={(e) => {
          e.stopPropagation();
        }}
        className={
          buttonText
            ? "bg-[#FFFFFF] border border-[#D8DCE2] rounded-[21.5px] text-core text-[12px] font-bold w-full md:w-auto hover:bg-white cursor-pointer flex items-center justify-center py-2.5 px-4 gap-2 ml-5"
            : "search-btn mr-1 rounded-[45px] w-[54px] h-[54px] md:w-[54px] cursor-pointer flex items-center justify-center"
        }
      >
        {buttonText ? (
          <span>{buttonText}</span>
        ) : (
          <>
            <Image src={searchIcon} width={18} height={18} alt="Search" />
          </>
        )}
      </Button>

      {isOpen && (
        <div
          className="absolute top-full left-0 right-0 md:right-auto mt-1 w-full md:w-72 bg-white rounded-[20px] shadow-xl p-4 z-30"
          onClick={(e) => e.stopPropagation()}
        >
          {/* Adults */}
          <div className="flex items-center justify-between py-2 px-3 rounded-lg">
            <h3 className="font-semibold text-sm">Adults</h3>
            <div className="w-36 flex items-center justify-between border border-[#C2C1E2] bg-[#F5F6FA] rounded-full">
              <button
                className="rounded-full p-2.5 text-core cursor-pointer"
                onClick={() => {
                  setNumberOfAdults((prev) => Math.max(0, prev - 1));
                  onSearchDataChange?.({
                    ...searchData!,
                    guests: {
                      adults: Math.max(0, numberOfAdults - 1),
                      children: numberOfChildren,
                    },
                  });
                }}
              >
                <IoRemove className="size-4" />
              </button>
              <span className="text-xs">{numberOfAdults}</span>
              <button
                className="rounded-full p-2.5 text-core cursor-pointer"
                onClick={() => {
                  setNumberOfAdults((prev) => prev + 1);
                  onSearchDataChange?.({
                    ...searchData!,
                    guests: {
                      adults: numberOfAdults + 1,
                      children: numberOfChildren,
                    },
                  });
                }}
              >
                <IoAdd className="size-4" />
              </button>
            </div>
          </div>

          {/* Children */}
          <div className="flex flex-col py-2 px-3 bg-[#F7F7FF] rounded-[20px]">
            <div className="flex items-center justify-between">
              <h3 className="font-semibold text-sm">Children</h3>
              <div className="w-36 flex items-center justify-between border border-[#C2C1E2] bg-[#F5F6FA] rounded-full">
                <button
                  className="rounded-full p-2.5 text-core cursor-pointer"
                  onClick={() => {
                    setNumberOfChildren((prev) => Math.max(0, prev - 1));
                    onSearchDataChange?.({
                      ...searchData!,
                      guests: {
                        adults: numberOfAdults,
                        children: Math.max(0, numberOfChildren - 1),
                      },
                    });
                  }}
                >
                  <IoRemove className="size-4" />
                </button>
                <span className="text-xs">{numberOfChildren}</span>
                <button
                  className="rounded-full p-2.5 text-core cursor-pointer"
                  onClick={() => {
                    setNumberOfChildren((prev) => prev + 1);
                    onSearchDataChange?.({
                      ...searchData!,
                      guests: {
                        adults: numberOfAdults,
                        children: numberOfChildren + 1,
                      },
                    });
                  }}
                >
                  <IoAdd className="size-4" />
                </button>
              </div>
            </div>

            {/* {numberOfChildren > 0 && (
              <div className="mt-4 flex flex-col gap-2">
                {Array.from({ length: numberOfChildren }).map((_, i) => (
                  <div
                    key={i}
                    className="relative flex items-center justify-between text-sm"
                  >
                    <label>Age of child {i + 1}</label>
                    <select
                      value={childrenAges[i]}
                      onChange={(e) => handleAgeChange(i, e.target.value)}
                      className="w-28 border border-core rounded-full px-1.5 py-2 pr-7 text-center text-core text-sm appearance-none"
                    >
                      <option value="">Select Age</option>
                      <option value="Under 1">Under 1</option>
                      {Array.from({ length: 17 }, (_, idx) => (
                        <option key={idx + 1} value={String(idx + 1)}>
                          {idx + 1}
                        </option>
                      ))}
                    </select>
                    <IoChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-core pointer-events-none" />
                  </div>
                ))}
              </div>
            )} */}
          </div>

          <button
            type="button"
            className="mt-3 w-full rounded-full bg-core text-white py-2.5 text-sm cursor-pointer"
            onClick={handleConfirm}
          >
            Confirm
          </button>
        </div>
      )}
    </div>
  );
};
