"use client";

import { useState, useEffect, useMemo } from "react";
import * as Slider from "@radix-ui/react-slider";
import Link from "next/link";
import Image from "next/image";
import BookingSearchForm from "@/components/shared/booking-search-form";
import { Tabs, TabsContent } from "@/components/ui/tabs";
import { Checkbox } from "@/components/ui/checkbox";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
// import priceRangeSvg from "@/assets/images/price-range.svg";
// import mapsIcon from "@/assets/images/maps-icon.svg";
// import filterIcon from "@/assets/images/filters-icon.png";
// import updownIcon from "@/assets/images/updown-icon.svg";
import fourSeasongsHotel from "@/assets/images/fourseasons-hotel.png";
import arrowDownIcon from "@/assets/images/arrow-down-icon.svg";
import sortIcon from "@/assets/images/sort-icon.svg";
import upDownIcon from "@/assets/images/up-down-arrow-icon.svg";
import rightIcon from "@/assets/images/right-arrow-icon.svg";
import heartIcon from "@/assets/images/heart-icon.svg";
import starIcon from "@/assets/images/start-icon.svg";
// import { ExclusiveOffersCarousel } from "./components/ExclusiveOffersCarousel";
import { useAvailabilitySearch } from "@/components/shared/booking-search-form/hooks/useHotels";
import ProfileLoader from "../profile/loading";
import HotelErrorPage from "./HotelErrorPage";
import { FaCheck } from "react-icons/fa";
import { SearchParams } from "./types";
import { useSearch } from "./context/SearchContext";
import HotelRatingRow from "./components/HotelRatingRow";

const mockHotels = [
  {
    code: 10234,
    name: "Azure Bay Resort & Spa",
    countryName: "Maldives",
    cityName: "Male",
    countryCode: "MV",
    destinationCode: "MLE",
    destinationName: "North Malé Atoll",
    description:
      "A stunning overwater villa experience with private reef access and world-class dining.",
    longitude: "73.5271",
    latitude: "4.1755",
    city: "Male",
    destination: "Maldives Islands",
    location: "North Malé Atoll, Maldives",
    category: "5 Star",
    rating: 4.8,
    lowestRate: "450.00",
    highestRate: "1200.00",
    currency: "USD",
    board: "All Inclusive",
    isRefundable: true,
    images: [],
    taxes: [],
    recommended: true,
    totalReviews: 0,
    noPrepaymentNeeded: true,
    freeCancellation: true,
    roomsLeftLabel: "Only 2 rooms left!",
    nights: 5,
    adults: 2,
    children: 0,
  },
  {
    code: 55890,
    name: "The Grand Canal Suites",
    countryName: "Netherlands",
    cityName: "Amsterdam",
    countryCode: "NL",
    destinationCode: "AMS",
    destinationName: "Amsterdam City Center",
    description:
      "Boutique stay overlooking the historic canals, walking distance to Anne Frank House.",
    longitude: "4.8951",
    latitude: "52.3702",
    city: "Amsterdam",
    destination: "North Holland",
    location: "Prinsengracht, Amsterdam",
    category: "4 Star",
    rating: 4.2,
    lowestRate: "185.50",
    highestRate: "310.00",
    currency: "EUR",
    board: "Bed and Breakfast",
    isRefundable: false,
    images: [],
    taxes: [],
    recommended: false,
    totalReviews: 1240,
    noPrepaymentNeeded: false,
    freeCancellation: false,
    roomsLeftLabel: "",
    nights: 2,
    adults: 2,
    children: 1,
  },
  {
    code: 88211,
    name: "Alpine Lodge & Suites",
    countryName: "Switzerland",
    cityName: "Zermatt",
    countryCode: "CH",
    destinationCode: "ZER",
    destinationName: "Swiss Alps",
    description:
      "Cozy family-run lodge with breathtaking views of the Matterhorn.",
    longitude: "7.7491",
    latitude: "46.0207",
    city: "Zermatt",
    destination: "Valais",
    location: "Winkelmatten, Zermatt",
    category: "3 Star",
    rating: 4.5,
    lowestRate: "120.00",
    highestRate: "200.00",
    currency: "CHF",
    board: "Room Only",
    isRefundable: true,
    images: [],
    taxes: [],
    recommended: true,
    totalReviews: 312,
    noPrepaymentNeeded: true,
    freeCancellation: true,
    roomsLeftLabel: "Available",
    nights: 3,
    adults: 2,
    children: 2,
  },
];

