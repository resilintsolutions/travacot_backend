"use client";

import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from "@/components/shared/CardCarousel";

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

import { FaEllipsisVertical } from "react-icons/fa6";
import Image from "next/image";
import { useState } from "react";
import { HotelOffer } from "@/app/search/types";
import offer1 from "@/assets/images/offer1.png";
import { useRouter } from "next/navigation";
import { useAddFavorite } from "@/app/favorites/hooks/useFavorites";
import { toast } from "sonner";
interface HotelCardProps {
  hotel: HotelOffer;
}

export default function OffersCarousel({ hotels }: { hotels: HotelOffer[] }) {
  return (
    <Carousel
      opts={{
        align: "start",
        dragThreshold: 10,
        skipSnaps: false,
      }}
    >
      <div className="flex items-center justify-end gap-2.5 mb-4">
        <CarouselPrevious className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm h-10 w-[68px]" />
        <CarouselNext className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm h-10 w-[68px]" />
      </div>
      <CarouselContent className="overflow-visible">
        {(hotels || []).map((hotel, index) => (
          <CarouselItem
            key={index}
            className="basis-3/4 md:basis-1/2 lg:basis-1/3 xl:basis-1/4 -mr-1"
          >
            <HotelCard hotel={hotel} />
          </CarouselItem>
        ))}
      </CarouselContent>
    </Carousel>
  );
}

const HotelCard = ({ hotel }: HotelCardProps) => {
  const [hover, setHover] = useState(false);
  const router = useRouter();
  const addFavoriteMutation = useAddFavorite();

  const handleAddFavourite = (hotelCode: number) => {
    // Implement your logic to add the hotel to favorites here
    console.log(`Hotel with code ${hotelCode} added to favorites.`);
    addFavoriteMutation.mutate({
      itemType: "hotel",
      itemId: hotelCode,
    });
    toast.success("Hotel added to favorites", {
      duration: 3000,
      position: "top-center",
    });
  };

  return (
    <div
      onMouseEnter={() => setHover(true)}
      onMouseLeave={() => setHover(false)}
      onClick={() => router.push(`/hotels/${hotel.hotel_code}`)}
      style={{
        backgroundColor: hover ? "#F5F6FA" : "transparent",
      }}
      className="flex flex-col overflow-hidden rounded-[20px] p-1 transition-all ease-in-out duration-200 h-full cursor-pointer"
    >
      <div className="relative flex-none max-w-[365px] aspect-square md:h-[268px] bg-gray-300 rounded-[20px] overflow-hidden">
        <Image
          alt={hotel.name}
          src={hotel.image_url || offer1}
          className="object-cover"
          fill
          sizes="(max-width: 768px) 75vw, (max-width: 1024px) 50vw, (max-width: 1280px) 33vw, 25vw"
        />
      </div>

      <div className="flex flex-col flex-1 text-core py-2 px-1">
        <div className="flex items-start justify-between">
          <div className="flex flex-col">
            <h3 className="font-semibold leading-tight text-xs sm:text-base">
              {hotel.name}
            </h3>
            <p className="text-xs">
              {hotel.city}, {hotel.country}
            </p>
          </div>

          <div className="flex items-center gap-2 mt-2">
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <button>
                  <FaEllipsisVertical className="size-5" />
                </button>
              </DropdownMenuTrigger>
              <DropdownMenuContent side="bottom" align="end">
                <DropdownMenuItem
                  className="cursor-pointer"
                  onClick={(e) => {
                    e.stopPropagation();
                    handleAddFavourite(hotel.hotel_code);
                  }}
                >
                  Add to favorites
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>

        <div className="flex items-center justify-between mt-auto pt-2 md:pt-4">
          <div className="flex flex-col md:hidden">
            <span className="text-xs leading-none text-[#4B5563]">
              Price for {hotel.nights} night
            </span>
            <div className="flex items-center gap-1 tracking-tight text-sm">
              <span className="text-[#8D2929] line-through">
                ${(hotel.pricing.vendor_total || 0).toLocaleString()}
              </span>
              <span className="font-bold">
                ${(hotel.pricing.marked_total || 0).toLocaleString()}
              </span>
            </div>
          </div>

          <div className="hidden md:flex flex-col">
            <span className="text-sm leading-none">
              Price for {hotel.nights} night
            </span>
            <div className="flex items-center gap-1 tracking-tight text-base">
              <span className="text-[#8D2929] line-through">
                ${(hotel.pricing.vendor_total || 0).toLocaleString()}
              </span>
              <span className="font-bold">
                ${(hotel.pricing.marked_total || 0).toLocaleString()}
              </span>
            </div>
          </div>

          {hotel.rating > 0 && (
            <div className="flex items-center justify-center gap-1 border border-[#A48B05] bg-[#F6F2DA] rounded-[20px] h-[25px] w-14">
              <div className="bg-[#FFD700] border border-[#A48B05] w-[11px] h-[11px] rounded-full" />
              <span className="font-medium text-xs">
                {hotel.rating.toFixed(2)}
              </span>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
