import Image from "next/image";
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from "@/components/shared/CardCarousel";
import { SECTION_DOWNLOAD_APP_IMAGES } from "@/lib/constants";

export function SectionDownloadAppCarousel() {
  return (
    <Carousel
      opts={{
        align: "start",
        dragThreshold: 10,
        skipSnaps: false,
      }}
    >
      <div className="flex flex-col sm:flex-row sm:items-center items-start justify-between gap-2.5 mb-4">
        <div className="flex flex-row items-baseline gap-1.5 flex-nowrap w-full">
          <h2 className="font-semibold text-core text-[17px] mb-0 leading-none">
            Travacot
          </h2>
          <span className="text-[#4F2662] font-semibold text-[15px] sm:text-[17px] mb-0 bg-[#F3DFFC] border border-[#E5AEFF] rounded px-2 py-0.5 sm:py-1 h-6 sm:h-8 inline-flex items-center justify-center">
            PULSE
          </span>
          <div className="ml-0.5 overflow-hidden max-w-[420px] sm:max-w-none">
            <div
              className="text-sm whitespace-nowrap marquee"
              role="status"
              aria-label="Latest updates from PULSE"
            >
              Get the latest updates from us with PULSE!
            </div>
          </div>
        </div>

        <div className="flex gap-5 mt-3 sm:mt-0">
          <CarouselPrevious className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm h-9 w-14 sm:h-10 sm:w-[68px]" />
          <CarouselNext className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm h-9 w-14 sm:h-10 sm:w-[68px]" />
        </div>
      </div>

      <CarouselContent className="overflow-visible -ml-2.5 sm:-ml-2.5">
        {SECTION_DOWNLOAD_APP_IMAGES.map((app, index) => (
          <CarouselItem
            key={index}
            className={`basis-3/4 md:basis-1/2 lg:basis-1/3 xl:basis-1/4 pl-2.5 sm:pl-2.5 ${
              index === SECTION_DOWNLOAD_APP_IMAGES.length - 1
                ? "flex items-center justify-center min-h-[220px] md:min-h-80"
                : ""
            }`}
          >
            <Image
              src={app.image}
              alt={app.alt}
              className="w-full h-auto rounded-lg object-contain max-h-[360px] sm:max-h-[420px]"
            />
          </CarouselItem>
        ))}
      </CarouselContent>
    </Carousel>
  );
}

// Local styles for marquee animation
const marqueeStyles = `
  @keyframes travacot-marquee {
    0% { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
  }
  .marquee {
    display: inline-block;
    animation: travacot-marquee 12s linear infinite;
  }
  .marquee:hover {
    animation-play-state: paused;
  }
`;

export default function SectionDownloadApp() {
  return (
    <section className="overflow-hidden">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
        <SectionDownloadAppCarousel />
      </div>
      <style>{marqueeStyles}</style>
    </section>
  );
}
