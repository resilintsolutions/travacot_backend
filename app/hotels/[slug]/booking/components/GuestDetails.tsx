import { Checkbox } from "@/components/ui/checkbox";
import { getCookie } from "@/lib/cookies";
import { Fragment } from "react";
import { IoIosArrowDown } from "react-icons/io";
import { BookingInfo } from "../page";
import { useSession } from "next-auth/react";
import { UserProfile } from "@/app/profile/types";

export const GuestDetails = ({
  bookingState,
  handleBookingInfoChange,
  profile,
}: {
  bookingState: BookingInfo;
  handleBookingInfoChange: (u: BookingInfo) => void;
  profile: UserProfile;
}) => {
  const session = useSession();
  const token = session.data?.user.accessToken;
  const isLoggedIn = getCookie("access_token") || Boolean(token);

  return (
    <Fragment>
      <h2 className="font-semibold mb-4">Who&apos;s coming?</h2>

      {isLoggedIn && (
        <>
          <div className="bg-[#F8F9FC] p-4 sm:rounded-full flex items-center justify-between mb-2 -mx-4 sm:mx-0">
            <span className="hidden md:block text-xs text-[#7F7F93]">
              {profile?.name || ""}
            </span>
            <span className="md:hidden text-xs text-[#7F7F93]">
              {profile?.name || ""}
            </span>
            <IoIosArrowDown />
          </div>

          <button
            onClick={() =>
              handleBookingInfoChange({
                ...bookingState,
                showFormTraveler: true,
              })
            }
            className="flex items-center justify-center border border-[#1A255A] rounded-full py-3 px-6 mb-6 cursor-pointer"
          >
            <span className="font-semibold text-xs">Change Traveler</span>
          </button>
        </>
      )}

      {!isLoggedIn && (
        <>
          <div className="md:hidden bg-[#F8F9FC] p-4 sm:rounded-full flex items-center justify-between mb-2 -mx-4 sm:mx-0">
            <span className="text-xs text-[#7F7F93]">Jamal Chatilla</span>
            <IoIosArrowDown />
          </div>

          <div className="bg-[#F8F9FC] rounded-2xl flex flex-col mb-2 -mx-4 sm:mx-0">
            <div className="flex flex-col gap-1 p-4 text-xs text-core border-b border-[#D9D9D9]">
              <label className="font-semibold">First name</label>
              <input
                type="text"
                placeholder="Your name"
                value={bookingState.guestFirstName}
                onChange={(e) =>
                  handleBookingInfoChange({
                    ...bookingState,
                    guestFirstName: e.target.value,
                  })
                }
                className="focus:ring-0 focus:outline-none text-xs placeholder:text-[#7F7F93]"
              />
            </div>

            <div className="flex flex-col gap-1 p-4 text-xs text-core border-b border-[#D9D9D9]">
              <label className="font-semibold">Last name</label>
              <input
                type="text"
                placeholder="Your last name"
                value={bookingState.guestLastName}
                onChange={(e) =>
                  handleBookingInfoChange({
                    ...bookingState,
                    guestLastName: e.target.value,
                  })
                }
                className="focus:ring-0 focus:outline-none text-xs placeholder:text-[#7F7F93]"
              />
            </div>

            <div className="flex flex-col gap-1 p-4 border-b border-[#D9D9D9] text-xs text-core">
              <label className="font-semibold">Email Address</label>
              <input
                type="text"
                placeholder="Your email"
                value={bookingState.guestEmail}
                onChange={(e) =>
                  handleBookingInfoChange({
                    ...bookingState,
                    guestEmail: e.target.value,
                  })
                }
                className="focus:ring-0 focus:outline-none text-xs placeholder:text-[#7F7F93]"
              />
            </div>

            <div className="flex">
              <select className="p-4 text-xs border-r border-[#D9D9D9]">
                <option value="">USA +1</option>
              </select>

              <input
                type="text"
                placeholder="Phone number"
                value={bookingState.guestPhone}
                onChange={(e) =>
                  handleBookingInfoChange({
                    ...bookingState,
                    guestPhone: e.target.value,
                  })
                }
                className="focus:ring-0 focus:outline-none text-xs p-4 placeholder:text-[#7F7F93]"
              />
            </div>
          </div>

          {isLoggedIn && (
            <>
              <div className="hidden md:flex items-center gap-4 mb-2">
                <p className="text-xs p-4">
                  Do you want us to send the reservation to a different email?
                </p>

                <div className="flex items-center justify-center gap-2">
                  <button className="h-10 w-[77px]  flex items-center justify-center rounded-full bg-core text-white cursor-pointer">
                    Yes
                  </button>
                  <button className="h-10 w-[77px]  flex items-center justify-center rounded-full border cursor-pointer">
                    No
                  </button>
                </div>
              </div>
              <input
                type="text"
                placeholder="Email address"
                className="hidden md:block w-full focus:ring-0 focus:outline-none text-xs p-4 rounded-full bg-[#F8F9FC] mb-6 placeholder:text-[#7F7F93]"
              />
              <div className="hidden md:flex items-center space-x-2 mb-6">
                <Checkbox id="save-profile" />
                <label
                  htmlFor="save-profile"
                  className="text-xs font-semibold leading-none cursor-pointer"
                >
                  Save guest profile for future bookings
                </label>
              </div>

              <button
                onClick={() =>
                  handleBookingInfoChange({
                    ...bookingState,
                    showFormTraveler: false,
                  })
                }
                className="h-10 w-[100px] flex items-center justify-center rounded-full border border-core mb-6 text-white bg-core md:bg-white md:text-core"
              >
                <span className="font-semibold text-xs">Cancel</span>
              </button>
            </>
          )}
        </>
      )}
    </Fragment>
  );
};
