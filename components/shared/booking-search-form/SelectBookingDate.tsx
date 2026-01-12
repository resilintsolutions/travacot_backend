"use client";
import { type DateRange } from "react-day-picker";
import Image from "next/image";
import { useEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";
import calendarArrowRight from "@/assets/images/calendar-arrow-right.svg";
import calendarIcon from "@/assets/images/calendar-icon.svg";
import { cn } from "@/lib/utils";
import { Calendar } from "@/components/shared/CalendarDefault";
import { SearchParams } from "../../../app/search/types";
import { formatDate } from "date-fns";
interface Props {
  className?: string;
  searchData: SearchParams;
  onSearchDataChange: (data: SearchParams) => void;
  isOtherPageSearch?: boolean;
}

export const SelectBookingDate = ({
  className,
  searchData,
  onSearchDataChange,
  isOtherPageSearch = false,
}: Props) => {
  const [isOpen, setIsOpen] = useState<boolean>(false);
  const [dateRange, setDateRange] = useState<DateRange | undefined>({
    from: searchData.checkIn ? new Date(searchData.checkIn) : undefined,
    to: searchData.checkOut ? new Date(searchData.checkOut) : undefined,
  });
  const [activeSelection, setActiveSelection] = useState<
    "from" | "to" | "none"
  >("from");
  const [dropdownPosition, setDropdownPosition] = useState({
    top: 0,
    left: 0,
    width: 0,
  });

  const wrapperRef = useRef<HTMLDivElement>(null);
  const dropdownRef = useRef<HTMLDivElement>(null);

  const options: Intl.DateTimeFormatOptions = {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  };

  const optionsNumber: Intl.DateTimeFormatOptions = {
    year: "numeric",
    month: "long",
    day: "numeric",
  };

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        wrapperRef.current &&
        !wrapperRef.current.contains(event.target as Node) &&
        dropdownRef.current &&
        !dropdownRef.current.contains(event.target as Node)
      ) {
        setIsOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  useEffect(() => {
    if (isOpen && wrapperRef.current) {
      const rect = wrapperRef.current.getBoundingClientRect();
      const isMobile = window.innerWidth < 768;
      setDropdownPosition({
        top: rect.bottom + window.scrollY,
        left: rect.left + window.scrollX + (isMobile ? 0 : rect.width / 2),
        width: rect.width,
      });
    }
  }, [isOpen]);

  return (
    <div
      ref={wrapperRef}
      onClick={() => setIsOpen(!isOpen)}
      className={cn(
        "w-full md:w-[190px] flex flex-col items-start justify-center px-5 py-4 md:py-0 bg-white hover:bg-[#F7F7FF] text-core relative cursor-pointer",
        className,
        isOtherPageSearch && "w-[190px] p-2.5 opacity-100"
      )}
    >
      {isOtherPageSearch ? (
        <>
          <p className="font-bold text-[13px]">Date</p>
          <p className="text-[13px] text-[#595870]">
            {dateRange?.from && dateRange?.to
              ? `${dateRange.from.toLocaleDateString("en-GB", options)} - ${dateRange.to.toLocaleDateString("en-GB", options)}`
              : "Add your dates"}
          </p>
        </>
      ) : (
        <div className="flex items-center gap-2.5">
          <Image src={calendarIcon} width={17} height={17} alt="Calendar" />
          <p className="text-[13px] text-[#595870] whitespace-nowrap truncate">
            {dateRange?.from && dateRange?.to
              ? `${formatDate(dateRange.from, "MMM dd")} - ${formatDate(dateRange.to, "MMM dd")}`
              : "Add your dates"}
          </p>
        </div>
      )}

      {isOpen &&
        typeof window !== "undefined" &&
        createPortal(
          <div
            ref={dropdownRef}
            onClick={(e) => e.stopPropagation()}
            style={{
              position: "absolute",
              top: `${dropdownPosition.top + 4}px`,
              left: `${dropdownPosition.left}px`,
              transform: window.innerWidth >= 768 ? "translateX(-50%)" : "none",
              width: "min(calc(100vw - 2rem), 600px)",
              maxWidth: "600px",
              zIndex: 9999,
            }}
            className="p-4 bg-white rounded-[20px] shadow-lg"
          >
            <div className="flex flex-col text-core gap-4">
              <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div
                  className={cn(
                    "flex-1 p-4 bg-core text-white rounded-lg",
                    activeSelection === "from"
                      ? "bg-core text-white"
                      : "bg-surface text-core"
                  )}
                  onClick={() => setActiveSelection("from")}
                >
                  <h2 className="text-xs font-semibold">Check-in</h2>
                  <p className="text-lg font-bold">
                    {dateRange?.from
                      ? dateRange.from.toLocaleDateString(
                          "en-US",
                          optionsNumber
                        )
                      : "-"}
                  </p>
                </div>
                <div className="hidden md:block size-10">
                  <Image
                    alt="Arrow Right"
                    src={calendarArrowRight}
                    className="size-full"
                  />
                </div>
                <div
                  className={cn(
                    "flex-1 p-4 rounded-lg transition-colors duration-200",
                    activeSelection === "to"
                      ? "bg-core text-white"
                      : "bg-surface text-core"
                  )}
                  onClick={() => setActiveSelection("to")}
                >
                  <h2 className="text-xs font-semibold">Check-out</h2>
                  <p className="text-lg font-bold">
                    {dateRange?.to
                      ? dateRange.to.toLocaleDateString("en-US", optionsNumber)
                      : "-"}
                  </p>
                </div>
              </div>

              <div className="w-full overflow-x-auto">
                <Calendar
                  mode="range"
                  defaultMonth={dateRange?.from}
                  selected={dateRange}
                  numberOfMonths={
                    typeof window !== "undefined" && window.innerWidth < 768
                      ? 1
                      : 2
                  }
                  showOutsideDays={false}
                  weekStartsOn={1}
                  disabled={(date) => {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    return date < today;
                  }}
                  onSelect={(range) => {
                    setDateRange(range);

                    onSearchDataChange({
                      ...searchData,
                      checkIn:
                        formatDate(
                          new Date(range?.from || new Date()),
                          "yyyy-MM-dd"
                        ) || "",
                      checkOut:
                        formatDate(
                          new Date(range?.to || new Date()),
                          "yyyy-MM-dd"
                        ) || "",
                    });

                    if (!range?.from) {
                      // No date selected yet
                      setActiveSelection("from");
                    } else if (
                      range.from &&
                      (!range.to || range.from.getTime() === range.to.getTime())
                    ) {
                      // User just picked check-in → highlight checkout
                      setActiveSelection("to");
                    } else if (
                      range.from &&
                      range.to &&
                      range.from.getTime() !== range.to.getTime()
                    ) {
                      // Both selected → reset to check-in, but visually both surface
                      setActiveSelection("none");
                    }
                  }}
                />
              </div>
            </div>
          </div>,
          document.body
        )}
    </div>
  );
};
