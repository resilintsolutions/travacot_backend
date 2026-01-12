"use client";
import Image, { type StaticImageData } from "next/image";
import { useEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";
import { cn } from "@/lib/utils";
import searchPlane from "@/assets/images/search-plane.svg";
import buildingBrown from "@/assets/images/search-building-brown.svg";
import buildingGreen from "@/assets/images/search-building-green.svg";
import buildingPurple from "@/assets/images/search-building-purple.svg";
import locationIcon from "@/assets/images/location-icon.svg";
import arrowDown from "@/assets/images/arrow-down-icon.svg";
import { SearchParams } from "../../../app/search/types";
interface LocationItem {
  icon: StaticImageData;
  title: string;
  description: string;
  value: string;
}

export const locations: LocationItem[] = [
  {
    icon: searchPlane,
    title: "Airport BEY, Lebanon",
    value: "BEY", // Airport Code
    description: "Want a stay around the airport?",
  },
  {
    icon: buildingBrown,
    title: "Beirut, Lebanon",
    value: "BEY", // City Code
    description: "Looking for a stay nearby?",
  },
  {
    icon: buildingGreen,
    title: "Istanbul, Turkiye",
    value: "IST", // City Code
    description: "People from Lebanon go there!",
  },
  {
    icon: buildingPurple,
    title: "London, United Kingdom",
    value: "LON", // City Code (Metropolitan Area)
    description: "Buckingham Palace visit?!",
  },
  {
    icon: searchPlane,
    title: "Airport JFK, New York",
    value: "JFK", // Airport Code
    description: "Stay near the busiest airport in New York!",
  },
  {
    icon: buildingBrown,
    title: "Paris, France",
    value: "CDG", // Primary International Airport Code (often used for city)
    description: "Romantic getaway in the City of Lights",
  },
  {
    icon: buildingGreen,
    title: "Tokyo, Japan",
    value: "TYO", // City Code (Metropolitan Area)
    description: "Explore the vibrant city life and sushi spots",
  },
  {
    icon: buildingPurple,
    title: "Sydney, Australia",
    value: "SYD", // Airport/City Code
    description: "Enjoy the Opera House and harbor views",
  },
  {
    icon: buildingBrown,
    title: "Dubai, UAE",
    value: "DXB", // Airport/City Code
    description: "Luxury stays in the desert metropolis",
  },
  {
    icon: buildingGreen,
    title: "Rio de Janeiro, Brazil",
    value: "RIO", // City Code (Metropolitan Area)
    description: "Experience beaches, carnival, and Christ the Redeemer",
  },
  {
    icon: searchPlane,
    title: "Airport LAX, Los Angeles",
    value: "LAX", // Airport Code
    description: "Quick stay before your next flight",
  },
  {
    icon: buildingPurple,
    title: "Rome, Italy",
    value: "FCO", // Primary International Airport Code (often used for city)
    description: "History, pizza, and gelato adventures await",
  },
  {
    icon: buildingBrown,
    title: "Barcelona, Spain",
    value: "BCN", // Airport/City Code
    description: "Enjoy Gaudi architecture and Mediterranean vibes",
  },
  {
    icon: buildingGreen,
    title: "Cape Town, South Africa",
    value: "CPT", // Airport/City Code
    description: "Mountains, beaches, and wine regions all in one",
  },
];

interface Props {
  className?: string;
  searchData: SearchParams;
  onSearchDataChange: (data: SearchParams) => void;
  isOtherPageSearch?: boolean;
}

export const SearchLocation = ({
  className,
  searchData,
  onSearchDataChange,
  isOtherPageSearch = false,
}: Props) => {
  const [isOpen, setIsOpen] = useState<boolean>(false);
  const [query, setQuery] = useState<string>(searchData.destination || "");
  const [dropdownPosition, setDropdownPosition] = useState({
    top: 0,
    left: 0,
    width: 0,
  });

  const wrapperRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);
  const dropdownRef = useRef<HTMLDivElement>(null);

  const filteredLocations = locations.filter(
    (item) =>
      item.title.toLowerCase().includes(query.toLowerCase()) ||
      item.description.toLowerCase().includes(query.toLowerCase())
  );

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setQuery(e.target.value);
  };

  const handleSelection = (location: string) => {
    onSearchDataChange({ ...searchData, destination: location });
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
      setDropdownPosition({
        top: rect.bottom + window.scrollY,
        left: rect.left + window.scrollX,
        width: rect.width,
      });
    }
  }, [isOpen]);

  return (
    <div
      ref={wrapperRef}
      onClick={() => {
        setIsOpen(true);
        inputRef.current?.focus();
      }}
      className={cn(
        "flex-1 flex flex-col w-[300px] items-start justify-center px-5 py-4 md:py-0 bg-white hover:bg-[#F7F7FF] text-core md:rounded-[45px_0px_0px_45px] relative cursor-pointer md:w-[300px]",
        className,
        isOtherPageSearch &&
          "md:rounded-[0px_0px_0px_0px] w-[250px] p-2.5 opacity-100"
      )}
    >
      {isOtherPageSearch ? (
        <>
          <p className="font-bold text-[13px]">Location</p>
          <input
            ref={inputRef}
            type="text"
            value={query}
            onChange={handleInputChange}
            onClick={(e) => {
              e.stopPropagation();
              setIsOpen(true);
            }}
            placeholder="Search your destinations"
            className="text-[13px] placeholder:text-[13px] placeholder:text-[#595870] focus:outline-none focus:ring-0 bg-transparent"
          />
        </>
      ) : (
        <div className="flex justify-between w-full">
          <div className="flex items-center">
            <Image
              src={locationIcon}
              width={26}
              height={23}
              alt="Location Icon"
              className="mr-3"
            />
            <input
              ref={inputRef}
              type="text"
              value={query}
              onChange={handleInputChange}
              onClick={(e) => {
                e.stopPropagation();
                setIsOpen(true);
              }}
              placeholder="Where to?"
              className="text-[13px] placeholder:text-[13px] placeholder:text-[#595870] focus:outline-none focus:ring-0 bg-transparent"
            />
          </div>
          <Image src={arrowDown} width={18} height={11} alt="arrown Icon" />
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
              width:
                dropdownPosition.width < 400
                  ? `${dropdownPosition.width}px`
                  : "max-content",
              minWidth: "400px",
              maxWidth: "400px",
              zIndex: 9999,
            }}
            className="p-4 bg-white rounded-[20px] shadow-lg"
          >
            <div className="flex flex-col gap-2 overflow-y-auto overflow-x-hidden max-h-80">
              {filteredLocations.length > 0 ? (
                filteredLocations.map((item, index) => (
                  <div
                    key={index}
                    onClick={() => {
                      setIsOpen(false);
                      setQuery(item.title);
                      handleSelection(item.value);
                    }}
                    className="flex items-center gap-4 rounded-md hover:bg-gray-50 cursor-pointer"
                  >
                    <div className="flex-none rounded-[10px] overflow-hidden">
                      <Image
                        alt="logo"
                        src={item.icon}
                        width={42}
                        height={42}
                        className="object-contain"
                      />
                    </div>

                    <div className="flex flex-col text-xs">
                      <h2 className="font-semibold">{item.title}</h2>
                      <p className="w-[120px] lg:w-40 truncate">
                        {item.description}
                      </p>
                    </div>
                  </div>
                ))
              ) : (
                <p className="text-sm text-gray-500">No results found ...</p>
              )}
            </div>
          </div>,
          document.body
        )}
    </div>
  );
};
