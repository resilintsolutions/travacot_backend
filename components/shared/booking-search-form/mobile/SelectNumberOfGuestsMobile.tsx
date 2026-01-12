"use client";

import { useState, useEffect } from "react";
import { IoAdd, IoRemove } from "react-icons/io5";

interface SelectNumberOfGuestsMobileProps {
  handleGuestsSelect: (guests: { adults: number; children: number }) => void;
}

const SelectNumberOfGuestsMobile = ({
  handleGuestsSelect,
}: SelectNumberOfGuestsMobileProps) => {
  const [numberOfAdults, setNumberOfAdults] = useState<number>(2);
  const [numberOfChildren, setNumberOfChildren] = useState<number>(0);
  // const [childrenAges, setChildrenAges] = useState<string[]>([]);

  useEffect(() => {
    handleGuestsSelect({
      adults: numberOfAdults,
      children: numberOfChildren,
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [numberOfAdults, numberOfChildren]);

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

  return (
    <div onClick={(e) => e.stopPropagation()}>
      <h2 className="text-core font-semibold text-sm">
        Tell us who&apos;s coming!
      </h2>
      <p className="text-xs text-[#595870] mb-4">
        Please provide us an accurate number of guests coming to avoid issues
        upon arrival.
      </p>
      {/* Adults */}
      <div className="flex items-center py-1.5 px-2.5 rounded-lg text-core text-xs mb-4">
        <h3 className="font-bold w-1/3">Adults</h3>
        <div className="w-32 flex items-center justify-between border border-[#C2C1E2] bg-[#F5F6FA] rounded-full">
          <button
            className="rounded-full p-2.5 text-core cursor-pointer"
            onClick={() => setNumberOfAdults((prev) => Math.max(0, prev - 1))}
          >
            <IoRemove className="size-4" />
          </button>
          <span>{numberOfAdults}</span>
          <button
            className="rounded-full p-2.5 text-core cursor-pointer"
            onClick={() => setNumberOfAdults((prev) => prev + 1)}
          >
            <IoAdd className="size-4" />
          </button>
        </div>
      </div>

      {/* Children */}
      <div className="flex flex-col py-1.5 px-2.5 bg-[#F5F6FA] rounded-lg text-core text-xs">
        <div className="flex items-center">
          <h3 className="font-bold w-1/3">Children</h3>
          <div className="w-32 flex items-center justify-between border border-[#C2C1E2] bg-[#F5F6FA] rounded-full">
            <button
              className="rounded-full p-2.5 text-core cursor-pointer"
              onClick={() =>
                setNumberOfChildren((prev) => Math.max(0, prev - 1))
              }
            >
              <IoRemove className="size-4" />
            </button>
            <span>{numberOfChildren}</span>
            <button
              className="rounded-full p-2.5 text-core cursor-pointer"
              onClick={() => setNumberOfChildren((prev) => prev + 1)}
            >
              <IoAdd className="size-4" />
            </button>
          </div>
        </div>

        {/* {numberOfChildren > 0 && (
          <div className="mt-4 flex flex-col gap-2 max-h-96 overflow-y-auto">
            {Array.from({ length: numberOfChildren }).map((_, i) => (
              <div
                key={i}
                className="relative flex items-center justify-between text-xs"
              >
                <label>Age of child {i + 1}</label>
                <select
                  value={childrenAges[i]}
                  onChange={(e) => handleAgeChange(i, e.target.value)}
                  className="w-28 border border-core rounded-full pl-1.5 pr-5 py-3 text-center text-xs appearance-none"
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
    </div>
  );
};

export default SelectNumberOfGuestsMobile;
