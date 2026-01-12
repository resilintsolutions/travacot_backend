import { cn } from "@/lib/utils";
import { BookingInfo } from "../page";

export const SpecialRequests = ({
  bookingState,
  handleBookingInfoChange,
}: {
  bookingState: BookingInfo;
  handleBookingInfoChange: (u: BookingInfo) => void;
}) => {
  const isOpen = bookingState.specialRequestsOpen;
  return (
    <div className="hidden md:block bg-[#FAFAFC] rounded-[30px] p-4 mb-4">
      <div className="md:min-h-20 text-core flex flex-wrap gap-2 items-center">
        <div className="flex flex-col gap-2">
          <h3 className="font-bold">Special requests</h3>
          <p className="text-xs max-w-sm">
            Do you have any special request that you would like to inform the
            hotel before arrival?
          </p>
        </div>
        <div className="flex gap-2 ml-auto">
          <button
            onClick={() =>
              handleBookingInfoChange({
                ...bookingState,
                specialRequestsOpen: true,
              })
            }
            className={cn(
              "w-[90px] h-10 text-sm flex items-center justify-center border border-core rounded-full cursor-pointer",
              isOpen === true ? "bg-core text-white" : "bg-white text-core"
            )}
          >
            Yes
          </button>
          <button
            onClick={() =>
              handleBookingInfoChange({
                ...bookingState,
                specialRequestsOpen: false,
              })
            }
            className={cn(
              "w-[90px] h-10 text-sm flex items-center justify-center border border-core rounded-full cursor-pointer",
              isOpen === false ? "bg-core text-white" : "bg-white text-core"
            )}
          >
            No
          </button>
        </div>
      </div>

      {isOpen && (
        <div className="border border-[#C7C7CF] bg-white rounded-[30px] mt-2">
          <input
            type="text"
            placeholder="Please write your special request"
            value={bookingState.specialRequestsText}
            onChange={(e) =>
              handleBookingInfoChange({
                ...bookingState,
                specialRequestsText: e.target.value,
              })
            }
            className="w-full focus:ring-0 focus:outline-none text-xs p-4 placeholder:text-[#7F7F93]"
          />
        </div>
      )}
    </div>
  );
};
