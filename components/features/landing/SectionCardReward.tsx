"use client";
import Autoplay from "embla-carousel-autoplay";
// import { IoCheckmarkCircle } from "react-icons/io5";
// import { MdLock } from "react-icons/md";
// import rewardCard1 from "@/assets/images/reward-card1.png";
// import rewardCard2 from "@/assets/images/reward-card2.png";
// import rewardCard3 from "@/assets/images/reward-card3.png";
import dealImage1 from "@/assets/images/on-going-deals1.svg";
import dealImage2 from "@/assets/images/on-going-deals2.svg";
import dealImage3 from "@/assets/images/on-going-deals3.svg";

import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
  type CarouselApi,
} from "@/components/shared/CardCarousel";
import { useEffect, useState } from "react";
import Image from "next/image";
// type Hotel = {
//   name: string;
//   description: string;
//   location: string;
//   rating: number;
//   discount: string;
//   bgCard: string;
//   bgBadge: string;
//   border: string;
//   image: StaticImageData; // or "StaticImageData" if using next/image import types
// };

// const hotels: Hotel[] = [
//   {
//     name: "Marriott Bay",
//     description: "Promo name (if there is a promotion)",
//     location: "Lebanon, Beirut, Minet El Hosn",
//     rating: 0,
//     discount: "30%",
//     bgCard: "#F9FCFB",
//     bgBadge: "#E9F4F0",
//     border: "#2A7157",
//     image: rewardCard1,
//   },
//   {
//     name: "Marriott Bay",
//     description: "15% Off + Free Breakfast for All Room Types",
//     location: "Lebanon, Beirut, Minet El Hosn",
//     rating: 2,
//     discount: "10%",
//     bgCard: "#FDFCFC",
//     bgBadge: "#F8F3F3",
//     border: "#984242",
//     image: rewardCard2,
//   },
//   {
//     name: "Marriott Bay",
//     description: "Save 20% if you book 30 days in advance",
//     location: "Lebanon, Beirut, Minet El Hosn",
//     rating: 2,
//     discount: "10%",
//     bgCard: "#FDFBFD",
//     bgBadge: "#F8F1F8",
//     border: "#9A2B99",
//     image: rewardCard3,
//   },
// ];

export default function SectionCardReward() {
  const [api, setApi] = useState<CarouselApi>();
  const [current, setCurrent] = useState(0);
  const [count, setCount] = useState(0);

  useEffect(() => {
    if (!api) return;

    const update = () => {
      setCount(api.scrollSnapList().length);
      setCurrent(api.selectedScrollSnap() + 1);
    };

    // Initial update
    update();

    // When carousel changes slide
    api.on("select", update);

    // Fix: Delay update so Embla can recalc size
    const handleResize = () => {
      setTimeout(update, 50);
    };

    window.addEventListener("resize", handleResize);

    return () => {
      window.removeEventListener("resize", handleResize);
    };
  }, [api]);

  // const handleLogin = () => {
  //   window.location.href = "/login";
  // };

  return (
    <section className="overflow-hidden">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
        <Carousel
          setApi={setApi}
          plugins={[
            Autoplay({
              delay: 5000,
            }),
          ]}
        >
          <div className="text-core flex items-center justify-between mb-4">
            <h2 className="font-semibold text-[17px]">Ongoing Deals</h2>
            <div className="flex items-center gap-2.5">
              <p className="mr-2">
                {current} / {count}
              </p>
              <CarouselPrevious className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm h-10 w-[68px]" />
              <CarouselNext className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm h-10 w-[68px]" />
            </div>
          </div>
          <CarouselContent>
            {[dealImage1, dealImage2, dealImage3].map((hotel, i) => (
              <CarouselItem
                key={i}
                className="basis-full md:basis-1/2 xl:basis-1/3"
              >
                <div className="rounded-[20px] h-[426px] md:h-[524px] lg:h-[524px] flex flex-col overflow-hidden">
                  <div className="h-[524px] w-full relative">
                    <Image alt="reward card" src={hotel} priority />
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
