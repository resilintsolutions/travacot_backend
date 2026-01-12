"use client";

import RoomSelectionCardRow from "./RoomSelectionCardRow";
import { HotelInfo, Room } from "@/app/search/types";

type RoomSelectionSectionProps = {
  rooms: Room[];
  hotel: HotelInfo;
};

const RoomSelectionSection = ({ rooms, hotel }: RoomSelectionSectionProps) => {
  return (
    <section id="room-selections" className="mt-6">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
        <div className="rounded-[20px] bg-[#FAFAFC] p-4">
          <h2 className="text-core text-base font-bold">
            Choose your rooms(s)
          </h2>
          <p className=" text-core text-sm mb-6">
            Don&apos;t worry we will ask you about breakfast and the refund
            policy after you proceed.
          </p>

          <div>
            {/* <div className="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 text-core mb-4">
              <h4>Room Types</h4>
              <div className="flex items-center justify-between rounded-full w-36 sm:w-52 px-4 border border-core py-1.5">
                <span>All</span>
                <IoChevronDown />
              </div>
            </div> */}

            <div className="flex flex-col gap-2">
              {rooms.map((room, i) => (
                <RoomSelectionCardRow key={i} room={room} hotel={hotel} />
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default RoomSelectionSection;
