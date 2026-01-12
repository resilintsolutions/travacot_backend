"use client";

import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import OffersCarousel from "./OffersCarousel";
import { useTailoredHotelRecommendations } from "@/app/search/hooks/useHotels";
import { HotelOffer } from "@/app/search/types";
import HotelsSkeleton from "./HotelsSkeleton";

export default function SectionPersonalizedOffers() {
  const tailoredHotels = useTailoredHotelRecommendations();
  const offersSectionHotels = tailoredHotels?.data?.tabs.offers;
  const weekendDealsSectionHotels = tailoredHotels?.data?.tabs["weekend_deals"];
  const topRatedSectionHotels = tailoredHotels?.data?.tabs["top_rated"];
  const promotionsSectionHotels = tailoredHotels?.data?.tabs.promotions;
  if (tailoredHotels.isLoading) {
    return (
      <section className="overflow-hidden">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <h2 className="font-semibold text-core text-[17px] mb-2">
            Tailored For You
          </h2>
          <HotelsSkeleton />
        </div>
      </section>
    );
  }
  return (
    <section className="overflow-hidden">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
        <h2 className="font-semibold text-core text-[17px] mt-4">
          Tailored For You
        </h2>

        <Tabs defaultValue="offers" className="relative">
          <TabsList className="absolute top-0 left-0 z-10 bg-white h-10 flex gap-4">
            <TabsTrigger
              value="offers"
              className="font-normal border-l-0 border-r-0 border-t-0 border-b-2 px-0 border-white rounded-none h-full data-[state=active]:font-semibold data-[state=active]:shadow-none data-[state=active]:border-core"
            >
              Offers
            </TabsTrigger>
            <TabsTrigger
              value="weekend-deals"
              className="font-normal border-l-0 border-r-0 border-t-0 border-b-2 px-0 border-white rounded-none h-full data-[state=active]:font-semibold data-[state=active]:shadow-none data-[state=active]:border-core"
            >
              Weekend Deals
            </TabsTrigger>
            <TabsTrigger
              value="top-rated"
              className="font-normal border-l-0 border-r-0 border-t-0 border-b-2 px-0 border-white rounded-none h-full data-[state=active]:font-semibold data-[state=active]:shadow-none data-[state=active]:border-core"
            >
              Top Rated
            </TabsTrigger>
            <TabsTrigger
              value="promotions"
              className="font-normal border-l-0 border-r-0 border-t-0 border-b-2 px-0 border-white rounded-none h-full data-[state=active]:font-semibold data-[state=active]:shadow-none data-[state=active]:border-core"
            >
              Promotions
            </TabsTrigger>
          </TabsList>
          <TabsContent value="offers" className="mt-12 sm:mt-0">
            <OffersCarousel hotels={offersSectionHotels as HotelOffer[]} />
          </TabsContent>
          <TabsContent value="weekend-deals" className="mt-12 sm:mt-0">
            <OffersCarousel
              hotels={weekendDealsSectionHotels as HotelOffer[]}
            />
          </TabsContent>
          <TabsContent value="top-rated" className="mt-12 sm:mt-0">
            <OffersCarousel hotels={topRatedSectionHotels as HotelOffer[]} />
          </TabsContent>
          <TabsContent value="promotions" className="mt-12 sm:mt-0">
            <OffersCarousel hotels={promotionsSectionHotels as HotelOffer[]} />
          </TabsContent>
        </Tabs>
      </div>
    </section>
  );
}
