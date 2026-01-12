"use client";

import Autoplay from "embla-carousel-autoplay";

import {
  Carousel,
  CarouselContent,
  CarouselItem,
} from "@/components/shared/CardCarousel";

import destination1 from "@/assets/images/destination1.png";
import destination2 from "@/assets/images/destination2.png";
import destination3 from "@/assets/images/destination3.png";
import destination4 from "@/assets/images/destination4.png";
import { StaticImageData } from "next/image";

interface Destination {
  id: number;
  country: string;
  city: string;
  flagUrl: string;
  image: StaticImageData;
}

const destinations: Destination[] = [
  {
    id: 1,
    country: "Qatar",
    city: "Lusail",
    flagUrl: "https://flagcdn.com/w40/qa.png",
    image: destination1,
  },
  {
    id: 2,
    country: "UAE",
    city: "Dubai",
    flagUrl: "https://flagcdn.com/w40/ae.png",
    image: destination2,
  },
  {
    id: 3,
    country: "Turkey",
    city: "Istanbul",
    flagUrl: "https://flagcdn.com/w40/tr.png",
    image: destination3,
  },
  {
    id: 4,
    country: "Saudi Arabia",
    city: "Riyadh",
    flagUrl: "https://flagcdn.com/w40/sa.png",
    image: destination4,
  },
  {
    id: 5,
    country: "Qatar",
    city: "Lusail",
    flagUrl: "https://flagcdn.com/w40/qa.png",
    image: destination1,
  },
];

export default function SectionTravelDestination() {
  return (
    <section className="mt-10">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
        <h2 className="font-semibold text-core text-[17px] mb-4 tracking-tight">
          Where Travelers from Lebanon Usually Go
        </h2>

        <Carousel
          opts={{
            align: "start",
            dragThreshold: 10,
            skipSnaps: false,
          }}
          plugins={[
            Autoplay({
              delay: 5000,
            }),
          ]}
        >
          <CarouselContent className="h-72">
            {destinations.map((_, index) => (
              <CarouselItem
                key={index}
                className="basis-[85%] md:basis-1/2 lg:basis-1/3 xl:basis-1/5"
              >
                <div
                  style={{ backgroundImage: `url(${_.image.src})` }}
                  className="relative w-auto h-[280px] rounded-3xl overflow-hidden flex items-end p-4 bg-cover bg-no-repeat"
                >
                  <div className="absolute -bottom-2 left-0 right-0 h-1/2 bg-linear-to-t from-core/90 via-core/40 to-transparent"></div>
                  <div className="relative flex items-center space-x-3 z-10">
                    <div className="w-6 h-6 rounded-full overflow-hidden">
                      <img
                        src="https://flagcdn.com/w40/qa.png"
                        alt="Qatar Flag"
                        className="w-full h-full object-cover"
                      />
                    </div>

                    <p className="text-white text-xs">Qatar, Lusail</p>
                  </div>
                </div>
              </CarouselItem>
            ))}
          </CarouselContent>
        </Carousel>
      </div>
    </section>
  );
}
