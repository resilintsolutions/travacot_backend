import CancellationPolicy from "@/components/features/terms/CancellationPolicy";
import { Checkbox } from "@/components/ui/checkbox";
import { MobileDealSelector } from "./MobileDealSelector";
import { BookingInfo, SelectedHotel } from "../page";

export const CancellationPolicySection = ({
  bookingState,
  handleBookingInfoChange,
  formatCancellationText,
  selectedHotel,
}: {
  bookingState: BookingInfo;
  handleBookingInfoChange: (u: BookingInfo) => void;
  formatCancellationText: string;
  selectedHotel: SelectedHotel;
}) => {
  const isOpen = bookingState.dealSelectorOpen;

  const onClose = () =>
    handleBookingInfoChange({ ...bookingState, dealSelectorOpen: false });

  return (
    <div className="text-core flex flex-col mb-4 mt-6">
      <h2 className="font-semibold mb-1">Cancellation Policy</h2>
      <p className="text-xs mb-4">This is what you selected</p>

      <ul className="list-disc ml-4 mb-3 text-xs text-[#065F46]">
        <li>{formatCancellationText}</li>
        {selectedHotel?.selectedRate?.boardName === "BED AND BREAKFAST" && (
          <li>✓ Breakfast Included</li>
        )}
      </ul>

      <p className="text-xs mb-1">
        No shows are subject to a property fee equal to 100% of the total amount
        paid for reservation.
      </p>

      <CancellationPolicy
        title="Read travacot's cancellation policy here"
        className="text-xs text-[#3E51CD] underline mb-4"
      />

      <div className="text-xs flex items-center justify-between mb-4">
        <span className="font-medium">Don&apos;t like this deal?</span>

        <button
          onClick={() =>
            handleBookingInfoChange({
              ...bookingState,
              dealSelectorOpen: true,
            })
          }
          className="text-[#3E51CD] font-bold rounded-[30px] hover:bg-[#3E51CD]/20 px-3 py-2 cursor-pointer"
        >
          Change Deal
        </button>
      </div>

      <MobileDealSelector
        isOpen={isOpen}
        onClose={onClose}
        selectedHotel={selectedHotel}
      />

      <div className="flex items-center space-x-2 bg-[#F4F6FA] rounded-[20px] p-2 w-fit">
        <Checkbox
          id="cancellation-policy"
          className="rounded-full size-4 border border-core text-base"
          checked={bookingState.cancellationAccepted}
          onCheckedChange={(checked) =>
            handleBookingInfoChange({
              ...bookingState,
              cancellationAccepted: !!checked,
            })
          }
        />
        <label
          htmlFor="cancellation-policy"
          className="text-xs cursor-pointer font-medium"
        >
          I agree on the cancellation policy that I chose
        </label>
      </div>
    </div>
  );
};
