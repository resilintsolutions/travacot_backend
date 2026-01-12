"use client";

import { useEffect, useState } from "react";
import { SearchLocation } from "./SearchLocation";
import { SelectBookingDate } from "./SelectBookingDate";
import { SelectNumberOfGuests } from "./SelectNumberOfGuests";
import BookingSearchFormMobile from "./mobile";
import { SearchParams } from "../../../app/search/types";
import { useAvailabilitySearch } from "../../../app/search/hooks/useHotels";
import { useRouter } from "next/navigation";
import { useSearch } from "@/app/search/context/SearchContext";
import { toast } from "sonner";

interface BookingSearchFormProps {
  useMobile?: boolean;
  buttonText?: string;
  className?: string;
  formClassName?: string;
  isCompact?: boolean;
  isOtherPageSearch?: boolean;
}

export default function BookingSearchForm({
  useMobile = false,
  buttonText,
  // className,
  formClassName,
  isCompact,
  isOtherPageSearch = false,
}: BookingSearchFormProps) {
  const router = useRouter();
  const { searchData: contextSearchData, setSearchData } = useSearch();
  const [isMobile, setIsMobile] = useState<boolean | null>(null);
  const [doSearch, setDoSearch] = useState(false);
  const [searchData, setSearchDataState] =
    useState<SearchParams>(contextSearchData);

  useEffect(() => {
    setSearchDataState(contextSearchData);
  }, [contextSearchData]);

  const hotelSearch = useAvailabilitySearch(searchData, doSearch);

  const handleSearchDataChange = (data: SearchParams) => {
    setSearchDataState(data);
  };

  useEffect(() => {
    const handleResize = () => {
      setIsMobile(window.innerWidth < 768); // Tailwind's `md` breakpoint
    };
    handleResize(); // run once on mount
    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  const handleHotelSearch = () => {
    if (!searchData.destination) {
      return toast.error("Please fill in all required fields of search", {
        duration: 4000,
        position: "top-center",
      });
    }
    setDoSearch(true);
    setSearchData(searchData);
    router.push(`/search`);
    // Implement search logic or navigation here
  };

  // Reset the `doSearch` flag after the query completes (either success or error)
  // so a failed request doesn't keep retriggering subsequent fetches.
  useEffect(() => {
    if (doSearch && (hotelSearch.isSuccess || hotelSearch.isError)) {
      setDoSearch(false);
    }
  }, [doSearch, hotelSearch.isSuccess, hotelSearch.isError]);

  if (isMobile === null) return null; // avoid hydration mismatch
  if (useMobile && isMobile)
    return (
      <BookingSearchFormMobile
        searchDataInitial={searchData}
        isCompact={isCompact}
        handleHotelSearch={handleHotelSearch}
        handleMobileSearchDataChange={(data: SearchParams) =>
          handleSearchDataChange(data)
        }
      />
    );

  return (
    <div
      className={`${isOtherPageSearch ? "bg-[#F5F6FACC]" : "bg-[#F0EFFF4D]"} w-full md:w-[90%] lg:w-[796px] ${isOtherPageSearch ? "h-[77px]" : "h-[104px]"} ${isOtherPageSearch ? "p-0" : "p-5"} flex items-center backdrop-blur-[11.199999809265137px] md:rounded-[20px]`}
    >
      <div
        className={`w-full md:w-[calc(100%-20px)] lg:w-[756px] ${isOtherPageSearch ? "h-full" : "md:h-16 lg:h-16"} flex flex-col md:flex-row ${isOtherPageSearch ? "gap-0" : "gap-0.5"} md:rounded-[25px] overflow-visible relative z-50`}
      >
        <SearchLocation
          className={formClassName}
          searchData={searchData}
          onSearchDataChange={handleSearchDataChange}
          isOtherPageSearch={isOtherPageSearch}
        />
        <SelectBookingDate
          className={formClassName}
          searchData={searchData}
          onSearchDataChange={handleSearchDataChange}
          isOtherPageSearch={isOtherPageSearch}
        />
        <SelectNumberOfGuests
          className={formClassName}
          buttonText={buttonText}
          searchData={searchData}
          onSearchDataChange={handleSearchDataChange}
          handleHotelSearch={handleHotelSearch}
          isOtherPageSearch={isOtherPageSearch}
        />
      </div>
    </div>
  );
}
