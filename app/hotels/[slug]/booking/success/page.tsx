"use client";
import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";
import checkmarkGreen from "@/assets/images/checkmark-green.svg";
import paymenChoiceHotel from "@/assets/images/payment-hotel-choice.png";
import bookingRewardIcon from "@/assets/images/booking-reward-icon.svg";
import { useSearch } from "@/app/search/context/SearchContext";
import { useReservationDetails } from "@/app/reservations/hooks/useReservations";
import { formatDate } from "date-fns";
import ProfileLoader from "@/app/profile/loading";
import { cn } from "@/lib/utils";
import { getCookie } from "@/lib/cookies";
import { useSession } from "next-auth/react";
import { useRouter } from "next/navigation";
// import reservationImage1 from "@/assets/images/reserved1.png";

export default function Page() {
  const { reservationId } = useSearch();
  const router = useRouter();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const session = useSession();
  const token = session.data?.user.accessToken;
  const [isLoggedIn, setIsLoggedIn] = useState(false);

  const { data, isFetching } = useReservationDetails(
    reservationId as unknown as string
  );
  const reservation = data;

  useEffect(() => {
    const loggedIn =
      (typeof window !== "undefined" && Boolean(getCookie("access_token"))) ||
      Boolean(token);
    // eslint-disable-next-line react-hooks/set-state-in-effect
    setIsLoggedIn(loggedIn);
  }, [token]);

  const handleViewReservation = () => {
    if (isLoggedIn) {
      router.push(`/reservations?id=${reservationId}`);
      return;
    }

    setIsModalOpen(true);
  };

  if (isFetching) {
    return <ProfileLoader title="reservation details" />;
  }

  return (
    <div className="min-h-screen flex flex-col">
      <div className="flex-1 flex flex-col md:flex-row items-center justify-center gap-4 md:gap-0 my-2">
        <h2 className="md:hidden text-core text-left font-semibold w-full px-4 text-xl">
          Booking Complete!
        </h2>

        <div className="w-full md:max-w-sm md:rounded-[20px] overflow-hidden p-4 border border-[#C9D0E7] relative md:ml-4">
          <div className="absolute inset-0 p-0.5">
            <div
              style={{ backgroundImage: `url(${reservation?.hotel.image})` }}
              className="bg-cover bg-no-repeat size-full rounded-[20px]"
            ></div>
          </div>

          <div className="absolute inset-0 bg-white/90 backdrop-blur-2xl"></div>

          <div className="hidden md:block relative rounded-[20px] w-full h-[345px] overflow-hidden mb-4">
            <Image
              alt="Hotel choice image"
              src={reservation?.hotel.image || paymenChoiceHotel}
              fill
              className="object-cover"
            />
          </div>

          <div className="relative flex items-center gap-4 w-fit mb-4">
            <Image
              alt="Checkmark"
              src={checkmarkGreen}
              className="h-10 w-10 object-contain"
            />
            <h4 className="hidden md:block text-[#065F46] font-semibold">
              Booking Complete!
            </h4>
            <h4 className="md:hidden text-[#065F46] font-semibold">
              You completed your booking!
            </h4>
          </div>

          <div className="relative flex flex-col mb-4">
            <h4 className="font-semibold text-core">Confirmation Number</h4>
            <p className="text-core">
              {reservation?.reservation?.confirmation_number}
            </p>
          </div>

          <div className="relative flex gap-4 mb-4">
            <div className="text-core flex flex-col items-start">
              <h3 className="font-semibold">Check-in</h3>
              <p>
                {formatDate(
                  new Date(reservation?.hotel.check_in || ""),
                  "MMM d, yyyy"
                )}
              </p>
            </div>
            <div className="text-core flex flex-col items-start">
              <h3 className="font-semibold">Check-out</h3>
              <p>
                {formatDate(
                  new Date(reservation?.hotel?.check_out || new Date()),
                  "MMM d, yyyy"
                )}
              </p>
            </div>
          </div>

          <p className="relative text-core mb-6">
            You booked {reservation?.hotel?.nights || 0} nights!
          </p>
        </div>

        <div className="w-full max-w-xl flex flex-col px-4">
          <div className="flex items-center gap-2 mb-4">
            <div className="">
              <Image
                alt="Booking reward icon"
                src={bookingRewardIcon}
                className="w-10 h-10"
              />
            </div>
            <div className="flex flex-col text-core">
              <h3 className="font-semibold text-lg">Hello, rewards!</h3>
              <p className="text-xs">You get points every 4 nights.</p>
            </div>
          </div>

          <p className="text-core text-xs ml-2 mb-4">
            We&apos;ll send your rewards the day <strong>after</strong> you
            check out.
          </p>

          <ul className="list-disc mx-8 text-core mb-10">
            <li>
              <p className="font-semibold text-xs ml-4 mb-2">
                What can I do with points?
              </p>
              <p className="text-xs ml-4 mb-4">
                Your points can give you free stays, discounts and more to come!
              </p>
            </li>

            <li>
              <p className="font-semibold text-xs ml-4 mb-2">
                What happens when I cancel?
              </p>
              <p className="text-xs ml-4 mb-4">
                Don&apos;t worry! When you cancel, your points roll back to
                where they were before your booking.
              </p>
            </li>
          </ul>

          <div className="mt-auto flex items-center gap-4">
            <Link
              href="/"
              className="h-10 w-1/2 rounded-[30px] bg-core text-white font-semibold text-xs flex items-center justify-center"
            >
              Back Home
            </Link>
            <button
              onClick={handleViewReservation}
              className="h-10 w-1/2 rounded-[30px] bg-white border border-core text-core font-semibold text-xs flex items-center justify-center hover:bg-core/5 transition-colors"
            >
              View Reservation
            </button>
          </div>
        </div>
      </div>

      {/* Reservation Modal */}
      <div
        className={cn(
          "fixed inset-0 bg-core/50 z-50 transition-opacity duration-300 ease-in-out",
          isModalOpen ? "opacity-100" : "opacity-0 pointer-events-none"
        )}
        onClick={() => setIsModalOpen(false)}
      />

      <div
        className={cn(
          "fixed inset-0 z-50 flex items-center justify-center p-4 transition-all duration-300 ease-in-out",
          isModalOpen ? "opacity-100" : "opacity-0 pointer-events-none"
        )}
      >
        <div
          id="reservation-details"
          className="w-full max-w-2xl h-[90vh] flex flex-col bg-[#F5F6FA] rounded-[30px] overflow-hidden"
        >
          <div
            style={{
              backgroundImage: `url(${reservation?.hotel.image})`,
              backgroundSize: "cover",
              backgroundPosition: "center",
              backgroundRepeat: "no-repeat",
            }}
            className="relative py-4 px-4 sm:px-6 h-auto sm:h-[126px] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-white rounded-t-[30px]"
          >
            <div className="absolute inset-0 bg-core/50 backdrop-blur-2xl"></div>
            <div className="flex flex-col z-10">
              <h2 className="font-semibold text-[17px] mb-2">
                {reservation?.hotel?.name}
              </h2>
              <p className="text-xs">{reservation?.reservation?.room_type}</p>
              <p className="text-xs">
                {formatDate(
                  new Date(reservation?.hotel?.check_in || ""),
                  "MMM dd, yyyy"
                )}{" "}
                -{" "}
                {formatDate(
                  new Date(reservation?.hotel?.check_out || ""),
                  "MMM dd, yyyy"
                )}
              </p>
            </div>

            <div
              className={cn(
                "rounded-[30px] w-[110px] h-10 flex items-center justify-center border text-xs z-10 capitalize",
                reservation?.reservation?.total_price?.status === "active" &&
                  "bg-[#E9F8F1] border-[#E9F8F1] text-[#065F46]",
                reservation?.reservation?.total_price?.status === "completed" &&
                  "bg-[#E7EBF7] border-[#2A2F8C] text-[#2A2F8C]",
                reservation?.reservation?.total_price?.status === "cancelled" &&
                  "bg-[#F7E7E7] border-[#8C2A2A] text-[#8C2A2A]"
              )}
            >
              {reservation?.reservation?.total_price?.status}
            </div>
          </div>

          <div className="flex-1 min-h-0 overflow-y-auto text-xs text-core px-4 sm:px-6 py-4">
            <h3 className="font-bold mb-3 text-sm">Guest Details</h3>

            <div className="max-w-xl flex flex-col gap-3 sm:gap-0 sm:flex-row sm:items-start sm:justify-between mb-6">
              <div className="w-full sm:w-fit flex flex-col gap-2">
                <div className="flex gap-2">
                  <span className="w-20 sm:w-[100px] font-semibold">Name</span>
                  <span className="flex-1 wrap-break-word">
                    {reservation?.guest?.name || "N/A"}
                  </span>
                </div>
              </div>

              <div className="w-full sm:w-fit flex flex-col gap-2">
                <div className="flex gap-2">
                  <span className="w-20 sm:w-[100px] font-semibold">Email</span>
                  <span className="flex-1 wrap-break-word">
                    {reservation?.guest?.email || "N/A"}
                  </span>
                </div>
                <div className="flex gap-2">
                  <span className="w-20 sm:w-[100px] font-semibold">
                    Guests
                  </span>
                  <span className="flex-1 wrap-break-word">
                    {reservation?.guest?.guests}
                  </span>
                </div>
              </div>
            </div>

            <h3 className="font-bold mb-3 text-sm">Reservation Details</h3>

            <div className="max-w-xl flex flex-col gap-3 sm:gap-0 sm:flex-row sm:items-start sm:justify-between mb-4">
              <div className="w-full sm:w-fit flex flex-col gap-2">
                <div className="flex gap-2">
                  <span className="w-20 sm:w-[100px] font-semibold">Hotel</span>
                  <span className="flex-1 wrap-break-word">
                    {reservation?.hotel?.name}
                  </span>
                </div>
                <div className="flex gap-2">
                  <span className="w-20 sm:w-[100px] font-semibold">
                    Check-in
                  </span>
                  <span className="flex-1 wrap-break-word">
                    {formatDate(
                      new Date(reservation?.hotel?.check_in || ""),
                      "MMM dd, yyyy"
                    )}
                  </span>
                </div>
                <div className="flex gap-2">
                  <span className="w-20 sm:w-[100px] font-semibold">Board</span>
                  <span className="flex-1 wrap-break-word">
                    ✔{" "}
                    {reservation?.reservation?.board === "ROOM ONLY"
                      ? "Room Only"
                      : reservation?.reservation?.board}
                  </span>
                </div>
              </div>

              <div className="w-full sm:w-auto flex flex-col gap-2">
                <div className="flex gap-2">
                  <span className="w-20 sm:w-[100px] font-semibold">
                    Room Type
                  </span>
                  <span className="flex-1 sm:flex-none wrap-break-word">
                    {reservation?.reservation?.room_type}
                  </span>
                </div>
                <div className="flex gap-2">
                  <span className="w-20 sm:w-[100px] font-semibold">
                    Check-out
                  </span>
                  <span className="flex-1 sm:flex-none wrap-break-word">
                    {formatDate(
                      new Date(reservation?.hotel?.check_out || ""),
                      "MMM dd, yyyy"
                    )}
                  </span>
                </div>
                <div className="flex gap-2">
                  <span className="w-20 sm:w-[100px] font-semibold">
                    Nights
                  </span>
                  <span className="flex-1 sm:flex-none wrap-break-word">
                    {reservation?.hotel?.nights}
                  </span>
                </div>
              </div>
            </div>

            <div className="text-core text-xs bg-[#E5E9FF] flex flex-col sm:flex-row gap-2 sm:gap-4 p-4 rounded-lg">
              <h4 className="font-semibold">Total Price</h4>
              <span>
                {reservation?.reservation?.total_price?.currency}{" "}
                {reservation?.reservation?.total_price?.amount}
              </span>
              <span>({reservation?.reservation?.total_price?.status})</span>
            </div>

            <div className="px-0 sm:px-0 py-4 text-xs text-core">
              <h3 className="font-bold mb-3 text-sm">Hotel Information</h3>
              <div className="max-w-xl">
                <div className="w-full flex flex-col gap-3">
                  <div className="flex gap-2">
                    <span className="w-20 sm:w-[100px] font-semibold whitespace-nowrap">
                      Address
                    </span>
                    <span className="flex-1 wrap-break-word">
                      {reservation?.hotel_information?.address}
                    </span>
                  </div>
                  <div className="flex gap-2">
                    <span className="w-20 sm:w-[100px] font-semibold whitespace-nowrap">
                      Phone
                    </span>
                    <span className="flex-1 wrap-break-word">
                      {reservation?.hotel_information?.phone?.[0]
                        ?.phoneNumber || "N/A"}
                    </span>
                  </div>
                  <div className="flex flex-col gap-2">
                    <div className="flex gap-2">
                      <span className="w-20 sm:w-[100px] font-semibold whitespace-nowrap">
                        Check-in Time
                      </span>
                      <span>
                        {reservation?.hotel_information?.check_in_time}
                      </span>
                    </div>

                    <div className="flex gap-2">
                      <span className="w-20 sm:w-[100px] font-semibold whitespace-nowrap">
                        Check-out Time
                      </span>
                      <span>
                        {reservation?.hotel_information?.check_out_time}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div className="flex gap-2 pt-4 pb-4">
              <button
                onClick={() => setIsModalOpen(false)}
                className="flex-1 h-10 rounded-[30px] bg-core text-white font-semibold text-xs flex items-center justify-center hover:bg-core/90 transition-colors"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
