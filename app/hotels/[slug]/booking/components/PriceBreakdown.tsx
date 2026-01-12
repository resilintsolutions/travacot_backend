import Image from "next/image";
import { useState } from "react";
import { IoIosArrowDown } from "react-icons/io";
import rewardsIcon from "@/assets/images/rewards-icon.svg";
import { HotelTax, SelectedHotel } from "../page";

export const PriceBreakdown = ({
  selectedHotel,
  checkInDate,
  checkOutDate,
}: {
  selectedHotel?: SelectedHotel;
  checkInDate?: string;
  checkOutDate?: string;
}) => {
  const [showBreakdown, setShowBreakdown] = useState(false);

  // Calculate nights
  const nights = checkInDate && checkOutDate 
    ? Math.ceil((new Date(checkOutDate).getTime() - new Date(checkInDate).getTime()) / (1000 * 60 * 60 * 24))
    : 0;

  // Calculate per night rate
  const perNightRate = nights > 0 && selectedHotel?.selectedRate?.pricing.final_price
    ? selectedHotel.selectedRate.pricing.final_price / nights / (selectedHotel.selectedRate.rooms || 1)
    : 0;

  // Get currency symbol
  const currencySymbol = selectedHotel?.room?.currency === "EUR" ? "€" : "$";
  const currency = selectedHotel?.room?.currency || "USD";

  // Calculate taxes total (only included taxes)
  const includedTaxesTotal = selectedHotel?.selectedRate?.taxes
    ?.filter(tax => tax.included !== false)
    ?.reduce((sum, tax) => sum + (parseFloat(tax.amount?.toString() || "0") || 0), 0) || 0;

  // Render tax section based on tax data
  const renderTaxSection = (taxes: HotelTax[] = []) => {
    if (!taxes || taxes.length === 0) return null;

    return taxes.map((tax, index) => {
      if (tax.included === false && (tax.amount || 0) > 0) {
        return (
          <div key={index} className="flex items-center justify-between">
            <span>Tax on fees (due at property)</span>
            <span className="shrink-0">
              {tax.currency} {parseFloat(tax.amount?.toString() || "0").toFixed(2)}
            </span>
          </div>
        );
      }
      if (tax.type === "comment" || tax.type === "info") {
        return (
          <div key={index} className="mb-2">
            <span className="text-amber-600">{tax.message}</span>
          </div>
        );
      }
      return null;
    });
  };
  return (
    <div>
      <div className="p-6 bg-[#FDFDFE] rounded-t-[30px] border-4 border-b-0 border-[#E8EAF3]">
        <div
          onClick={() => setShowBreakdown(!showBreakdown)}
          className="flex flex-wrap items-center justify-between text-core mb-2 cursor-pointer"
        >
          <h3 className="font-semibold">Price</h3>
          <div className="text-right">
            <h3 className="font-semibold">
              {currency}{currencySymbol} {selectedHotel?.selectedRate?.pricing?.final_price?.toFixed(2) || "0.00"}
            </h3>
            <p className="text-xs">
              Excluding what is or could be due at the property
            </p>
          </div>
        </div>

        <button
          onClick={() => setShowBreakdown(!showBreakdown)}
          className="flex items-center gap-2 text-core cursor-pointer select-none"
        >
          <IoIosArrowDown
            className={`transition-transform duration-300 ${
              showBreakdown ? "rotate-180" : ""
            }`}
          />
          <p className="text-xs font-semibold">
            {showBreakdown ? "Hide breakdown" : "Show breakdown"}
          </p>
        </button>

        {showBreakdown && (
          <div className="text-xs space-y-1 mt-2">
            <div className="flex items-center justify-between">
              <span>{nights} night{nights !== 1 ? 's' : ''} x {selectedHotel?.selectedRate?.rooms || 1} room x {currency}{currencySymbol} {perNightRate.toFixed(2)}</span>
              <span className="shrink-0">{currency}{currencySymbol} {selectedHotel?.selectedRate?.pricing?.final_price?.toFixed(2) || "0.00"}</span>
            </div>
            {includedTaxesTotal > 0 && (
              <div className="flex items-center justify-between">
                <span className="font-medium underline">Taxes & Fees</span>
                <span className="font-medium underline shrink-0">{currency}{currencySymbol} {includedTaxesTotal.toFixed(2)}</span>
              </div>
            )}
            {renderTaxSection(selectedHotel?.selectedRate?.taxes)}

            <div className="flex items-center justify-between mt-4">
              <span className="font-bold">Total</span>
              <span className="font-bold shrink-0">{currency}{currencySymbol} {selectedHotel?.selectedRate?.pricing?.final_price?.toFixed(2) || "0.00"}</span>
            </div>
            <div className="flex items-center justify-between">
              <span>To be paid today</span>
              <span className="shrink-0">{currency}{currencySymbol} {selectedHotel?.selectedRate?.pricing?.final_price?.toFixed(2) || "0.00"}</span>
            </div>
            {selectedHotel?.selectedRate?.taxes?.some(tax => tax.included === false && (tax.amount || 0) > 0) && (
              <div className="flex items-center justify-between mb-4">
                <span>To be paid at the property</span>
                <span className="shrink-0">
                  {currency}{currencySymbol} {
                    selectedHotel.selectedRate.taxes
                      .filter(tax => tax.included === false && (tax.amount || 0) > 0)
                      .reduce((sum, tax) => sum + (parseFloat(tax.amount?.toString() || "0") || 0), 0)
                      .toFixed(2)
                  }
                </span>
              </div>
            )}
          </div>
        )}
      </div>

      <div className="p-6 flex flex-wrap gap-2 items-center justify-between bg-[#F9F4FF] border-4 border-[#E9D5FF] rounded-b-[30px]">
        <Image alt="" src={rewardsIcon} className="w-[50px] h-[50px]" />

        <div>
          <h2 className="font-bold mb-2">Travacot Rewards</h2>
          <p className="text-xs font-semibold">
            We realized that you booked here before.
          </p>
          <p className="text-xs">
            You only need to book 2 more stays to get your discount for your
            next trip!
          </p>
        </div>

        <div className="flex gap-2">
          <div className="p-2 rounded-full bg-core"></div>
          <div className="p-2 rounded-full bg-core"></div>
          <div className="p-2 rounded-full bg-white border border-core"></div>
          <div className="p-2 rounded-full bg-white border border-core"></div>
        </div>
      </div>
    </div>
  );
};
