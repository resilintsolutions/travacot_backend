"use client";
import { useEffect, useState } from "react";
import { type DateRange } from "react-day-picker";
import { IoClose } from "react-icons/io5";
import { FiSearch } from "react-icons/fi";
import { FaPen } from "react-icons/fa";
import searchIcon from "@/assets/images/search-icon.svg";
import { cn } from "@/lib/utils";
import SelectBookingDateMobile from "./SelectBookingDateMobile";
import SelectNumberOfGuestsMobile from "./SelectNumberOfGuestsMobile";
import SelectLocationMobile from "./SelectLocationMobile";
import Link from "next/link";
import { SearchParams } from "@/app/search/types";
import { formatDate } from "date-fns";
import Image from "next/image";
interface BookingSearchFormMobileProps {
  isCompact?: boolean;
  handleHotelSearch: () => void;
  handleMobileSearchDataChange: (data: SearchParams) => void;
  searchDataInitial: SearchParams;
}

export default function BookingSearchFormMobile({
  handleHotelSearch,
  isCompact = false,
  handleMobileSearchDataChange,
  searchDataInitial,
}: BookingSearchFormMobileProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [tabs, setTabs] = useState<"destination" | "dates" | "guests">(
    "destination"
  );

  const [searchData, setSearchData] = useState<SearchParams>(searchDataInitial);

  console.log("Mobile search data:", searchData);

  useEffect(() => {
    handleMobileSearchDataChange(searchData);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchData]);

  useEffect(() => {
    const scrollBarWidth =
      window.innerWidth - document.documentElement.clientWidth;
    const marketPlaceDeals = document.getElementById("marketplace-deals");

    if (isOpen) {
      document.body.classList.add("overflow-hidden");
      document.body.style.paddingRight = `${scrollBarWidth}px`;
      if (marketPlaceDeals) {
        marketPlaceDeals.style.display = "none";
      }
    } else {
      document.body.classList.remove("overflow-hidden");
      document.body.style.paddingRight = "";
      if (marketPlaceDeals) {
        marketPlaceDeals.style.display = "";
      }
    }

    return () => {
      document.body.classList.remove("overflow-hidden");
      document.body.style.paddingRight = "";
      if (marketPlaceDeals) {
        marketPlaceDeals.style.display = "";
      }
    };
  }, [isOpen]);

  console.log({ searchData });

  return (
    <>
      {isCompact ? (
        <div className="w-full h-[79px] md:h-auto md:p-5 p-3 bg-[#F0EFFF4D] flex items-center rounded-[10px] md:rounded-[20px]">
          <div
            onClick={() => setIsOpen(true)}
            className="w-full h-[55px] md:h-16 bg-white rounded-[27px] p-5 flex items-center gap-2.5 cursor-pointer"
          >
            <Image src={searchIcon} width={18} height={18} alt="Search" />
            <div className="flex flex-col justify-center flex-1">
              <span className="font-bold text-xs text-core">Search Places</span>
              <div className="flex items-center">
                <span className="text-xs text-[#595870]">Date range</span>
                <span className="mx-1 text-[#595870]">•</span>
                <span className="text-xs text-[#595870]">Number of guests</span>
              </div>
            </div>
          </div>
        </div>
      ) : (
        <div
          onClick={() => setIsOpen((prev) => !prev)}
          className="relative z-50 w-full flex items-center justify-between bg-[#F9F4FF] border border-[#E5E0FF] rounded-full px-4 py-2 shadow-sm cursor-pointer"
        >
          {/* Left Section */}
          <div className="flex items-center space-x-3">
            <FiSearch className="text-core size-7" />
            <div className="flex flex-col leading-tight text-core">
              <span className="text-sm">{searchData.destination}</span>
              <span className="text-xs">
                {searchData.checkIn} - {searchData.checkOut} •{" "}
                {searchData.guests.adults + searchData.guests.children} Guests
              </span>
            </div>
          </div>

          {/* Right Section */}
          <Link
            onClick={(e) => e.stopPropagation()}
            href="/search"
            className="bg-core text-white py-2.5 px-5 rounded-full"
          >
            <FaPen className="text-sm size-3.5" />
          </Link>
        </div>
      )}

      {/* Overlay */}
      <div
        onClick={() => setIsOpen(false)}
        className={cn(
          "fixed inset-0 bg-core/20 z-40 transition-opacity duration-500",
          isOpen ? "opacity-100 visible" : "opacity-0 invisible"
        )}
      />

      {/* Bottom Sheet */}
      <div
        className={cn(
          "fixed bottom-0 left-0 right-0 bg-white z-50 h-[calc(100vh-150px)] rounded-t-3xl transition-transform duration-500 ease-in-out",
          isOpen ? "translate-y-0" : "translate-y-full"
        )}
      >
        <div className="flex gap-6 px-5 py-3 relative border-b border-gray-200">
          <div
            onClick={() => setTabs("destination")}
            className={cn(
              "text-core py-2 px-1 cursor-pointer whitespace-nowrap",
              tabs === "destination" && "border-b-2 border-core font-medium"
            )}
          >
            Destination
          </div>
          <div
            onClick={() => setTabs("dates")}
            className={cn(
              "text-core py-2 px-1 cursor-pointer whitespace-nowrap",
              tabs === "dates" && "border-b-2 border-core font-medium"
            )}
          >
            Dates
          </div>
          <div
            onClick={() => setTabs("guests")}
            className={cn(
              "text-core py-2 px-1 cursor-pointer whitespace-nowrap",
              tabs === "guests" && "border-b-2 border-core font-medium"
            )}
          >
            Guests
          </div>

          <button
            onClick={(e) => {
              e.stopPropagation();
              setIsOpen(false);
            }}
            className="absolute right-4 top-1/2 -translate-y-1/2 text-core text-base font-bold p-2"
          >
            <IoClose className="w-7 h-7" />
          </button>
        </div>

        {tabs === "destination" && (
          <SelectLocationMobile
            handleLocationSelect={(location: string) => {
              setSearchData((prev) => ({
                ...prev,
                destination: location,
              }));
              setTabs("dates");
            }}
          />
        )}

        {tabs === "dates" && (
          <div className="px-4">
            <SelectBookingDateMobile
              initialCheckIn={searchData.checkIn}
              initialCheckOut={searchData.checkOut}
              handleDateSelect={(dateRange: DateRange | undefined) => {
                setSearchData((prev) => ({
                  ...prev,
                  checkIn:
                    formatDate(
                      new Date(dateRange?.from || new Date()),
                      "yyyy-MM-dd"
                    ) || "",
                  checkOut:
                    formatDate(
                      new Date(dateRange?.to || new Date()),
                      "yyyy-MM-dd"
                    ) || "",
                }));
                // setTabs("guests");
              }}
            />
          </div>
        )}

        {tabs === "guests" && (
          <div className="px-4">
            <SelectNumberOfGuestsMobile
              handleGuestsSelect={(guests) => {
                setSearchData((prev) => ({
                  ...prev,
                  guests: guests,
                }));
                // setIsOpen(false);
              }}
            />
          </div>
        )}

        {tabs === "guests" && (
          <div className="absolute bottom-0 left-0 right-0 p-4">
            <button
              type="button"
              onClick={handleHotelSearch}
              className="bg-core text-white py-2.5 rounded-full w-full flex items-center justify-center"
            >
              <span className="font-medium">Search</span>
            </button>
          </div>
        )}
      </div>
    </>
  );
}
