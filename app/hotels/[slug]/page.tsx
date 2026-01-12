"use client";
import Image from "next/image";
import { useState } from "react";
import {
  IoIosHeart,
  IoIosStar,
  IoIosStarHalf,
  IoMdShare,
} from "react-icons/io";
import BookingSearchForm from "@/components/shared/booking-search-form";
import UserReviewsPanel from "@/components/features/hotel-details/UserReviewsPanel";
import RoomSelectionSection from "@/components/features/hotel-details/RoomSelectionSection";
import accessibilityIcon from "@/assets/images/accessibility-icon.png";
import lotusIcon from "@/assets/images/lotus-icon.png";
import parkingIcon from "@/assets/images/parking-icon.png";
import noSmokingIcon from "@/assets/images/no-smoking-icon.png";
import wifiIcon from "@/assets/images/wifi-icon.png";
// import carouselSliderIcon from "@/assets/images/carousel-slider-icon.svg";
// import personPlaceholder from "@/assets/images/person-placeholder.png";
import americanExpressLogo from "@/assets/images/american-express-logo.png";
import visaLogo from "@/assets/images/visa-logo.png";
import mastercardLogo from "@/assets/images/mastercard-logo.png";
import hotelSearch1 from "@/assets/images/hotel-search1.png";
// import {
//   Carousel,
//   CarouselContent,
//   CarouselItem,
// } from "@/components/shared/CardCarousel";
import { useParams } from "next/navigation";
import { useHotelRooms } from "@/app/search/hooks/useHotels";
import ProfileLoader from "@/app/profile/loading";
import { HotelInfo } from "@/app/search/types";
import { useSearch } from "@/app/search/context/SearchContext";
import viewLargerMapIcon from "@/assets/images/vew-large-map-icon.svg";
import messageIcon from "@/assets/images/message-icon.svg";

export const features = [
  { icon: noSmokingIcon, label: "Non-smoking Rooms" },
  { icon: accessibilityIcon, label: "Accessible Facilities" },
  { icon: parkingIcon, label: "Free Parking" },
  { icon: lotusIcon, label: "Spa" },
  { icon: wifiIcon, label: "Free Wi-Fi" },
];

export type SearchData = {
  checkIn: string;
  checkOut: string;
  adults: number;
  children: number;
};

