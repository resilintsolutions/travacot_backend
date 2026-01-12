import { BookingInfo } from "../page";

export const BillingInfo = ({
  bookingState,
  handleBookingInfoChange,
}: {
  bookingState: BookingInfo;
  handleBookingInfoChange: (u: BookingInfo) => void;
}) => {
  return (
    <div className="hidden md:block mt-6">
      <h2 className="font-semibold mb-2">Billing Info</h2>

      <div className="bg-[#F8F9FC] rounded-2xl flex flex-col mb-4">
        <div className="flex flex-col gap-2 py-2 px-4 border-b border-[#D9D9D9]">
          <label className="font-semibold text-xs">Country</label>
          <select
            className="text-xs text-[#7F7F93]"
            value={bookingState.billingCountry}
            onChange={(e) =>
              handleBookingInfoChange({
                ...bookingState,
                billingCountry: e.target.value,
              })
            }
          >
            <option value="">Hong Kong SAR China</option>
          </select>
        </div>

        <div className="flex flex-col py-2 px-4 gap-2">
          <label className="font-semibold text-xs">Zip Code</label>
          <input
            type="text"
            placeholder="XXXXX"
            value={bookingState.billingZip}
            onChange={(e) =>
              handleBookingInfoChange({
                ...bookingState,
                billingZip: e.target.value,
              })
            }
            className="focus:ring-0 focus:outline-none text-xs placeholder:text-[#7F7F93]"
          />
        </div>
      </div>
    </div>
  );
};
