"use client";
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from "@/components/shared/CardCarousel";
import Image from "next/image";
import makretPlaceBedIcon from "@/assets/images/marketplace-bed-icon.svg";
import marketPlaceImage1 from "@/assets/images/market-place-bg-image1.svg";
import marketPlaceImage2 from "@/assets/images/market-place-bg-image2.svg";
import marketPlaceImage3 from "@/assets/images/market-place-bg-image3.svg";
import marketPlaceImage4 from "@/assets/images/market-place-bg-image4.svg";

import appIcon from "@/assets/images/get-the-app-icon.svg";

export default function MarketPlaceDeals() {
  return (
    <section className="overflow-hidden relative z-10" id="marketplace-deals">
      <div className="container mx-auto px-4 sm:px-6 md:px-6 lg:px-8 xl:px-10">
        <h2 className="font-semibold text-core text-[17px] md:text-[18px] lg:text-[17px] mt-4 mb-4 md:mt-6 lg:mt-4">
          Deals From The Marketplace
        </h2>
        <div className="relative">
          <div
            className="pointer-events-none absolute inset-0 w-full h-full min-h-80 sm:min-h-[420px] md:min-h-[450px] lg:min-h-[566px] p-6 sm:p-8 md:p-10 md:pt-20 md:pr-16 md:pb-16 md:pl-10 lg:pt-[193px] lg:pr-[608px] lg:pb-[243px] lg:pl-[68px] flex flex-col items-center justify-center gap-2.5 backdrop-blur-[2.9px] z-20"
            style={{ background: "#FFFFFF96" }}
          >
            <div className="flex items-center gap-4 sm:gap-6 md:gap-4 lg:gap-6 self-start md:self-start lg:self-start">
              <Image
                alt="Market place bed icon"
                src={makretPlaceBedIcon}
                className="sm:w-[334px] sm:h-[98px] md:w-[70px] md:h-[55px] lg:w-[54px] lg:h-[84px] object-contain shrink-0"
              />
              <div className="flex flex-col items-start justify-center sm:max-w-[334px] md:max-w-[200px] lg:max-w-[320px]">
                <p className="font-bold leading-snug text-core text-[15px] sm:text-[15px] md:text-[16px] text-wrap md:text-nowrap lg:text-[25px] lg:text-nowrap">
                  Download the App to gain access to our Marketplace
                </p>
                <Image
                  alt="App Store Badge"
                  src={appIcon}
                  className="w-[100px] md:w-[110px] lg:w-[126px]"
                />
              </div>
            </div>
          </div>
          <Carousel
            opts={{
              watchDrag: false,
            }}
          >
            <div className="hidden items-center justify-end gap-2.5 mb-4">
              <CarouselPrevious className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm h-10 w-[68px]" />
              <CarouselNext className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm h-10 w-[68px]" />
            </div>
            <CarouselContent className="overflow-visible">
              {[
                marketPlaceImage1,
                marketPlaceImage2,
                marketPlaceImage3,
                marketPlaceImage4,
              ].map((image, index) => (
                <CarouselItem
                  key={index}
                  className="basis-full md:basis-1/2 lg:basis-1/3 xl:basis-1/4"
                >
                  <div className="relative w-full h-[541px] sm:h-[541px] md:h-[420px] lg:h-[500px] rounded-[20px] overflow-hidden">
                    <Image
                      alt={`Market place deal ${index + 1}`}
                      src={image}
                      width={368.75}
                      height={541}
                    />
                  </div>
                </CarouselItem>
              ))}
            </CarouselContent>
          </Carousel>
        </div>
      </div>
    </section>
  );
}
