import MainFooter from "@/components/shared/MainFooter";
import SectionCardReward from "@/components/features/landing/SectionCardReward";
import SectionSearchStays from "@/components/features/landing/SectionSearchStays";
// import SectionTravelDestination from "@/components/features/landing/SectionTravelDestination";
// import SectionPersonalizedOffers from "@/components/features/landing/SectionPersonalizedOffers";
import SectionLocationSelector from "@/components/features/landing/SectionLocationSelector";
// import SectionWhatWeHave from "@/components/features/landing/SectionWhatWeHave";
import SectionDownloadApp from "@/components/features/landing/SectionDownloadApp";
import MarketPlaceDeals from "@/components/features/landing/MarketPlaceDeals";
export default function Page() {
  return (
    <>
      <SectionSearchStays />
      <SectionLocationSelector />
      <MarketPlaceDeals />
      <SectionCardReward />
      <SectionDownloadApp />
      {/* <SectionPersonalizedOffers /> */}
      {/* <SectionTravelDestination /> */}
      {/* <SectionWhatWeHave /> */}
      <div className="py-20"></div>
      <MainFooter />
    </>
  );
}