export default function Page() {
  const [openUserReviews, setOpenUserReviews] = useState<boolean>(false);
  const [descExpanded, setDescExpanded] = useState<boolean>(false);
  const { searchData } = useSearch();

  const params = useParams();
  const id = Number(params.slug);

  // Initialize with default values immediately
  const defaultSearchData: SearchData = {
    checkIn: new Date().toISOString().split("T")[0],
    // eslint-disable-next-line react-hooks/purity
    checkOut: new Date(Date.now() + 86400000).toISOString().split("T")[0],
    adults: 2,
    children: 0,
  };

  // Use search context data if available, otherwise use defaults
  const searchDataState: SearchData = {
    checkIn: searchData?.checkIn || defaultSearchData.checkIn,
    checkOut: searchData?.checkOut || defaultSearchData.checkOut,
    adults: searchData?.guests?.adults || defaultSearchData.adults,
    children: searchData?.guests?.children || defaultSearchData.children,
  };

  console.log("Hotel ID from params:", id, searchDataState);

  const getHotelsRooms = useHotelRooms(id, searchDataState);
  console.log("getHotelsRooms:", id, getHotelsRooms.data);
  const hotelDetails = getHotelsRooms.data?.hotel;
  const rooms = getHotelsRooms.data?.rooms;
  const hotelFirstImage = hotelDetails?.images?.[0];

  console.log("Hotel Rooms Data:", getHotelsRooms.data);

  if (getHotelsRooms.isLoading) {
    return <ProfileLoader title="hotel details..." />;
  }

  return (
    <>
      <section className="-mt-px sticky top-0 z-50">
        <div className="absolute inset-0 bg-[#F5F6FA]/80 backdrop-blur-2xl"></div>
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <div className="max-w-3xl mx-auto py-2 sm:py-0">
            <BookingSearchForm
              isOtherPageSearch={true}
              useMobile={true}
              buttonText="Update search"
              formClassName="bg-transparent sm:rounded-none hover:bg-[#F3E9FF]"
            />
          </div>
        </div>
      </section>

      <section className="mt-5">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <div className="flex flex-col lg:flex-row gap-2.5">
            <div className="w-full aspect-video md:h-80 lg:h-auto lg:flex-1 rounded-[20px] overflow-hidden bg-[#22214180] relative">
              <Image
                alt="Hotel details"
                src={hotelFirstImage || hotelSearch1}
                className="object-cover"
                fill
                sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw"
              />
              {/* <button className="absolute bottom-4 right-4 bg-white text-core text-xs font-medium rounded-full p-2 md:p-3 shadow hover:bg-gray-100 flex items-center justify-center gap-2">
                <div className="size-5">
                  <Image
                    alt="icon"
                    src={carouselSliderIcon}
                    className="size-full"
                  />
                </div>

                <span>View all 36 photos</span>
              </button> */}
            </div>

            <div className="w-full lg:w-sm xl:w-full lg:max-w-xl grid grid-cols-4 md:grid-cols-3 lg:grid-cols-2 gap-2 rounded-[20px] overflow-hidden">
              {[...(hotelDetails?.images || []), hotelSearch1]
                .flat()
                .map((img, i) => (
                  <div
                    key={i}
                    className={`bg-[#22214180] rounded-md w-full aspect-square relative ${
                      i >= 4 ? "hidden md:block lg:hidden" : ""
                    }`}
                  >
                    <Image
                      alt="Hotel details"
                      src={img}
                      className="object-cover"
                      fill
                      sizes="(max-width: 768px) 50vw, (max-width: 1024px) 33vw, 25vw"
                    />
                  </div>
                ))}
            </div>
          </div>
          {/* <div className="w-full flex flex-col py-4">
            <div className="hidden sm:block py-2">
              <div className="flex items-center gap-3">
                {features.map((feature, i) => (
                  <div
                    key={i}
                    className="flex items-center gap-3 text-sm px-[19px] py-3.5 border border-[#E6E8F2] rounded-[20px] flex-1 min-w-0"
                  >
                    <div className="w-8 h-8 shrink-0">
                      <Image
                        alt="icon"
                        src={feature.icon}
                        className="w-full h-full object-contain"
                      />
                    </div>
                    <span className="text-xs truncate" title={feature.label}>
                      {feature.label}
                    </span>
                  </div>
                ))}
              </div>
            </div>
          </div> */}
          <div className="flex flex-col lg:flex-row w-full gap-2.5 mt-4">
            <div className="flex-1 flex flex-col w-full text-core">
              <div className="flex flex-col sm:flex-row gap-8 sm:gap-0 justify-between mb-4">
                <div>
                  <h2 className="flex text-xl font-bold">
                    {hotelDetails?.name || "Hotel Name"}
                  </h2>
                  <div className="flex">
                    {Array.from({ length: 4 }).map((_, i) => (
                      <IoIosStar key={i} className="w-4 h-4" />
                    ))}
                    <IoIosStarHalf className="w-4 h-4" />
                  </div>
                  {hotelDetails?.address &&
                    hotelDetails?.address !== ", , " && (
                      <p className="font-normal text-sm mt-3">
                        {hotelDetails?.address}
                      </p>
                    )}

                  {/* <div className="w-full flex flex-wrap items-center gap-3 mt-6 text-xs">
                    <div className="flex items-center gap-2 border border-[#A48B05] bg-[#F6F2DA] rounded-full px-2 py-0.5">
                      <div className="bg-[#FFD700] border border-[#A48B05] w-2.5 h-2.5 rounded-full" />
                      <span className="font-normal">Overall Rating 4.53</span>
                    </div>
                    <span>487 reviews</span>

                    <p
                      className="underline cursor-pointer font-medium text-sm"
                      onClick={() => setOpenUserReviews(true)}
                    >
                      see reviews
                    </p>
                  </div> */}
                </div>

                <div className="flex gap-2">
                  <button
                    onClick={() => {
                      const section =
                        document.getElementById("room-selections");
                      if (section) {
                        // prefer to scroll with an offset so the sticky search bar
                        // doesn't cover the heading. Find the sticky search
                        // section (if present) and use its height as an offset.
                        const sticky = document.querySelector("section.sticky");
                        const stickyHeight = sticky
                          ? (sticky as HTMLElement).getBoundingClientRect()
                              .height
                          : 0;
                        // additional small gap
                        const gap = 12;
                        const top =
                          window.scrollY +
                          section.getBoundingClientRect().top -
                          stickyHeight -
                          gap;
                        window.scrollTo({ top, behavior: "smooth" });
                      }
                    }}
                    className="bg-core text-sm md:text-base h-10 md:h-[55px] w-[150px] md:py-3 md:px-[35px] rounded-[20px] text-white"
                  >
                    Reserve
                  </button>
                  <button className="sm:py-2 px-3.5 h-10 sm:h-[55px] shadow-md rounded-[22px] border-l border-t border-[#E6E8F2] flex items-center justify-center">
                    <IoIosHeart className="w-5 h-5" />
                  </button>
                  <button className="sm:py-2 px-3.5 h-10 sm:h-[55px] shadow-md rounded-[22px] border-l border-t border-[#E6E8F2] flex items-center justify-center">
                    <IoMdShare className="w-5 h-5" />
                  </button>
                </div>
              </div>
              <div className="flex flex-col gap-2.5">
                <div className="flex-1 p-4 text-core group rounded-[20px] bg-[#FAFAFC] hover:bg-[#F0F0F4] space-y-3 max-h-[220px] md:max-h-[280px] overflow-y-auto">
                  {hotelDetails?.description && (
                    <h2 className="font-semibold text-base">Description</h2>
                  )}
                  {hotelDetails?.description ? (
                    <>
                      <p
                        className={`text-sm tracking-tight wrap-break-word whitespace-pre-wrap ${
                          descExpanded ? "" : "line-clamp-3"
                        }`}
                      >
                        {hotelDetails?.description}
                      </p>
                      <button
                        type="button"
                        onClick={() => setDescExpanded((s) => !s)}
                        className="text-sm font-medium hover:underline cursor-pointer"
                        aria-expanded={descExpanded}
                      >
                        {descExpanded ? "Read less" : "Read more"}
                      </button>
                    </>
                  ) : (
                    <div className="flex items-center gap-2 p-2">
                      <Image
                        alt="icon"
                        src={messageIcon}
                        className="size-5 object-contain"
                        width={24}
                        height={27}
                      />
                      <p className="text-[17px] text-core font-semibold">
                        No Description Available
                      </p>
                    </div>
                  )}
                </div>
              </div>
            </div>
            {/* <div className="block sm:hidden py-2">
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

            <div className="w-full sm:w-auto lg:w-sm xl:w-[274px] lg:max-w-xl rounded-[20px] overflow-hidden border">
              <div className="w-full h-full flex flex-col">
                <div className="w-full h-[300px] sm:h-[350px] md:h-[400px] lg:h-[243px] rounded-t-[20px] overflow-hidden">
                  <iframe
                    title="hotel location"
                    width="100%"
                    height="100%"
                    className="border-0"
                    src={`https://www.google.com/maps?q=${hotelDetails?.latitude},${hotelDetails?.longitude}&z=15&output=embed`}
                    allowFullScreen
                    loading="lazy"
                  />
                </div>
                <div className="p-3">
                  <button
                    onClick={() => {
                      window.open(
                        `https://www.google.com/maps?q=${hotelDetails?.latitude},${hotelDetails?.longitude}&z=15`,
                        "_blank",
                        "noopener,noreferrer"
                      );
                    }}
                    className="w-full py-2 text-sm font-medium text-core bg-white hover:bg-gray-50 transition-colors rounded-[20px] border border-core"
                  >
                    <Image
                      alt="icon"
                      src={viewLargerMapIcon}
                      width={30}
                      height={30}
                      className="inline-block mr-2 size-8 object-contain"
                    />
                    View Larger Map
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <RoomSelectionSection
        rooms={rooms ?? []}
        hotel={hotelDetails as HotelInfo}
      />

      {/* <section className="mt-10">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <h2 className="font-bold text-core mb-6">Reviews</h2>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            {Array.from({ length: 3 }).map((_, i) => (
              <div
                key={i}
                className="rounded-[30px] text-core bg-[#FAFAFC] p-6"
              >
                <div className="flex items-center gap-4">
                  <div className="size-10 rounded-full bg-gray-300 overflow-hidden flex items-center justify-center">
                    <Image
                      alt="person"
                      src={personPlaceholder}
                      className="size-5 object-contain"
                    />
                  </div>
                  <div>
                    <h3 className="text-xs">Jamal Chatila</h3>
                    <span className="text-[10px]">Spain 08/07/2022</span>
                  </div>
                </div>
                <p className="text-xs mt-4">
                  Had a great stay in Raddison Blu! My wife and I were had
                  annoying neighbors but our sleep was saved by the soundproof
                  walls! You don’t get to find this option in properties very
                  often nowadays. Breakfast was amazing too! I would also like
                  to thank...
                </p>
              </div>
            ))}
          </div>

          <button
            onClick={() => setOpenUserReviews(true)}
            className="text-core rounded-full py-2.5 px-5 border border-core hover:bg-gray-100 mt-6 cursor-pointer"
          >
            <span>See all reviews</span>
          </button>
        </div>
      </section> */}

      {/* <section className="mt-10">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <div className="py-10 border-t border-b text-core text-sm space-y-4">
            <h2 className="font-bold text-base">Fine Print</h2>
            <p>
              Guests are required to show a photo identification and credit card
              upon check-in. Please note that all Special Requests are subject
              to availability and additional charges may apply. In response to
              Coronavirus (COVID-19), additional safety and sanitation measures
              are in effect at this property.
            </p>

            <p>
              Food & beverage services at this property may be limited or
              unavailable due to Coronavirus (COVID-19). Due to Coronavirus
              (COVID-19), this property is taking steps to help protect the
              safety of guests and staff. Certain services and amenities may be
              reduced or unavailable as a result.
            </p>

            <p>
              In accordance with government guidelines to minimize transmission
              of the Coronavirus (COVID-19), this property may request
              additional documentation from guests to validate identity, travel
              itinerary and other relevant information, during dates where such
              guidelines exist.
            </p>
          </div>
        </div>
      </section> */}

      {hotelDetails?.houseRules && (
        <section className="mt-10">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
            <h2 className="text-base font-bold mb-6">House Rules</h2>

            <div className="flex flex-col gap-10 text-sm">
              <div className="flex flex-col sm:flex-row gap-4">
                <label className="w-56 font-medium">Check In</label>
                <div>
                  <span>From 15:00 to 23:30</span>
                  <p className="text-[#6562C4]">
                    Guests must show an identification and credit card upon
                    check-in.
                  </p>
                </div>
              </div>

              <div className="flex flex-col sm:flex-row gap-4">
                <label className="w-56 font-medium">Check Out</label>
                <span>From 07:30 to 12:00</span>
              </div>

              <div className="flex flex-col sm:flex-row gap-4">
                <label className="min-w-56 font-medium">
                  Cancellation/Prepayment Policy
                </label>
                <div>
                  <p>
                    Cancellation and Prepayment policies vary according to
                    accommodation type.
                  </p>
                  <p>
                    Check the packages to see whether you chose has a
                    cancellation/prepayment policy or not.
                  </p>
                </div>
              </div>

              <div className="flex flex-col sm:flex-row gap-4">
                <label className="min-w-56 font-medium">
                  Children and Beds
                </label>
                <div>
                  <p>{hotelDetails.houseRules.childrenAndBeds.summary || ""}</p>

                  <div className="flex flex-col px-6 mt-4">
                    {/* <div className="flex flex-col gap-2">
                      <label className="font-semibold">0 to 6 years</label>
                      <div className="flex">
                        <span className="w-50">Extra Bed Upon Request</span>
                        <span>US$50 per night</span>
                      </div>
                      <div className="flex">
                        <span className="w-50">Cot Upon Request</span>
                        <span className="font-bold text-[#2E8929]">Free</span>
                      </div>
                    </div>

                    <div className="flex flex-col gap-2 mt-3">
                      <label className="font-semibold">7+ years</label>
                      <div className="flex">
                        <span className="w-50">Extra Bed Upon Request</span>
                        <span>US$50 per night</span>
                      </div>
                    </div> */}
                    <p className="mt-4">
                      {hotelDetails.houseRules.childrenAndBeds.note || ""}
                      {/* Cots/Extra Bed are{" "}
                      <span className="underline font-bold">NOT</span> included
                      in the total price and must be paid separately during your
                      stay. */}
                    </p>
                  </div>
                </div>
              </div>

              <div className="flex flex-col sm:flex-row gap-4">
                <label className="min-w-56 font-medium">Age Restrictions</label>
                <p>
                  {hotelDetails.houseRules.ageRestrictions ||
                    "There are no age restrictions."}
                </p>
              </div>

              <div className="flex flex-col sm:flex-row gap-4">
                <label className="min-w-56 font-medium">Pets</label>
                <p>{hotelDetails.houseRules.pets || "."}</p>
              </div>

              <div className="flex flex-col sm:flex-row gap-4">
                <label className="min-w-56 font-medium">Payment Methods</label>
                <div className="flex gap-2.5 items-center">
                  <div className="size-10">
                    <Image
                      alt="payment"
                      src={mastercardLogo}
                      className="size-full lg:size-auto object-contain"
                    />
                  </div>
                  <div className="size-10">
                    <Image
                      alt="payment"
                      src={visaLogo}
                      className="size-full lg:size-auto object-contain"
                    />
                  </div>
                  <div className="size-10">
                    <Image
                      alt="payment"
                      src={americanExpressLogo}
                      className="size-full lg:size-auto object-contain"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      )}

      <UserReviewsPanel
        isOpen={openUserReviews}
        onClose={() => setOpenUserReviews(false)}
        reviews={[]}
      />

      {/* <ViewRecommendation /> */}

      <div className="py-20"></div>
    </>
  );
}
