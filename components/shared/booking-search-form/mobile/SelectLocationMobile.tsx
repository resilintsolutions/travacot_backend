"use client";

import { useState } from "react";
import { IoLocationSharp } from "react-icons/io5";
import Image from "next/image";
import { locations } from "../SearchLocation";

type SelectLocationMobileProps = {
  handleLocationSelect: (location: string) => void;
};

const SelectLocationMobile = ({
  handleLocationSelect,
}: SelectLocationMobileProps) => {
  const [query, setQuery] = useState<string>("");

  const filteredLocations = locations.filter(
    (item) =>
      item.title.toLowerCase().includes(query.toLowerCase()) ||
      item.description.toLowerCase().includes(query.toLowerCase())
  );

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setQuery(e.target.value);
  };

  return (
    <div className="px-4">
      <div className="flex flex-col gap-2 p-2 bg-[#F5F6FA] rounded-md mb-4 text-core">
        <p className="flex items-center gap-1.5 text-xs font-semibold">
          <span>
            <IoLocationSharp className="size-5" />
          </span>{" "}
          Going to
        </p>
        <input
          type="text"
          placeholder="Search destinations"
          className="w-full px-2 bg-transparent outline-none focus:ring-0 focus:outline-none placeholder:text-gray-400 text-sm"
          value={query}
          onChange={handleInputChange}
        />
      </div>

      <div className="flex flex-col gap-2 h-[calc(85vh-16rem)] overflow-y-auto rounded-md text-core">
        {filteredLocations.length > 0 ? (
          filteredLocations.map((item, index) => (
            <div
              key={index}
              onClick={() => {
                handleLocationSelect(item.value);
                setQuery(item.title);
              }}
              className="flex items-center gap-4"
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
                <p className="w-[200px] truncate">{item.description}</p>
              </div>
            </div>
          ))
        ) : (
          <p className="text-sm text-gray-500 p-4">No results found ...</p>
        )}
      </div>
    </div>
  );
};

export default SelectLocationMobile;
