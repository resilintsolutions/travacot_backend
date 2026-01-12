"use client";
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from "@/components/shared/CardCarousel";
import outlineStar from "@/assets/images/asterik-star.png";
import divertArrow from "@/assets/images/divert-arrow.png";
import Image from "next/image";
import { getCookie } from "@/lib/cookies";
import { useSession } from "next-auth/react";

export const ExclusiveOffersCarousel = () => {
  const session = useSession();
  const token = session.data?.user.accessToken;
  const isLoggedIn = getCookie("access_token") || Boolean(token);
  return (
    <Carousel
      opts={{
        align: "start",
        dragThreshold: 10,
        skipSnaps: false,
      }}
    >
      <div className="flex flex-col sm:flex-row sm:items-center items-start justify-between gap-2.5 mb-4">
        <div className="flex flex-col w-auto">
          <h2 className="font-semibold text-core text-[20px]">Start saving!</h2>
          <p className="text-sm ml-0.5">
            What you see are Travacot Exclusive Offers and do not originate from
            the property.
          </p>
        </div>

        <div className="flex gap-5 mt-3 sm:mt-0">
          <CarouselPrevious className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm h-9 w-14 sm:h-10 sm:w-[68px]" />
          <CarouselNext className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm h-9 w-14 sm:h-10 sm:w-[68px]" />
        </div>
      </div>

      <CarouselContent className="overflow-visible -ml-2.5 sm:-ml-2.5 gap-2.5">
        {[
          {
            title: "Four Seasons",
            location: "Beirut Lebanon",
            promo: isLoggedIn
              ? "$10 cashback after booking 3 nights"
              : "Sign in to see exclusive offers",
          },
          {
            title: "Intercontinental Phoenicia",
            location: "Beirut Lebanon",
            promo: isLoggedIn
              ? "$15 off on your booking"
              : "Sign in to see exclusive offers",
          },
          {
            title: "Staybridge Suites",
            location: "Beirut Lebanon",
            promo: isLoggedIn
              ? "$15 off on your booking"
              : "Sign in to see exclusive offers",
          },
        ].map((offer, index) => (
          <CarouselItem
            key={index}
            style={{ flex: "0 0 auto" }}
            className={`pl-2.5 sm:pl-2.5 flex items-center justify-center`}
          >
            <div className="w-[300px] sm:w-[349px] h-[172px] bg-[#F9F2FC] rounded-2xl p-4 relative">
              <div className="flex items-start justify-between">
                <div className="flex items-center gap-2">
                  <Image
                    src={outlineStar}
                    alt="Exclusive"
                    className="w-7 h-8"
                  />
                  <span className="text-[20px] text-core font-medium">
                    Exclusive
                  </span>
                </div>

                <div className="flex items-center justify-center w-15 h-8 rounded-2xl bg-[#1C1740]">
                  <Image src={divertArrow} alt="Arrow" className="w-4 h-4" />
                </div>
              </div>

              <div className="mt-4">
                <h3 className="text-[17px] font-semibold text-[#1C1740]">
                  {offer.title}
                </h3>
                <p className="text-xs text-core">{offer.location}</p>

                <p
                  onClick={() => {
                    // eslint-disable-next-line @typescript-eslint/no-unused-expressions
                    isLoggedIn ? undefined : (window.location.href = "/login");
                  }}
                  className={`${!isLoggedIn ? "cursor-pointer" : ""} mt-3 text-[17px] font-semibold text-[#1C1740]`}
                >
                  {offer.promo}
                </p>
              </div>
            </div>
          </CarouselItem>
        ))}
      </CarouselContent>
    </Carousel>
  );
};