const ratingOptions = ["1 Star", "2 Stars", "3 Stars", "4 Stars", "5 Stars"];
const currencyOptions = ["USD", "EUR", "GBP", "AED"];
const priceSortOptions = [
  { value: "high-low", label: "Price: High - Low" },
  { value: "low-high", label: "Price: Low - High" },
];

const defaultSearchData: SearchParams = {
  destination: "",
  checkIn: "2024-10-10",
  checkOut: "2024-10-15",
  guests: {
    adults: 2,
    children: 0,
  },
};

interface FilterState {
  priceRange: [number, number];
  bedrooms: number;
  bathrooms: number;
  propertyTypes: string[];
  facilities: string[];
  brands: string[];
  propertyRatings: string[];
  bedPreferences: string[];
  roomAccessibility: string[];
  propertyAccessibility: string[];
  filters: string[];
}

export default function Page() {
  const { searchData } = useSearch();

  const [searchDataState, setSearchDataState] =
    useState<SearchParams>(defaultSearchData);

  useEffect(() => {
    if (typeof window === "undefined") return;
    try {
      setSearchDataState(searchData ? searchData : defaultSearchData);
    } catch {
      setSearchDataState(defaultSearchData);
    }
  }, [searchData]);

  const hotelSearch = useAvailabilitySearch(
    searchDataState ?? {},
    searchDataState.destination ? true : false
  );
  // eslint-disable-next-line react-hooks/exhaustive-deps
  const allHotels = hotelSearch.data || mockHotels || [];

  // Calculate min and max prices from actual hotel data
  const { minPrice, maxPrice } = useMemo(() => {
    if (allHotels.length === 0) return { minPrice: 0, maxPrice: 1000 };

    const prices = allHotels.map((hotel) => parseInt(hotel.lowestRate) || 0);
    const min = Math.floor(Math.min(...prices));
    const max = Math.ceil(Math.max(...prices));

    return { minPrice: min, maxPrice: max };
  }, [allHotels]);

  const [value, setValue] = useState<[number, number]>([minPrice, maxPrice]);
  const [filters, setFilters] = useState<FilterState>({
    priceRange: [minPrice, maxPrice],
    bedrooms: 1,
    bathrooms: 1,
    propertyTypes: [],
    facilities: [],
    brands: [],
    propertyRatings: [],
    bedPreferences: [],
    roomAccessibility: [],
    propertyAccessibility: [],
    filters: [],
  });

  const [currency, setCurrency] = useState("USD");
  const [priceSort, setPriceSort] = useState<"low-high" | "high-low">(
    "high-low"
  );

  // Update slider and filters when price range changes
  useEffect(() => {
    setValue([minPrice, maxPrice]);
    setFilters((prev) => ({
      ...prev,
      priceRange: [minPrice, maxPrice],
    }));
  }, [minPrice, maxPrice]);

  // Update filter state when slider or counter changes
  useEffect(() => {
    setFilters((prev) => ({
      ...prev,
      priceRange: [value[0], value[1]],
    }));
  }, [value]);

  console.log("All Hotels:", allHotels);

  // Apply filters to hotels
  const filteredHotels = allHotels.filter((hotel) => {
    // Price filter
    const hotelPrice = parseInt(hotel.lowestRate);
    if (
      hotelPrice < filters.priceRange[0] ||
      hotelPrice > filters.priceRange[1]
    ) {
      return false;
    }

    // Property type filter
    if (filters.propertyTypes.length > 0) {
      const hotelCategory = hotel.category?.toLowerCase() || "";
      const matchesType = filters.propertyTypes.some((type) =>
        hotelCategory.includes(type.toLowerCase())
      );
      if (!matchesType) return false;
    }

    // Facilities filter
    if (filters.facilities.length > 0) {
      // Check if hotel has any of the selected facilities
      // This assumes hotel data structure includes facilities info
      // For now, we'll do basic matching
      const hotelDescription = (hotel.description || "").toLowerCase();
      const matchesFacility = filters.facilities.some((facility) =>
        hotelDescription.includes(facility.toLowerCase())
      );
      if (!matchesFacility && filters.facilities.length > 0) return true; // Allow pass if no facility match found in description
    }

    // Brands filter
    if (filters.brands.length > 0) {
      const hotelName = hotel.name.toLowerCase();
      const matchesBrand = filters.brands.some((brand) =>
        hotelName.includes(brand.toLowerCase())
      );
      if (!matchesBrand) return false;
    }

    // Property rating filter
    if (filters.propertyRatings.length > 0) {
      const hotelRating = Math.floor(hotel.rating);
      const hotelStarRating = `${hotelRating} Star${hotelRating !== 1 ? "s" : ""}`;
      if (!filters.propertyRatings.includes(hotelStarRating)) {
        return false;
      }
    }

    // Refundable/Free Cancellation filter
    if (filters.filters.includes("Free Cancellation")) {
      if (!hotel.isRefundable) return false;
    }

    // Recommended filter
    if (filters.filters.includes("Recommended")) {
      if (!hotel.recommended) return false;
    }

    return true;
  });

  const hotels = useMemo(() => {
    const sorted = [...filteredHotels];
    sorted.sort((a, b) => {
      const priceA = parseFloat(a.lowestRate) || 0;
      const priceB = parseFloat(b.lowestRate) || 0;
      return priceSort === "low-high" ? priceA - priceB : priceB - priceA;
    });
    return sorted;
  }, [filteredHotels, priceSort]);

  const togglePropertyRating = (rating: string) => {
    setFilters((prev) => {
      const exists = prev.propertyRatings.includes(rating);
      const propertyRatings = exists
        ? prev.propertyRatings.filter((item) => item !== rating)
        : [...prev.propertyRatings, rating];

      return { ...prev, propertyRatings };
    });
  };

  if (hotelSearch.isLoading) {
    return <ProfileLoader title="hotels..." />;
  }

  if (hotelSearch.isError) {
    return <HotelErrorPage />;
  }

  return (
    <>
      <section className="sticky top-0 z-50">
        <div className="absolute inset-0 bg-[#F5F6FA]/80 backdrop-blur-2xl"></div>
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <div className="max-w-3xl mx-auto">
            <BookingSearchForm
              useMobile={true}
              buttonText="Update search"
              isOtherPageSearch={true}
              formClassName="bg-transparent sm:rounded-none hover:bg-[#F3E9FF]"
            />
          </div>
        </div>
      </section>
      <div className="mt-4">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <div className="flex flex-col gap-8 pb-20">
            <div className="w-full bg-white rounded-2xl p-5 sm:p-6">
              <div className="flex flex-col gap-6">
                <div className="flex flex-col xl:flex-row xl:items-start xl:gap-10 gap-6">
                  <div className="max-w-xl space-y-3 text-core">
                    <div className="w-full h-24 flex items-end gap-0.5 px-2 overflow-x-auto">
                      {(() => {
                        const priceCounts = new Map<number, number>();

                        allHotels.forEach((hotel) => {
                          const price = parseInt(hotel.lowestRate) || 0;
                          priceCounts.set(
                            price,
                            (priceCounts.get(price) || 0) + 1
                          );
                        });

                        const sortedPrices = Array.from(
                          priceCounts.entries()
                        ).sort((a, b) => a[0] - b[0]);

                        return sortedPrices.map(([price, count]) => {
                          const priceRange = maxPrice - minPrice;
                          const heightPercent =
                            priceRange > 0
                              ? ((price - minPrice) / priceRange) * 100
                              : 50;

                          return (
                            <div
                              key={price}
                              className="bg-core rounded-t transition-all shrink-0"
                              style={{
                                height: `${Math.max(heightPercent, 5)}%`,
                                width: "8px",
                              }}
                              title={`$${price}: ${count} hotel(s)`}
                            />
                          );
                        });
                      })()}
                    </div>

                    <div className="w-full">
                      <Slider.Root
                        className="relative flex items-center select-none touch-none w-full h-5"
                        min={minPrice}
                        max={maxPrice}
                        step={Math.max(
                          1,
                          Math.floor((maxPrice - minPrice) / 100)
                        )}
                        value={value}
                        onValueChange={(newValue) =>
                          setValue([newValue[0], newValue[1]])
                        }
                        aria-label="Range"
                      >
                        <Slider.Track className="bg-gray-300 relative grow rounded-full h-2">
                          <Slider.Range className="absolute bg-core rounded-full h-full" />
                        </Slider.Track>
                        <Slider.Thumb className="block w-5 h-5 bg-core rounded-full focus:outline-none" />
                        <Slider.Thumb className="block w-5 h-5 bg-core rounded-full focus:outline-none" />
                      </Slider.Root>
                    </div>
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                      <span className="inline-flex items-center rounded-full bg-[#F5F6FA] px-3 py-1.5 text-xs font-semibold">
                        {filters.priceRange[0]} $ - {filters.priceRange[1]} $
                      </span>
                    </div>
                  </div>

                  <div className="w-full max-w-sm space-y-3 text-core">
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                      <h2 className="text-[15px] font-bold">Rating</h2>
                    </div>
                    <div className="flex flex-wrap gap-3">
                      {ratingOptions.map((rating) => {
                        const checked =
                          filters.propertyRatings.includes(rating);
                        return (
                          <label
                            key={rating}
                            className="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium cursor-pointer"
                          >
                            <Checkbox
                              checked={checked}
                              onCheckedChange={() =>
                                togglePropertyRating(rating)
                              }
                              className="size-4"
                            />
                            <span>{rating}</span>
                          </label>
                        );
                      })}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <main className="w-full bg-white space-y-4">
              {/* <ExclusiveOffersCarousel /> */}
              <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h2 className="text-core font-semibold text-lg md:text-xl">
                    What you currently see
                  </h2>
                  <p className="text-core text-xs tracking-tight">
                    We found {hotels.length} places that match your search.
                  </p>
                </div>

                <div className="flex flex-wrap gap-3 sm:justify-end">
                  <DropdownMenu>
                    <DropdownMenuTrigger className="inline-flex items-center gap-2 rounded-full bg-[#F5F6FA] px-3 py-2 text-xs font-semibold text-core focus:outline-none">
                      <span>Currency</span>
                      <span className="text-[#6B7280]">{currency}</span>
                      <Image
                        src={arrowDownIcon}
                        alt="Arrow down"
                        className="size-3"
                      />
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="min-w-[140px]">
                      {currencyOptions.map((option) => (
                        <DropdownMenuItem
                          key={option}
                          onSelect={() => {
                            setCurrency(option);
                          }}
                          className="cursor-pointer"
                        >
                          {option}
                        </DropdownMenuItem>
                      ))}
                    </DropdownMenuContent>
                  </DropdownMenu>

                  <DropdownMenu>
                    <DropdownMenuTrigger className="inline-flex items-center gap-2 rounded-full bg-[#F5F6FA] px-3 py-2 text-xs font-semibold text-core focus:outline-none">
                      <span>
                        <Image
                          src={sortIcon}
                          alt="Sort"
                          width={17}
                          height={17}
                        />
                      </span>
                      <span className="text-core">
                        {
                          priceSortOptions.find((p) => p.value === priceSort)
                            ?.label
                        }
                      </span>
                      <Image
                        src={upDownIcon}
                        alt="Arrow down"
                        width={15}
                        height={30}
                      />
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="min-w-[170px]">
                      {priceSortOptions.map((option) => (
                        <DropdownMenuItem
                          key={option.value}
                          onSelect={() => {
                            setPriceSort(
                              option.value as "low-high" | "high-low"
                            );
                          }}
                          className="cursor-pointer"
                        >
                          {option.label}
                        </DropdownMenuItem>
                      ))}
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
              </div>
              <div>
                <Tabs defaultValue="all" className="w-full">
                  <div className="flex flex-row flex-wrap items-center gap-3 lg:gap-30">
                    {/*                     
                    <TabsList className="flex-auto min-w-0 rounded-full h-12 gap-2.5 bg-[#F5F6FA]">
                      <TabsTrigger
                        value="all"
                        className="rounded-full text-core data-[state=active]:bg-core data-[state=active]:text-white"
                      >
                        All
                      </TabsTrigger>
                      <TabsTrigger
                        value="hotels"
                        className="rounded-full text-core data-[state=active]:bg-core data-[state=active]:text-white"
                      >
                        Hotels
                      </TabsTrigger>
                      <TabsTrigger
                        value="privateHomes"
                        className="rounded-full text-core data-[state=active]:bg-core data-[state=active]:text-white"
                      >
                        Private Homes
                      </TabsTrigger>
                    </TabsList>
                    */}

                    {/* <div className="flex items-center gap-3">
                      <button className="shrink-0 border border-core flex items-center py-2 px-3 md:px-4 text-core rounded-full gap-3 hover:bg-[#F5F6FA]">
                        <div className="size-6">
                          <Image
                            alt="Maps"
                            src={mapsIcon}
                            className="size-full"
                          />
                        </div>

                        <span className="font-semibold text-base">Maps</span>
                      </button>

                      <button className="shrink-0 bg-[#F5F6FA] w-auto md:w-52 flex items-center py-2 pl-4 pr-2 text-core rounded-full gap-3 hover:bg-gray-100">
                        <div className="size-5">
                          <Image
                            alt="Filters"
                            src={filterIcon}
                            className="size-full"
                          />
                        </div>
                        <span className="font-semibold text-sm">Filters</span>
                        <div className="size-8 ml-auto">
                          <Image
                            alt="Updown"
                            src={updownIcon}
                            className="size-full"
                          />
                        </div>
                      </button>
                    </div> */}
                  </div>
                  <TabsContent value="all" className="py-5">
                    <div className="flex flex-col gap-6">
                      {hotels.map((hotel) => (
                        <Link
                          href={`/hotels/${hotel.code}`}
                          key={hotel.code}
                          className="group flex flex-col p-2 lg:flex-row bg-[#F5F6FA] border border-[#E6E8F2] rounded-[20px] overflow-hidden w-full max-w-[1512px]"
                        >
                          <div className="flex lg:hidden items-center gap-2 text-xs px-3 pt-1 pb-2 order-1">
                            {hotel.totalReviews > 0 ? (
                              <div className="flex items-center gap-2 border border-[#A48B05] bg-[#F6F2DA] rounded-full px-2 py-0.5">
                                <div className="bg-[#FFD700] border border-[#A48B05] size-2.5 rounded-full" />
                                <span className="font-normal">
                                  {hotel.rating.toFixed(2)}
                                </span>
                              </div>
                            ) : (
                              <HotelRatingRow />
                            )}
                            <span className="text-[#8B90AA] text-xs">
                              {hotel.totalReviews
                                ? `${hotel.totalReviews} reviews`
                                : "No reviews yet"}
                            </span>
                            <div
                              style={{
                                width: "50px",
                                height: "30px",
                                gap: "10px",
                                opacity: 1,
                                borderRadius: "39px",
                                borderWidth: "1px",
                                background: "#FFFFFF",
                                border: "1px solid #E6E8F2",
                                display: "flex",
                                alignItems: "center",
                                justifyContent: "center",
                                marginLeft: "auto",
                              }}
                            >
                              <Image
                                src={heartIcon}
                                alt="heart icon"
                                width={11}
                                height={10}
                              />
                            </div>
                          </div>

                          <div className="shrink-0 w-full h-64 lg:w-[530px] lg:h-[280px] relative order-2 lg:order-0">
                            <Image
                              src={hotel.images[0] || fourSeasongsHotel}
                              alt={hotel.name}
                              className="object-cover w-full h-full rounded-[20px]"
                              width={630}
                              height={280}
                            />
                            <div className="absolute inset-0 bg-white opacity-0 rounded-[20px] transition-opacity duration-300 group-hover:opacity-10" />
                          </div>

                          <div className="flex flex-col grow p-3 relative text-core order-3 lg:order-0">
                            {/* Reviews section for desktop only */}
                            <div className="hidden lg:flex items-center gap-2 text-xs mb-3">
                              {hotel.totalReviews > 0 ? (
                                <div className="flex items-center gap-2 border border-[#A48B05] bg-[#F6F2DA] rounded-full px-2 py-0.5">
                                  <div className="bg-[#FFD700] border border-[#A48B05] size-2.5 rounded-full" />
                                  <span className="font-normal">
                                    {hotel.rating.toFixed(2)}
                                  </span>
                                </div>
                              ) : (
                                <HotelRatingRow />
                              )}
                              <span className="text-[#8B90AA] text-xs">
                                {hotel.totalReviews
                                  ? `${hotel.totalReviews} reviews`
                                  : "No reviews yet"}
                              </span>
                              <div
                                style={{
                                  width: "50px",
                                  height: "30px",
                                  gap: "10px",
                                  opacity: 1,
                                  borderRadius: "39px",
                                  borderWidth: "1px",
                                  background: "#FFFFFF",
                                  border: "1px solid #E6E8F2",
                                  display: "flex",
                                  alignItems: "center",
                                  justifyContent: "center",
                                  marginLeft: "auto",
                                }}
                              >
                                <Image
                                  src={heartIcon}
                                  alt="heart icon"
                                  width={11}
                                  height={10}
                                />
                              </div>
                            </div>
                            <div className="flex flex-col-reverse gap-2 md:gap-0 md:flex-row md:justify-between md:items-start">
                              <div>
                                <h2 className="text-base font-bold">
                                  {hotel.name}
                                </h2>
                                <div className="flex items-center gap-1 text-[#2e2e48]">
                                  {Array.from(
                                    {
                                      length: Math.max(
                                        1,
                                        Math.min(
                                          5,
                                          Math.round(hotel.rating || 0)
                                        )
                                      ),
                                    },
                                    (_, idx) => (
                                      <Image
                                        key={idx}
                                        alt="star icon"
                                        src={starIcon}
                                        className="w-3.5 h-3.5"
                                        width={13}
                                        height={13}
                                      />
                                    )
                                  )}
                                </div>
                                {hotel.location && (
                                  <p className="text-xs mt-2">
                                    {hotel.location || ""}
                                  </p>
                                )}
                              </div>
                              {/* Pricing section - hidden on mobile in this position */}
                              <div className="hidden md:flex mt-auto flex-col items-end text-right text-xs">
                                <p className="text-core">{`Price for ${hotel.nights} nights, ${hotel?.adults || 0} adults${hotel?.children ? `, ${hotel.children} children` : ""}`}</p>
                                <p className="text-[#3E51CD] font-bold text-lg leading-none">
                                  {hotel.currency} {hotel.lowestRate}
                                </p>
                                + inc. tax and charges
                              </div>
                            </div>

                            <div className="mt-4 text-xs mb-4">
                              <ul className="mt-3 space-y-1 text-[#065F46]">
                                {hotel.freeCancellation && (
                                  <li className="flex items-center gap-2 text-xs">
                                    <FaCheck className="w-3 h-3" /> Free
                                    Cancellation
                                  </li>
                                )}
                                {hotel.board && (
                                  <li className="flex items-center gap-2 text-xs">
                                    <FaCheck className="w-3 h-3" />{" "}
                                    {hotel.board === "Bed and Breakfast"
                                      ? "Breakfast Included"
                                      : hotel.board}
                                  </li>
                                )}
                              </ul>
                            </div>
                            <div className="mt-4 lg:mt-8 flex flex-col md:flex-row items-end md:items-center justify-between gap-3 text-xs">
                              {/* Pricing section for mobile - shown at the right end on mobile */}
                              <div className="flex md:hidden flex-col items-end text-right text-xs w-full">
                                <p className="text-core">{`Price for ${hotel.nights} nights, ${hotel?.adults || 0} adults${hotel?.children ? `, ${hotel.children} children` : ""}`}</p>
                                <p className="text-[#3E51CD] font-bold text-lg leading-none">
                                  {hotel.currency} {hotel.lowestRate}
                                </p>
                                <span className="text-xs">
                                  + inc. tax and charges
                                </span>
                              </div>
                              {/* Rooms left and button section - positioned at bottom on large screens */}
                            </div>
                            <div className="hidden md:flex lg:mt-auto flex-row justify-between items-end gap-3 w-full">
                              <div className="text-[#CD3838] font-bold self-start text-xs">
                                {hotel.roomsLeftLabel}
                              </div>
                              {/* Check Availability button */}
                              <button
                                className="font-semibold text-nowrap text-xs text-core flex items-center justify-center w-[140px] h-[50px] px-2.5 py-px gap-2.5 rounded-[22.5px] bg-[#E4E6F2] cursor-pointer opacity-100 self-end"
                                style={{ transform: "rotate(0deg)" }}
                              >
                                Check Availability
                                <Image
                                  src={rightIcon}
                                  alt="Right arrow"
                                  width={17}
                                  height={11}
                                />
                              </button>
                            </div>
                          </div>
                        </Link>
                      ))}
                    </div>
                  </TabsContent>
                  {/* <TabsContent value="hotels">No hotels found.</TabsContent> */}
                  {/* <TabsContent value="privateHomes">
                    No private homes found.
                  </TabsContent> */}
                </Tabs>
              </div>
            </main>
          </div>
        </div>
      </div>
    </>
  );
}
