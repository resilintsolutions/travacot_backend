"use client";

import { useEffect, useRef, useState } from "react";
import { FiSearch } from "react-icons/fi";
import polygon from "@/assets/images/polygon.svg";
import arrownDownIcon from "@/assets/images/down-arrow-icon.svg";
import Image from "next/image";

export default function SectionLocationSelector() {
  const [isOpen, setIsOpen] = useState(false);
  const [selectedLocation, setSelectedLocation] = useState("Lebanon, Beirut");
  const dropdownRef = useRef<HTMLDivElement>(null);

  const locations = [
    "Lebanon, Beirut",
    "USA, New York",
    "France, Paris",
    "Germany, Berlin",
    "Japan, Tokyo",
    "Australia, Sydney",
  ];

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
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
  return (
    <div className="mb-4 relative mt-4" ref={dropdownRef}>
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
        <div className="flex items-center justify-normal gap-2 text-core">
          <div className="flex items-center justify-center w-[55px] h-[55px] p-2.5 border border-[#C9D0E7] rounded-[70px] bg-[#F5F6FA]">
            <Image
              alt="arrow down"
              src={arrownDownIcon}
              width={15}
              height={23}
            />
          </div>
          <div
            onClick={() => setIsOpen(!isOpen)}
            className="w-fit h-10 md:h-[55px] md:w-[174px] px-4 md:px-0 sm:px-2 -mr-4 md:mr-0 hover:bg-[#F3E9FF] flex items-center justify-center gap-4 cursor-pointer relative"
          >
            <span className="text-xs md:text-base">{selectedLocation}</span>{" "}
            <Image alt="polygon" src={polygon} className="w-3 h-3 rotate-90" />
            {/* Dropdown */}
            {isOpen && (
              <div
                onClick={(e) => e.stopPropagation()}
                className="absolute top-full right-0 md:left-0 mt-1 py-4 px-2 w-full bg-white border rounded-lg shadow-lg z-50 min-w-[290px]"
              >
                <div className="flex items-center gap-2 h-10 bg-[#F5F6FA] rounded-[20px] px-3 mb-2">
                  <FiSearch className="w-5 h-5" />
                  <input
                    type="text"
                    placeholder="Where?"
                    className="w-full focus:ring-0 focus:outline-none text-xs placeholder:text-[#7F7F93]"
                  />
                </div>
                {locations.map((loc) => (
                  <div
                    key={loc}
                    onClick={() => {
                      setSelectedLocation(loc);
                      setIsOpen(false);
                    }}
                    className="w-full px-2 py-4 hover:bg-gray-100 cursor-pointer text-xs"
                  >
                    {loc}
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
