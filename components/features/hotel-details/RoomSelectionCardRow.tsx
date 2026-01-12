"use client";
import Image from "next/image";
import { useEffect, useState } from "react";
import { IoIosTimer } from "react-icons/io";
import { FaCheck } from "react-icons/fa";
import { IoClose } from "react-icons/io5";
import parentChild from "@/assets/images/parent-child.png";
import bedIcon from "@/assets/images/bed-icon.svg";
import roomAndBeds from "@/assets/images/room-and-beds.png";
import arrowBoth from "@/assets/images/arrow-both-sharp.svg";
import showrateArrow from "@/assets/images/show-rates-arrow.svg";
import hotelSearch4 from "@/assets/images/hotel-search4.png";
import hotelSearch7 from "@/assets/images/hotel-search7.png";
import { Checkbox } from "@/components/ui/checkbox";
import { cn } from "@/lib/utils";
import Modal from "@/components/shared/Modal";
import { useRouter } from "next/navigation";
import {
  Carousel,
  CarouselApi,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from "@/components/shared/CardCarousel";
import { HotelInfo, Room } from "@/app/search/types";
import { formatDate } from "date-fns";
// import { features } from "@/app/hotels/[slug]/page";

const RoomSelectionCardRow = ({
  room,
  hotel,
}: {
  room: Room;
  hotel: HotelInfo;
}) => {
  const router = useRouter();
  const [openDetails, setOpenDetails] = useState(false);
  const [selectedIndex, setSelectedIndex] = useState<number | null>(null);
  const [isMobile, setIsMobile] = useState<boolean | null>(null);
  const hotelId = hotel.id;

  const [isOpenMobileDeal, setIsOpenMobileDeal] = useState<boolean>(false);

  const onCloseMobileDeal = () => setIsOpenMobileDeal(false);

  useEffect(() => {
    const selectedRoomRate = room.hb_raw.rates[selectedIndex ?? 0];
    localStorage.setItem(
      "selectedHotelRoom",
      JSON.stringify({
        hotel: hotel,
        room: room,
        selectedRate: selectedRoomRate,
      })
    );
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedIndex]);

  useEffect(() => {
    const handleResize = () => {
      setIsMobile(window.innerWidth < 768);
    };
    handleResize();
    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  useEffect(() => {
    // Reset openDetails when switching to mobile view
    if (isMobile && openDetails) {
      setOpenDetails(false);
    }
  }, [isMobile, openDetails]);

  return (
    <div className="bg-white p-4 rounded-[20px] border border-[#E6E8F2] text-core hover:bg-[#FBFBFC]">
      <div
        className="flex flex-col sm:flex-row gap-4"
        onClick={() => {
          if (isMobile) {
            setIsOpenMobileDeal(true);
            return;
          }

          setOpenDetails((prev) => !prev);
        }}
      >
        <div className="shrink-0 h-[200px] md:h-[150px] w-full md:w-[201px] bg-gray-200 rounded-lg flex justify-center items-center">
          <Image
            src={room.images[0] || hotelSearch4}
            width={150}
            height={50}
            alt={room.name}
            className="h-full w-full object-cover rounded-lg"
          />
        </div>

        <div className="w-full flex flex-col lg:flex-row items-start justify-between gap-6 md:gap-0">
          <div className="flex-1 w-full h-full flex flex-col items-start justify-between mb-4 lg:mb-0">
            <div className="flex items-center space-x-2 mb-2 lg:mb-4">
              <div className="flex items-center gap-1 bg-[#B34343] text-white text-xs px-1.5 py-1 rounded-[5px]">
                <IoIosTimer />
                {room.remainingRooms} Rooms Left
              </div>
            </div>
            <div className="flex flex-col gap-4">
              <RoomSelectionModalDetails room={room} />

              <div className="flex items-center gap-1">
                <div className="flex items-center gap-1 text-xs bg-[#F9F4FF] border border-[#BD81FF] rounded-[5px] py-1 px-2">
                  <span className="text-[#5F3296]">
                    Save US$ 20 as a member!
                  </span>
                </div>
                <span className="bg-[#F2F4FF] border border-[#ACB6FB] text-[#2D3FB4] text-xs px-3 py-1 rounded-[5px]">
                  Best Value
                </span>
              </div>
            </div>
          </div>

          <div className="flex-1 w-full h-full flex flex-col justify-center gap-0 lg:gap-2 xl:gap-4">
            {room.roomSizeSqm && (
              <div className="flex items-center text-xs">
                <div className="shrink-0 min-w-[84px] xl:min-w-[120px] flex items-center gap-2">
                  <Image
                    alt="Arrow Both Sharp"
                    src={arrowBoth}
                    className="w-3 h-3"
                  />
                  <h3 className="text-xs font-normal">Room Size</h3>
                </div>
                <p className="font-medium text-xs">{room.roomSizeSqm} m²</p>
              </div>
            )}

            <div className="flex items-center text-xs">
              <div className="shrink-0 min-w-[84px] xl:min-w-[120px] flex items-center gap-2">
                <Image alt="icon" src={parentChild} className="w-3 h-auto" />
                <h3 className="text-xs font-normal">Room Fit</h3>
              </div>
              <p className="font-medium text-xs">{room.roomFit}</p>
            </div>

            {room.bedType && (
              <div className="flex items-center text-xs">
                <div className="shrink-0 min-w-[84px] xl:min-w-[120px] flex items-center gap-2">
                  <Image alt="icon" src={bedIcon} className="w-4 h-auto" />
                  <h3>Bedding</h3>
                </div>
                <div className="flex flex-col">
                  <p className="font-medium text-xs">{room.bedType}</p>
                  <p className="text-[#065F46]">
                    Bed type: Subject to availability
                  </p>
                </div>
              </div>
            )}
          </div>

          <div className="flex-1 w-full h-full flex flex-col md:items-end md:justify-end md:text-end">
            <span className="text-sm lg:text-base">From</span>
            <span className="text-sm lg:text-base font-semibold text-core">
              {room.currency} {room.pricePerNight} per night per room
            </span>
            <span className="text-xs lg:text-sm text-core/50 mb-2 tracking-tight">
              Excludes local taxes and fees payable at the property
            </span>

            <button className="h-10 w-full md:w-[120px] bg-core rounded-[30px] text-white text-xs flex items-center justify-center gap-2">
              <Image alt="show rate" src={showrateArrow} className="w-5 h-5" />
              <span>{openDetails ? "Hide Rates" : "Show Rates"}</span>
            </button>
          </div>
        </div>
      </div>

      <MobileDealSelector
        isOpen={isOpenMobileDeal}
        onClose={onCloseMobileDeal}
        room={room}
        hotel={hotel}
      />

      {openDetails && !isMobile && (
        <div className={cn("border-t mt-4 text-core", isMobile && "hidden")}>
          <div className="flex flex-col xl:flex-row xl:justify-between gap-10">
            <div className="flex-1">
              <Carousel>
                <div className="mt-2 text-core flex items-center justify-between mb-4 gap-4">
                  <h2 className="text-core font-semibold whitespace-nowrap m-0 leading-tight">
                    Don&apos;t get overwhelmed, we already chose the top{" "}
                    {room.hb_raw.rates.length}!
                  </h2>
                  <div className="flex items-center justify-end gap-2.5 shrink-0">
                    <CarouselPrevious className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm" />
                    <CarouselNext className="bg-white static top-auto left-auto translate-x-0 translate-y-0 shadow-sm" />
                  </div>
                </div>
                <CarouselContent>
                  {room.hb_raw.rates.map((rate, index) => {
                    const itemCount = room.hb_raw.rates.length;

                    let itemClasses = "flex-[0_0_calc(50%-8px)]"; // Mobile: 2 items per row (50% - gap compensation)

                    if (itemCount === 1) {
                      itemClasses =
                        "sm:flex-[0_0_100%] md:flex-[0_0_100%] lg:flex-[0_0_100%]";
                    } else if (itemCount === 2) {
                      itemClasses =
                        "sm:flex-[0_0_calc(50%-8px)] md:flex-[0_0_calc(50%-8px)] lg:flex-[0_0_calc(50%-8px)]";
                    } else if (itemCount === 3) {
                      itemClasses =
                        "sm:flex-[0_0_calc(50%-8px)] md:flex-[0_0_calc(33.333%-10px)] lg:flex-[0_0_calc(33.333%-10px)]";
                    } else if (itemCount === 4) {
                      itemClasses =
                        "sm:flex-[0_0_calc(50%-8px)] md:flex-[0_0_calc(33.333%-10px)] lg:flex-[0_0_calc(25%-10px)]";
                    } else {
                      // For 5+ items, show 2 on mobile, 3 on tablet, 4 on desktop
                      itemClasses =
                        "sm:flex-[0_0_calc(50%-8px)] md:flex-[0_0_calc(33.333%-10px)] lg:flex-[0_0_calc(25%-10px)]";
                    }
                    // const cancellationDate = formatDate(
                    //   new Date(rate.cancellationPolicies[0]?.from),
                    //   "dd/MM/yy"
                    // );

                    // const cancellationTime = formatDate(
                    //   new Date(rate.cancellationPolicies[0]?.from),
                    //   "HH:mm"
                    // );

                    return (
                      <CarouselItem key={index} className={`${itemClasses}`}>
                        <div
                          className={cn(
                            "cursor-pointer rounded-2xl p-5 w-full h-[220px] flex flex-col overflow-hidden", // Uniform fixed height for all cards
                            selectedIndex === index
                              ? index === 0
                                ? "border-2 border-[#8FD8B4] bg-[#F4FBF7] shadow-lg" // Enhanced: Thicker border and more pronounced shadow for the primary selected card (index 0)
                                : "border-2 border-[#A8B2E0] bg-[#F6F7FF] shadow-lg" // Enhanced: Slightly modified border color for other selected cards for better contrast, thicker border, pronounced shadow
                              : "border border-gray-200 hover:border-gray-300 bg-white" // Enhanced: Subtle border for unselected, hover effect for better discoverability
                          )}
                          onClick={(e) => {
                            e.stopPropagation();
                            setSelectedIndex(index);
                          }}
                        >
                          <div className="flex items-center space-x-3 mb-4">
                            <Checkbox
                              id={`deal-${index}`}
                              checked={selectedIndex === index}
                              onChange={() => setSelectedIndex(index)}
                              className={cn(
                                "rounded-full h-5 w-5", // Enhanced: Slightly larger checkbox
                                selectedIndex === index
                                  ? "data-[state=checked]:bg-[#065F46] data-[state=checked]:text-white border-none" // Enhanced: Checkbox uses the primary color when selected
                                  : "border-gray-400"
                              )}
                            />
                            <label
                              htmlFor={`deal-${index}`}
                              className="text-core leading-snug cursor-pointer flex flex-col"
                            >
                              <span className="font-semibold text-lg text-gray-800">
                                {rate.boardName === "BED AND BREAKFAST"
                                  ? "Breakfast"
                                  : rate.boardName === "ROOM ONLY"
                                    ? "Room Only"
                                    : rate.boardName}
                              </span>
                            </label>
                          </div>

                          {/* Scrollable content area to keep footer pinned */}

                          <div className="mb-2 text-xs overflow-y-auto flex-1 pr-1">
                            <p
                              className={cn(
                                "flex items-start gap-2", // Aligned items to the start for better wrapping
                                selectedIndex === index
                                  ? "text-[#065F46]"
                                  : "text-[#107567]" // Consistent color use for selected/unselected states
                              )}
                            >
                              <span className="text-sm">
                                {" "}
                                <FaCheck className="w-4 h-4" />
                              </span>{" "}
                              {rate.freeCancellation ? (
                                <span className="flex-1">
                                  Free Cancellation until{" "}
                                  <span className="font-bold">
                                    {rate.freeCancellationUntil}
                                  </span>
                                  .
                                </span>
                              ) : (
                                <span className="flex-1">
                                  This rate is non-refundable.
                                </span>
                              )}
                            </p>
                          </div>

                          {rate.boardName === "BED AND BREAKFAST" && (
                            <div className="mb-4 text-xs">
                              <p
                                className={cn(
                                  "flex items-start gap-2", // Aligned items to the start for better wrapping
                                  selectedIndex === index
                                    ? "text-[#065F46]"
                                    : "text-[#107567]" // Consistent color use for selected/unselected states
                                )}
                              >
                                <span className="text-sm">
                                  {" "}
                                  <FaCheck className="w-4 h-4" />
                                </span>{" "}
                                <span className="flex-1">
                                  {rate.boardName === "BED AND BREAKFAST"
                                    ? "Breakfast Included"
                                    : ""}
                                </span>
                              </p>
                            </div>
                          )}

                          {/* Footer pinned at bottom */}
                          <div className="mt-auto pt-2 border-t border-gray-100 flex justify-between items-end">
                            <div className="flex flex-col">
                              <span className="text-xs font-medium text-gray-500 mb-0.5">
                                Total Stay Price:
                              </span>
                              <div
                                className={cn(
                                  "font-semibold text-[17px]", // Enhanced: Increased font size (text-2xl) and weight (font-extrabold) for maximum price emphasis
                                  selectedIndex === index
                                    ? "text-[#065F46]"
                                    : "text-gray-900" // Use a darker color for the price when unselected to maintain readability
                                )}
                              >
                                {room.currency} {rate.pricing.final_price}
                              </div>
                            </div>
                            <div className="flex flex-col items-end">
                              <span className="text-xs text-gray-400 mb-0.5">
                                Taxes Incl.
                              </span>
                            </div>
                          </div>
                        </div>
                      </CarouselItem>
                    );
                  })}
                </CarouselContent>
              </Carousel>
            </div>

            <div className="flex items-end justify-end">
              <div className="flex flex-col">
                <button
                  disabled={selectedIndex === null}
                  className={cn(
                    "flex-none h-10 w-35 bg-[#2D2A7B] cursor-pointer rounded-[20px] flex items-center justify-center text-white text-xs font-bold",
                    selectedIndex !== null
                      ? "hover:bg-[#1e1b55] transition"
                      : "bg-gray-400 cursor-not-allowed"
                  )}
                  onClick={() => {
                    router.push(`/hotels/${hotelId}/booking`);
                  }}
                >
                  Reserve
                </button>
                <span className="text-xs text-core text-center mt-1">
                  You won&apos;t be charged yet
                </span>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

const MobileDealSelector = ({
  isOpen,
  onClose,
  room,
  hotel,
}: {
  isOpen: boolean;
  onClose: () => void;
  room: Room;
  hotel: HotelInfo;
}) => {
  const router = useRouter();
  const [selectedIndex, setSelectedIndex] = useState<number | null>(null);

  console.log("MobileDealSelector Rendered", { isOpen, room, hotel });

  useEffect(() => {
    const selectedRoomRate = room.hb_raw.rates[selectedIndex ?? 0];
    localStorage.setItem(
      "selectedHotelRoom",
      JSON.stringify({
        hotel: hotel,
        room: room,
        selectedRate: selectedRoomRate,
      })
    );
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedIndex]);

  useEffect(() => {
    const scrollBarWidth =
      window.innerWidth - document.documentElement.clientWidth;

    if (isOpen) {
      document.body.classList.add("overflow-hidden");
      document.body.style.paddingRight = `${scrollBarWidth}px`;
    } else {
      document.body.classList.remove("overflow-hidden");
      document.body.style.paddingRight = "";
    }

    return () => {
      document.body.classList.remove("overflow-hidden");
      document.body.style.paddingRight = "";
    };
  }, [isOpen]);
  return (
    <>
      <div
        className={cn(
          "fixed inset-0 bg-core/50 z-50 transition-opacity duration-300 ease-in-out",
          isOpen ? "opacity-100" : "opacity-0 pointer-events-none"
        )}
        onClick={onClose}
      />

      <div
        className={cn(
          "fixed bottom-0 left-0 right-0 bg-white z-50 max-h-[90vh] rounded-t-3xl flex flex-col transition-transform duration-500 ease-in-out",
          isOpen ? "translate-y-0" : "translate-y-full"
        )}
        style={{ paddingBottom: "env(safe-area-inset-bottom)" }}
      >
        <div className="shrink-0 p-4 border-b sticky top-0 bg-white">
          <div className="flex items-start justify-between gap-4">
            <div className="flex flex-col flex-1">
              <h4 className="font-semibold text-base text-core">
                Don&apos;t get overwhelmed.
              </h4>
              <p className="text-sm text-gray-600">
                We already chose the top {(room?.hb_raw?.rates || []).length}!
              </p>
            </div>
            <button
              onClick={onClose}
              className="shrink-0 p-1 hover:bg-gray-100 rounded-full transition"
            >
              <IoClose className="text-2xl text-gray-600" />
            </button>
          </div>
        </div>

        <div className="flex-1 overflow-y-auto p-4 space-y-3">
          {room.hb_raw.rates.map((rate, index) => {
            const cancellationDate = formatDate(
              new Date(rate.cancellationPolicies[0]?.from),
              "dd/MM/yy"
            );

            const cancellationTime = formatDate(
              new Date(rate.cancellationPolicies[0]?.from),
              "HH:mm"
            );
            return (
              <div
                key={index}
                className={cn(
                  "cursor-pointer rounded-2xl border-2 p-4 transition-all duration-200 flex flex-col",
                  index === 0 ? "border-[#8FD8B4]" : "border-gray-200",
                  selectedIndex === index
                    ? index === 0
                      ? "bg-[#F4FBF7] shadow-md"
                      : "bg-[#F6F7FF] shadow-md border-[#A8B2E0]"
                    : "bg-white hover:border-gray-300"
                )}
                onClick={() => setSelectedIndex(index)}
              >
                <div className="flex items-start gap-3 mb-3">
                  <Checkbox
                    id={`deal-${index}`}
                    checked={selectedIndex === index}
                    onChange={() => setSelectedIndex(index)}
                    className={cn(
                      "rounded-full h-5 w-5 shrink-0 mt-0.5",
                      selectedIndex === index
                        ? "data-[state=checked]:bg-[#065F46] data-[state=checked]:text-white border-none"
                        : "border-gray-400"
                    )}
                  />
                  <div className="flex-1 min-w-0">
                    <label
                      htmlFor={`deal-${index}`}
                      className="text-core cursor-pointer block"
                    >
                      <span className="font-semibold text-base block">
                        {rate.boardName === "BED AND BREAKFAST"
                          ? "Breakfast Included"
                          : rate.boardName === "ROOM ONLY"
                            ? "Room Only"
                            : rate.boardName}
                      </span>
                    </label>
                  </div>
                </div>

                <div className="mb-3 text-xs">
                  <p
                    className={cn(
                      "flex items-start gap-2",
                      selectedIndex === index
                        ? "text-[#065F46] font-medium"
                        : "text-[#107567]"
                    )}
                  >
                    <span className="shrink-0 mt-0.5">
                      <FaCheck className="w-3 h-3" />
                    </span>
                    <span className="flex-1">
                      Free Cancellation until{" "}
                      <span className="font-bold">
                        {cancellationDate} at {cancellationTime}
                      </span>
                    </span>
                  </p>
                </div>

                {rate.boardName === "BED AND BREAKFAST" && (
                  <div className="mb-3 text-xs">
                    <p
                      className={cn(
                        "flex items-start gap-2",
                        selectedIndex === index
                          ? "text-[#065F46] font-medium"
                          : "text-[#107567]"
                      )}
                    >
                      <span className="shrink-0 mt-0.5">
                        <FaCheck className="w-3 h-3" />
                      </span>
                      <span className="flex-1">Breakfast Included</span>
                    </p>
                  </div>
                )}

                <div className="mt-auto pt-3 border-t border-gray-100">
                  <div className="flex items-end justify-between">
                    <div className="flex flex-col">
                      <span className="text-xs font-medium text-gray-500 mb-1">
                        Total Stay Price:
                      </span>
                      <div
                        className={cn(
                          "font-bold text-lg",
                          selectedIndex === index
                            ? "text-[#065F46]"
                            : "text-gray-900"
                        )}
                      >
                        {room.currency} {rate.pricing.final_price}
                      </div>
                    </div>
                    <span className="text-xs text-gray-400">Taxes Incl.</span>
                  </div>
                </div>
              </div>
            );
          })}
        </div>

        <div className="shrink-0 p-4 border-t bg-white sticky bottom-0">
          <button
            onClick={() => router.push(`/hotels/${hotel.id}/booking`)}
            className="bg-[#2D2A7B] w-full h-11 rounded-2xl flex items-center justify-center font-bold text-white hover:bg-[#1e1b55] transition"
          >
            Proceed to Booking
          </button>

          <p className="text-xs text-core text-center mt-2">
            You won&apos;t be charged yet
          </p>
        </div>
      </div>
    </>
  );
};

const RoomSelectionModalDetails = ({ room }: { room: Room }) => {
  const [isOpen, setIsOpen] = useState(false);
  const [isMobile, setIsMobile] = useState<boolean | null>(null);

  console.log("RoomSelectionModalDetails Rendered", { isOpen, room });

  useEffect(() => {
    const handleResize = () => {
      setIsMobile(window.innerWidth < 768); // Tailwind's `md` breakpoint
    };

    handleResize(); // run once on mount

    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  useEffect(() => {
    const scrollBarWidth =
      window.innerWidth - document.documentElement.clientWidth;

    if (isOpen) {
      document.body.classList.add("overflow-hidden");
      document.body.style.paddingRight = `${scrollBarWidth}px`;
    } else {
      document.body.classList.remove("overflow-hidden");
      document.body.style.paddingRight = "";
    }

    return () => {
      document.body.classList.remove("overflow-hidden");
      document.body.style.paddingRight = "";
    };
  }, [isOpen]);

  const onClose = () => {
    setIsOpen(false);
  };
  return (
    <div onClick={(e) => e.stopPropagation()}>
      <h3
        className="text-sm md:text-base font-bold text-[#3E51CD] cursor-pointer"
        onClick={(e) => {
          e.stopPropagation();
          setIsOpen(true);
        }}
      >
        {room.name}
      </h3>

      {isMobile ? (
        <>
          <div
            className={cn(
              "fixed inset-0 bg-core/50 z-40 transition-opacity duration-300 ease-in-out",
              isOpen ? "opacity-100" : "opacity-0 pointer-events-none"
            )}
            onClick={onClose}
          />

          <div
            className={cn(
              "fixed bottom-0 left-0 right-0 z-50 bg-white h-[calc(100vh-10rem)] rounded-t-3xl flex flex-col transition-transform duration-500 ease-in-out",
              isOpen ? "translate-y-0" : "translate-y-full"
            )}
            style={{ paddingBottom: "env(safe-area-inset-bottom)" }}
          >
            <div className="flex items-center justify-between gap-2 p-4 border-b">
              <h2 className="text-sm md:text-base text-core font-bold">
                {room.name}
              </h2>

              <button onClick={onClose} className="ml-auto">
                <IoClose className="text-xl" />
              </button>
            </div>

            <div className="flex-1 flex flex-col overflow-y-auto p-8">
              <div>
                <RoomDetailsCarousel room={room} />
              </div>
              {/* <div className="block mb-2">
                <h3 className="font-semibold text-[20px] mb-3">Highlights</h3>
                <Carousel
                  opts={{
                    align: "start",
                    dragThreshold: 10,
                    skipSnaps: false,
                  }}
                >
                  <CarouselContent className="overflow-visible -ml-2.5">
                    {features.map((feature, i) => (
                      <CarouselItem key={i} className={`basis-11/12 pl-2.5`}>
                        <div className="flex items-center gap-3 text-sm w-full px-[19px] py-3.5 border border-[#E6E8F2] rounded-[20px]">
                          <div className="w-8 h-8 shrink-0">
                            <Image
                              alt="icon"
                              src={feature.icon}
                              className="w-full h-full object-contain"
                            />
                          </div>
                          <span
                            className="text-xs whitespace-normal"
                            title={feature.label}
                          >
                            {feature.label}
                          </span>
                        </div>
                      </CarouselItem>
                    ))}
                  </CarouselContent>
                </Carousel>
              </div> */}
              {/* <div className="bg-[#FAFAFC] mb-2 rounded-md">
                <h3 className="font-medium text-sm mb-2">Highlights</h3>
                <div className="flex items-center justify-between">
                  <Image
                    alt="No smoking"
                    src={noSmokingIcon}
                    className="size-14 object-contain"
                  />
                  <Image
                    alt="No smoking"
                    src={cityView}
                    className="size-14 object-contain"
                  />
                  <Image
                    alt="No smoking"
                    src={noPetAllowed}
                    className="size-14 object-contain"
                  />
                </div>
              </div> */}

              <div className="bg-[#FAFAFC] py-4 px-3 rounded-md mb-2 flex flex-col gap-3">
                {room.roomSizeSqm && (
                  <div className="flex items-center text-xs">
                    <div className="shrink-0 min-w-[100px] xl:min-w-[120px] flex items-center gap-2">
                      <Image
                        alt="Arrow Both Sharp"
                        src={arrowBoth}
                        className="w-4 h-4"
                      />
                      <h3>Room Size</h3>
                    </div>
                    <p className="font-medium">{room.roomSizeSqm || 27} m²</p>
                  </div>
                )}

                {room.roomFit && (
                  <div className="flex items-center text-xs">
                    <div className="shrink-0 min-w-[100px] xl:min-w-[120px] flex items-center gap-2">
                      <Image
                        alt="icon"
                        src={parentChild}
                        className="w-4 h-auto"
                      />
                      <h3>Room Fit</h3>
                    </div>
                    <p className="font-medium">{room.roomFit}</p>
                  </div>
                )}

                {room.bedType && (
                  <div className="flex items-center text-xs">
                    <div className="shrink-0 min-w-[100px] xl:min-w-[120px] flex items-center gap-2">
                      <Image alt="icon" src={bedIcon} className="w-4 h-auto" />
                      <h3>Bedding</h3>
                    </div>
                    <div className="flex flex-col">
                      <p className="font-medium">{room.bedType}</p>
                      <p className="text-[#065F46]">
                        Bed type: Subject to availability
                      </p>
                    </div>
                  </div>
                )}
              </div>

              <div className="group bg-[#FAFAFC] hover:bg-[#F0F0F4] p-2 rounded-md text-core mb-2">
                <h3 className="font-medium text-sm mb-2">Description</h3>
                <p className="text-xs mb-2 line-clamp-2">{room.description}</p>
                {/* <span className="font-medium text-xs group-hover:underline">
                  read more
                </span> */}
              </div>

              {room.facilities.length > 0 && (
                <div className="group bg-[#FAFAFC] hover:bg-[#F0F0F4] p-2 rounded-md text-core">
                  <h3 className="font-medium text-sm mb-2">Facilities</h3>
                  {(room.facilities || []).map((facility, i) => (
                    <p key={i} className="text-xs">
                      {facility || "No Facility"}
                    </p>
                  ))}
                  {/* <span className="font-medium text-xs group-hover:underline">
                  show more facilities
                </span> */}
                </div>
              )}
            </div>
          </div>
        </>
      ) : (
        <Modal
          isOpen={isOpen}
          onClose={() => setIsOpen(false)}
          styleHeader="border-none px-6"
          title={<h2 className="font-bold text-sm">{room.name}</h2>}
          className="rounded-[20px] w-md"
        >
          <div className="flex-col md:p-4">
            <RoomDetailsCarousel room={room} />

            {/* <div className="block mb-2">
              <h3 className="font-semibold text-[20px] mb-3">Highlights</h3>
              <Carousel
                opts={{
                  align: "start",
                  dragThreshold: 10,
                  skipSnaps: false,
                }}
              >
                <CarouselContent className="overflow-visible -ml-2.5">
                  {features.map((feature, i) => (
                    <CarouselItem key={i} className={`basis-11/12 pl-2.5`}>
                      <div className="flex items-center gap-3 text-sm w-full px-[19px] py-3.5 border border-[#E6E8F2] rounded-[20px]">
                        <div className="w-8 h-8 shrink-0">
                          <Image
                            alt="icon"
                            src={feature.icon}
                            className="w-full h-full object-contain"
                          />
                        </div>
                        <span
                          className="text-xs whitespace-normal"
                          title={feature.label}
                        >
                          {feature.label}
                        </span>
                      </div>
                    </CarouselItem>
                  ))}
                </CarouselContent>
              </Carousel>
            </div>

            {/* <div className="bg-[#FAFAFC] mb-2 p-2 rounded-md">
              <h3 className="font-medium text-sm mb-2">Highlights</h3>
              <div className="flex items-center justify-between">
                <Image
                  alt="No smoking"
                  src={noSmokingIcon}
                  className="size-14 object-contain"
                />
                <Image
                  alt="No smoking"
                  src={cityView}
                  className="size-14 object-contain"
                />
                <Image
                  alt="No smoking"
                  src={noPetAllowed}
                  className="size-14 object-contain"
                />
              </div>
            </div> */}

            <div className="bg-[#FAFAFC] py-4 px-3 rounded-md mb-2 flex flex-col gap-3">
              {room.roomSizeSqm && (
                <div className="flex items-center text-xs">
                  <div className="shrink-0 min-w-[100px] xl:min-w-[120px] flex items-center gap-2">
                    <Image
                      alt="Arrow Both Sharp"
                      src={arrowBoth}
                      className="w-4 h-4"
                    />
                    <h3>Room Size</h3>
                  </div>
                  <p className="font-medium">{room.roomSizeSqm || 27} m²</p>
                </div>
              )}

              {room.roomFit && (
                <div className="flex items-center text-xs">
                  <div className="shrink-0 min-w-[100px] xl:min-w-[120px] flex items-center gap-2">
                    <Image
                      alt="icon"
                      src={parentChild}
                      className="w-4 h-auto"
                    />
                    <h3>Room Fit</h3>
                  </div>
                  <p className="font-medium">{room.roomFit}</p>
                </div>
              )}

              {room.bedType && (
                <div className="flex  items-start text-xs">
                  <div className="shrink-0 min-w-[100px] xl:min-w-[120px] flex items-center gap-2">
                    <Image alt="icon" src={bedIcon} className="w-4 h-4" />
                    <h3>Bedding</h3>
                  </div>
                  <div className="flex flex-col">
                    <p className="font-medium">{room.bedType}</p>
                    <p className="text-[#065F46]">
                      Bed type: Subject to availability
                    </p>
                  </div>
                </div>
              )}
            </div>

            <div className="group bg-[#FAFAFC] hover:bg-[#F0F0F4] p-2 rounded-md text-core mb-2">
              <h3 className="font-medium text-sm mb-2">Description</h3>
              <p className="text-xs mb-2 line-clamp-2">{room.description}</p>
              {/* <span className="font-medium text-xs group-hover:underline">
                read more
              </span> */}
            </div>

            {room.facilities.length > 0 && (
              <div className="group bg-[#FAFAFC] hover:bg-[#F0F0F4] p-2 rounded-md text-core">
                <h3 className="font-medium text-sm mb-2">Facilities</h3>
                {(room.facilities || []).map((facility, i) => (
                  <p key={i} className="text-xs">
                    {facility || "No Facility"}
                  </p>
                ))}
                {/* <span className="font-medium text-xs group-hover:underline">
                show more facilities
              </span> */}
              </div>
            )}
          </div>
        </Modal>
      )}
    </div>
  );
};

const RoomDetailsCarousel = ({ room }: { room: Room }) => {
  const [api, setApi] = useState<CarouselApi>();
  const [current, setCurrent] = useState(0);
  const [count, setCount] = useState(0);

  useEffect(() => {
    if (!api) return;

    // eslint-disable-next-line react-hooks/set-state-in-effect
    setCount(api.scrollSnapList().length);
    setCurrent(api.selectedScrollSnap() + 1);

    api.on("select", () => setCurrent(api.selectedScrollSnap() + 1));
  }, [api]);

  return (
    <Carousel
      setApi={setApi}
      className="-mx-4 -mt-4 w-[calc(100%+2rem)] h-[250px] md:h-[350px] overflow-hidden rounded-[20px] relative"
    >
      <CarouselContent className="h-full">
        {(room.images.length > 0
          ? room.images
          : [roomAndBeds, hotelSearch7, hotelSearch4]
        ).map((img, index) => (
          <CarouselItem key={index} className="h-full">
            <div className="w-full h-[230px] md:h-[300px] overflow-hidden rounded-[20px]">
              <Image
                alt="Room and beds"
                src={img}
                className="w-full h-full object-cover"
                width={500}
                height={300}
              />
            </div>
          </CarouselItem>
        ))}
      </CarouselContent>

      <div className="absolute bottom-8 left-1/2 -translate-x-1/2 sm:bottom-4 md:bottom-15 lg:bottom-20">
        <div className="bg-core h-5 w-12 rounded-[20px] flex items-center justify-center gap-1">
          {Array.from({ length: count }).map((_, idx) => (
            <button
              key={idx}
              className={cn(
                "rounded-full size-2 transition-colors",
                idx === current - 1 ? "bg-white" : "bg-[#C9C8D7]"
              )}
              onClick={() => api?.scrollTo(idx)}
            />
          ))}
        </div>
      </div>
    </Carousel>
  );
};

export default RoomSelectionCardRow;
