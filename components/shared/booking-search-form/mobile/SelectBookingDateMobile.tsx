"use client";

import { useState } from "react";
import { type DateRange } from "react-day-picker";

import { Calendar } from "@/components/shared/CalendarDefault";

import { cn } from "@/lib/utils";

interface SelectBookingDateMobileProps {
  handleDateSelect: (dateRange: DateRange | undefined) => void;
  initialCheckIn?: string; // YYYY-MM-DD format
  initialCheckOut?: string; // YYYY-MM-DD format
}

const SelectBookingDateMobile = ({
  handleDateSelect,
  initialCheckIn,
  initialCheckOut,
}: SelectBookingDateMobileProps) => {
  const getInitialDateRange = (): DateRange | undefined => {
  if (initialCheckIn && initialCheckOut) {
      return {
        from: new Date(initialCheckIn),
        to: new Date(initialCheckOut),
      };
    }
    return undefined;
  };

  const [dateRange, setDateRange] = useState<DateRange | undefined>(
    getInitialDateRange()
  );
  const [activeSelection, setActiveSelection] = useState<
    "from" | "to" | "none"
  >("from");

  const optionsNumber: Intl.DateTimeFormatOptions = {
    year: "numeric",
    month: "long",
    day: "numeric",
  };

  return (
    <div className="flex flex-col text-core gap-4">
      <div className="flex flex-row items-center gap-2">
        <div
          className={cn(
            "flex-1 p-4 rounded-lg transition-colors duration-300 flex flex-col gap-4",
            activeSelection === "from"
              ? "bg-core text-white"
              : "bg-surface text-core"
          )}
          onClick={() => setActiveSelection("from")}
        >
          <h2 className="text-xs font-semibold">Check-in</h2>
          <p className="text-xs">
            {dateRange?.from
              ? dateRange.from.toLocaleDateString("en-US", optionsNumber)
              : "-"}
          </p>
        </div>

        <div
          className={cn(
            "flex-1 p-4 rounded-lg transition-colors duration-300 flex flex-col gap-4",
            activeSelection === "to"
              ? "bg-core text-white"
              : "bg-surface text-core"
          )}
          onClick={() => setActiveSelection("to")}
        >
          <h2 className="text-xs font-semibold">Check-out</h2>
          <p className="text-xs">
            {dateRange?.to
              ? dateRange.to.toLocaleDateString("en-US", optionsNumber)
              : "-"}
          </p>
        </div>
      </div>

      <div className="w-full px-2.5">
        <Calendar
          mode="range"
          defaultMonth={dateRange?.from}
          selected={dateRange}
          numberOfMonths={1}
          showOutsideDays={false}
          weekStartsOn={1}
          disabled={(date) => {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return date < today;
          }}
          onSelect={(range) => {
            setDateRange(range);
            handleDateSelect(range);
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
  );
};

export default SelectBookingDateMobile;
