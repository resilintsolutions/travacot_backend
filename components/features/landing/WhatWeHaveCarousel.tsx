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

import offer1 from "@/assets/images/offer1.png";
import offer2 from "@/assets/images/offer2.png";
import offer3 from "@/assets/images/offer3.png";
import offer4 from "@/assets/images/offer4.png";

import polygon from "@/assets/images/polygon.svg";

import { FaEllipsisVertical } from "react-icons/fa6";
import Image, { StaticImageData } from "next/image";
import { useState } from "react";

interface Hotel {
  name: string;
  rating: number;
  reviews: string;
  location: string;
  price: number;
  originalPrice: number;
  image: StaticImageData;
  bgColor: string;
}

interface HotelCardProps {
  hotel: Hotel;
}

const hotels = [
  {
    name: "Radisson Blu Airport",
    rating: 4.5,
    reviews: "1.2k",
    location: "Spain, Madrid, Main St.",
    price: 1360,
    originalPrice: 1500,
    image: offer1,
    bgColor: "#FEF3E3",
  },
  {
    name: "Hilton Downtown",
    rating: 4.2,
    reviews: "900",
    location: "Paris, France",
    price: 1200,
    originalPrice: 1400,
    image: offer2,
    bgColor: "#E9E9E9",
  },
  {
    name: "Sheraton Beach Resort",
    rating: 4.7,
    reviews: "3k",
    location: "Dubai, UAE",
    price: 1500,
    originalPrice: 1700,
    image: offer3,
    bgColor: "#E2EDF3",
  },
  {
    name: "Intercontinental",
    rating: 4.3,
    reviews: "2k",
    location: "Tokyo, Japan",
    price: 1800,
    originalPrice: 2000,
    image: offer4,
    bgColor: "#E8F3FF",
  },
];

export default function WhatWeHaveCarousel({
  exploreBtnTitle,
}: {
  exploreBtnTitle?: string;
}) {
  return (
    <Carousel
      opts={{
        align: "start",
        dragThreshold: 10,
        skipSnaps: false,
      }}
    >
      <div className="flex items-center justify-between gap-2.5 mb-4">
        <button className="rounded-[30px] py-2.5 px-3 bg-[#F5F6FA] flex items-center justify-center gap-2">
          <span className="text-core font-medium text-[15px]">
            {exploreBtnTitle}
          </span>
          <Image alt="polygon" src={polygon} className="w-3 h-3" />
        </button>
        <div className="flex items-center justify-center gap-2.5">
          <CarouselPrevious className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm h-10 w-[68px]" />
          <CarouselNext className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm h-10 w-[68px]" />
        </div>
      </div>
      <CarouselContent className="overflow-visible">
        {hotels.map((hotel, index) => (
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
  return (
    <div
      onMouseEnter={() => setHover(true)}
      onMouseLeave={() => setHover(false)}
      style={{ backgroundColor: hover ? hotel.bgColor : "transparent" }}
      className="flex flex-col overflow-hidden rounded-[20px] p-1 transition-all ease-in-out duration-500"
    >
      <div className="relative flex-none max-w-[365px] aspect-square md:h-[268px] bg-gray-300 rounded-[20px] overflow-hidden">
        <Image
          alt="Recommendation Stays"
          src={hotel.image}
          className="object-cover"
          fill
          sizes="(max-width: 768px) 75vw, (max-width: 1024px) 50vw, (max-width: 1280px) 33vw, 25vw"
        />
      </div>

      <div className="flex-none text-core md:h-[103px] py-2 px-1">
        <div className="flex items-start justify-between">
          <div className="flex flex-col">
            <h3 className="font-semibold leading-tight text-xs sm:text-base">
              {hotel.name}
            </h3>
            <p className="text-xs">{hotel.location}</p>
          </div>

          <div className="flex items-center gap-2 mt-2">
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <button>
                  <FaEllipsisVertical className="size-5" />
                </button>
              </DropdownMenuTrigger>
              <DropdownMenuContent side="bottom" align="end">
                <DropdownMenuItem>Add to favorites</DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>

        <div className="flex items-center justify-between mt-2 md:mt-4">
          <p className="md:hidden text-xs text-core">
            <strong>$ 1,250</strong> for 10 nights
          </p>
          <div className="hidden md:flex flex-col">
            <span className="text-sm leading-none">Price for 7 night</span>
            <div className="flex items-center gap-1 tracking-tight text-base">
              <span className="text-[#8D2929] line-through">
                ${hotel.originalPrice.toLocaleString()}
              </span>
              <span className="font-bold">${hotel.price.toLocaleString()}</span>
            </div>
          </div>

          <div className="flex items-center justify-center gap-1 border border-[#A48B05] bg-[#F6F2DA] rounded-[20px] h-[25px] w-14">
            <div className="bg-[#FFD700] border border-[#A48B05] w-[11px] h-[11px] rounded-full" />
            <span className="font-medium text-xs">
              {hotel.rating.toFixed(2)}
            </span>
          </div>
        </div>
      </div>
    </div>
  );
};
