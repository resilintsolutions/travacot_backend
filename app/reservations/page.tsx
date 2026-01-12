"use client";

import { useState } from "react";
import { useSession } from "next-auth/react";

import emptyBox from "@/assets/images/empty-box.png";
import { cn } from "@/lib/utils";
import Image from "next/image";
import editIcon from "@/assets/images/edit-icon.png";
import { IoChatbubbleEllipses } from "react-icons/io5";
// import { IoIosAdd } from "react-icons/io";
import { ReservationType } from "./types";
import {
  useReservationDetails,
  useReservationsList,
} from "./hooks/useReservations";
import { formatDate } from "date-fns";
import { useSearchParams } from "next/navigation";
// import reservationImage1 from "@/assets/images/reserved1.png";

export default function Page() {
  const { data: session } = useSession();
  const searchParams = useSearchParams();
  const reservationId = searchParams.get("id");
  const [activeTab, setActiveTab] = useState<ReservationType>("all");
  const [selectedReservationId, setSelectedReservationId] = useState<
    string | null
  >(reservationId);

  const { data: reservationsData, isLoading: isLoadingList } =
    useReservationsList({
      type: activeTab,
      page: 1,
      limit: 20,
      userId: session?.user?.id || "",
    });

  const { data: selectedReservation, isLoading: isLoadingDetails } =
    useReservationDetails(selectedReservationId || "");

  const reservations = reservationsData?.data || [];

  return (
    <>
      <section className="mt-10">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <div className="flex flex-col md:flex-row h-auto md:h-[calc(100vh-190px)] mb-6 md:mb-0 gap-4 md:gap-0">
            <div className="w-full md:w-1/3 xl:w-1/4 flex flex-col mb-6 md:mb-0 min-h-[300px] md:min-h-0">
              <h2 className="text-xl font-bold mb-5">Reservations</h2>

              <div className="bg-[#F5F6FA] rounded-[20px] flex items-center justify-between gap-2.5 max-w-sm mb-4 p-1 overflow-x-auto lg:overflow-visible">
                <button
                  onClick={() => setActiveTab("all")}
                  className={cn(
                    "border border-transparent rounded-[30px] py-2 px-5 text-core w-24 flex items-center justify-center",
                    activeTab === "all" && "bg-core text-white"
                  )}
                >
                  <span className="font-bold text-xs ">All</span>
                </button>
                <button
                  onClick={() => setActiveTab("hotels")}
                  className={cn(
                    "border border-transparent rounded-[30px] py-2 px-5 text-core w-24 flex items-center justify-center",
                    activeTab === "hotels" && "bg-core text-white"
                  )}
                >
                  <span className="font-bold text-xs">Hotels</span>
                </button>
                <button
                  onClick={() => setActiveTab("flights")}
                  className={cn(
                    "border border-transparent rounded-[30px] py-2 px-5 text-core w-24 flex items-center justify-center",
                    activeTab === "flights" && "bg-core text-white"
                  )}
                >
                  <span className="font-bold text-xs">Flights</span>
                </button>
              </div>

              {/* <button className="w-fit mb-4 flex items-center justify-center gap-2 bg-[#F5F6FA] border border-core rounded-full py-2 px-4">
                <IoIosAdd className="w-5 h-5" />
                <span className="font-semibold text-xs text-core text-center">
                  Would you like to plan a trip?
                </span>
              </button> */}

              <div className="flex-1 flex md:flex-col gap-2.5 overflow-y-auto pb-4 md:pb-0 md:pr-2 xl:pr-4">
                {isLoadingList ? (
                  [...Array(4)].map((_, index) => (
                    <div
                      key={index}
                      className="shrink-0 group relative w-[85%] sm:w-auto max-w-sm h-52 rounded-[20px] overflow-hidden bg-[#FAFAFC] border animate-pulse"
                    >
                      <div className="w-full h-full bg-gray-300" />
                    </div>
                  ))
                ) : (reservations || [])?.length === 0 ? (
                  <div className="flex flex-col items-center justify-center text-center py-8">
                    <p className="text-sm text-core/60">
                      No reservations found
                    </p>
                  </div>
                ) : (
                  (reservations || []).map((reservationData) => (
                    <div
                      key={reservationData.reservation.id}
                      onClick={() => {
                        setSelectedReservationId(
                          reservationData.reservation.id
                        );
                      }}
                      className={cn(
                        "shrink-0 group relative w-[85%] sm:w-auto max-w-sm h-52 rounded-[20px] overflow-hidden bg-[#FAFAFC] border cursor-pointer transition-all",
                        selectedReservationId ===
                          reservationData.reservation.id &&
                          "border-2 border-core"
                      )}
                    >
                      <div className="w-full h-full bg-linear-to-br from-blue-100 to-blue-50 flex items-center justify-center">
                        <Image
                          src={reservationData.hotel.image}
                          alt={
                            reservationData.hotel.name || "Reservation Image"
                          }
                          className="w-full h-full object-cover"
                          width={300}
                          height={100}
                        />
                      </div>

                      <div className="absolute inset-0 bg-white opacity-0 rounded-2xl transition-opacity duration-300 group-hover:opacity-10" />

                      <div className="absolute bottom-0 left-0 right-0 h-20 bg-core/50 backdrop-blur-3xl">
                        <div className="relative flex items-center justify-between p-4 text-white h-full">
                          <div>
                            <h3 className="text-xs font-semibold">
                              {reservationData.hotel.name}
                            </h3>
                            <p className="text-[10px]">
                              {reservationData.reservation.board === "ROOM ONLY"
                                ? "Room Only"
                                : reservationData.reservation.board}
                            </p>
                          </div>

                          <div>
                            <div
                              className={cn(
                                "rounded-[30px] w-20 md:w-[110px] h-6 md:h-10 flex items-center justify-center border text-xs text-core capitalize",
                                reservationData.reservation.total_price
                                  .status === "confirmed" &&
                                  "bg-[#E9F8F1] border-[#E9F8F1] text-[#065F46]",
                                reservationData.reservation.total_price
                                  .status === "completed" &&
                                  "bg-[#E7EBF7] border-[#2A2F8C] text-[#2A2F8C]",
                                reservationData.reservation.total_price
                                  .status === "cancelled" &&
                                  "bg-[#F7E7E7] border-[#8C2A2A] text-[#8C2A2A]",
                                reservationData.reservation.total_price
                                  .status === "pending_payment" &&
                                  "bg-[#FFF4E5] border-[#FFB547] text-[#FF8C00]"
                              )}
                            >
                              {reservationData.reservation.total_price
                                .status === "pending_payment"
                                ? "pending"
                                : reservationData.reservation.total_price
                                    .status}
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  ))
                )}
              </div>
            </div>
            <div className="w-full md:w-2/3 xl:w-3/4 flex flex-col md:flex-row h-full">
              <div className="hidden md:block h-[90%] self-end border md:mx-4 xl:mx-8"></div>
              <div className="md:hidden w-full h-px bg-gray-300 my-4"></div>
              {activeTab === "flights" ? (
                <div className="w-full flex flex-col items-center justify-center overflow-hidden">
                  <div className="w-40 sm:w-52 md:w-64 mb-4">
                    <Image
                      alt="Empty box"
                      src={emptyBox}
                      className="w-full h-auto object-contain"
                      priority
                    />
                  </div>

                  <p className="font-semibold text-sm sm:text-xl text-core">
                    Hmm... It seems empty here
                  </p>

                  <p className="text-sm sm:text-base text-[#3E51CD]">
                    Book a stay here
                  </p>
                </div>
              ) : isLoadingDetails ? (
                <div className="w-full flex items-center justify-center">
                  <p className="text-core/60">Loading reservation details...</p>
                </div>
              ) : selectedReservation ? (
                <div
                  id="reservation-details"
                  className="w-full h-full flex flex-col bg-[#F5F6FA] rounded-[30px] overflow-hidden"
                >
                  <div
                    style={{
                      backgroundImage: `url(${selectedReservation.hotel.image})`,
                      backgroundSize: "cover",
                      backgroundPosition: "center",
                      backgroundRepeat: "no-repeat",
                    }}
                    className="relative py-4 px-4 sm:px-6 h-auto sm:h-[126px] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-white rounded-t-[30px]"
                  >
                    <div className="absolute inset-0 bg-core/50 backdrop-blur-2xl"></div>
                    <div className="flex flex-col z-10">
                      <h2 className="font-semibold text-[17px] mb-2">
                        {selectedReservation.hotel.name}
                      </h2>
                      <p className="text-xs">
                        {selectedReservation.reservation.room_type}
                      </p>
                      <p className="text-xs">
                        {formatDate(
                          new Date(selectedReservation.hotel.check_in),
                          "MMM dd, yyyy"
                        )}{" "}
                        -{" "}
                        {formatDate(
                          new Date(selectedReservation.hotel.check_out),
                          "MMM dd, yyyy"
                        )}
                      </p>
                    </div>

                    <div
                      className={cn(
                        "rounded-[30px] w-[110px] h-10 flex items-center justify-center border text-xs z-10 capitalize",
                        selectedReservation.reservation.total_price.status ===
                          "active" &&
                          "bg-[#E9F8F1] border-[#E9F8F1] text-[#065F46]",
                        selectedReservation.reservation.total_price.status ===
                          "completed" &&
                          "bg-[#E7EBF7] border-[#2A2F8C] text-[#2A2F8C]",
                        selectedReservation.reservation.total_price.status ===
                          "cancelled" &&
                          "bg-[#F7E7E7] border-[#8C2A2A] text-[#8C2A2A]"
                      )}
                    >
                      {selectedReservation.reservation.total_price.status}
                    </div>
                  </div>

                  <div className="flex-1 min-h-0 overflow-y-auto text-xs text-core px-4 sm:px-6 py-4">
                    <h3 className="font-bold mb-3 text-sm">Guest Details</h3>

                    <div className="max-w-xl flex flex-col gap-3 sm:gap-0 sm:flex-row sm:items-start sm:justify-between mb-6">
                      <div className="w-full sm:w-fit flex flex-col gap-2">
                        <div className="flex gap-2">
                          <span className="w-20 sm:w-[100px] font-semibold">
                            Name
                          </span>
                          <span className="flex-1 wrap-break-word">
                            {selectedReservation.guest.name || "N/A"}
                          </span>
                        </div>
                        {/* <div className="flex gap-2">
                          <span className="w-20 sm:w-[100px] font-semibold">
                            Phone
                          </span>
                          <span className="flex-1 wrap-break-word">
                            {selectedReservation.guest.phone}
                          </span>
                        </div> */}
                      </div>

                      <div className="w-full sm:w-fit flex flex-col gap-2">
                        <div className="flex gap-2">
                          <span className="w-20 sm:w-[100px] font-semibold">
                            Email
                          </span>
                          <span className="flex-1 wrap-break-word">
                            {selectedReservation.guest.email || "N/A"}
                          </span>
                        </div>
                        <div className="flex gap-2">
                          <span className="w-20 sm:w-[100px] font-semibold">
                            Guests
                          </span>
                          <span className="flex-1 wrap-break-word">
                            {selectedReservation.guest.guests}
                          </span>
                        </div>
                      </div>
                    </div>

                    <h3 className="font-bold mb-3 text-sm">
                      Reservation Details
                    </h3>

                    <div className="max-w-xl flex flex-col gap-3 sm:gap-0 sm:flex-row sm:items-start sm:justify-between mb-4">
                      <div className="w-full sm:w-fit flex flex-col gap-2">
                        <div className="flex gap-2">
                          <span className="w-20 sm:w-[100px] font-semibold">
                            Hotel
                          </span>
                          <span className="flex-1 wrap-break-word">
                            {selectedReservation.hotel.name}
                          </span>
                        </div>
                        <div className="flex gap-2">
                          <span className="w-20 sm:w-[100px] font-semibold">
                            Check-in
                          </span>
                          <span className="flex-1 wrap-break-word">
                            {formatDate(
                              new Date(selectedReservation.hotel.check_in),
                              "MMM dd, yyyy"
                            )}
                          </span>
                        </div>
                        <div className="flex gap-2">
                          <span className="w-20 sm:w-[100px] font-semibold">
                            Board
                          </span>
                          <span className="flex-1 wrap-break-word">
                            ✔{" "}
                            {selectedReservation.reservation.board ===
                            "ROOM ONLY"
                              ? "Room Only"
                              : selectedReservation.reservation.board}
                          </span>
                        </div>
                      </div>

                      <div className="w-full sm:w-auto flex flex-col gap-2">
                        <div className="flex gap-2">
                          <span className="w-20 sm:w-[100px] font-semibold">
                            Room Type
                          </span>
                          <span className="flex-1 sm:flex-none wrap-break-word">
                            {selectedReservation.reservation.room_type}
                          </span>
                        </div>
                        <div className="flex gap-2">
                          <span className="w-20 sm:w-[100px] font-semibold">
                            Check-out
                          </span>
                          <span className="flex-1 sm:flex-none wrap-break-word">
                            {formatDate(
                              new Date(selectedReservation.hotel.check_out),
                              "MMM dd, yyyy"
                            )}
                          </span>
                        </div>
                        <div className="flex gap-2">
                          <span className="w-20 sm:w-[100px] font-semibold">
                            Nights
                          </span>
                          <span className="flex-1 sm:flex-none wrap-break-word">
                            {selectedReservation.hotel.nights}
                          </span>
                        </div>
                      </div>
                    </div>

                    <div className="text-core text-xs bg-[#E5E9FF] flex flex-col sm:flex-row gap-2 sm:gap-4 p-4 rounded-lg">
                      <h4 className="font-semibold">Total Price</h4>
                      <span>
                        {selectedReservation.reservation.total_price.currency}{" "}
                        {selectedReservation.reservation.total_price.amount}
                      </span>
                      <span>
                        ({selectedReservation.reservation.total_price.status})
                      </span>
                    </div>

                    <div className="px-0 sm:px-0 py-4 text-xs text-core">
                      <h3 className="font-bold mb-3 text-sm">
                        Hotel Information
                      </h3>
                      <div className="max-w-xl">
                        <div className="w-full flex flex-col gap-3">
                          <div className="flex gap-2">
                            <span className="w-20 sm:w-[100px] font-semibold whitespace-nowrap">
                              Address
                            </span>
                            <span className="flex-1 wrap-break-word">
                              {selectedReservation.hotel_information.address}
                            </span>
                          </div>
                          <div className="flex gap-2">
                            <span className="w-20 sm:w-[100px] font-semibold whitespace-nowrap">
                              Phone
                            </span>
                            <span className="flex-1 wrap-break-word">
                              {selectedReservation.hotel_information.phone?.[0]
                                ?.phoneNumber || "N/A"}
                            </span>
                          </div>
                          <div className="flex flex-col gap-2">
                            <div className="flex gap-2">
                              <span className="w-20 sm:w-[100px] font-semibold whitespace-nowrap">
                                Check-in Time
                              </span>
                              <span>
                                {
                                  selectedReservation.hotel_information
                                    .check_in_time
                                }
                              </span>
                            </div>

                            <div className="flex gap-2">
                              <span className="w-20 sm:w-[100px] font-semibold whitespace-nowrap">
                                Check-out Time
                              </span>
                              <span>
                                {
                                  selectedReservation.hotel_information
                                    .check_out_time
                                }
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div className="px-0 sm:px-0 py-4 text-xs">
                      <h3 className="font-bold mb-3 text-sm">Refunds</h3>
                      <ul className="list-disc pl-4 space-y-2 text-xs">
                        <li>
                          If you are eligible for a refund, it will be processed
                          according to the{" "}
                          <span className="underline">hotel&apos;s rules</span>.
                        </li>
                        <li>
                          Refunds may take up to 5 to 7 business days to appear
                          on your account, depending on the payment method and
                          banking processes.
                        </li>
                      </ul>
                    </div>

                    <div className="flex flex-col gap-4 p-4 text-core border-t">
                      <h2 className="font-bold text-sm sm:text-[17px]">
                        Having problems?
                      </h2>
                      <p className="text-xs sm:text-sm">
                        You can reach out to us here or modify the reservation.
                      </p>
                      <div className="flex flex-col gap-2">
                        <button className="bg-white text-xs sm:text-[10px] border border-core rounded-[30px] h-9 sm:h-10 w-full sm:w-[190px] flex items-center justify-center gap-2 hover:bg-core/5 transition-colors">
                          <Image
                            alt="edit icon"
                            src={editIcon}
                            className="w-3 h-3 sm:w-4 sm:h-4"
                          />
                          <span>Modify Reservation</span>
                        </button>
                        <button className="bg-white text-xs sm:text-[10px] border border-core rounded-[30px] h-9 sm:h-10 w-full sm:w-[190px] flex items-center justify-center gap-2 hover:bg-core/5 transition-colors">
                          <IoChatbubbleEllipses className="w-3 h-3 sm:w-4 sm:h-4" />
                          <span>Contact Support</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              ) : (
                <div className="w-full flex flex-col items-center justify-center overflow-hidden">
                  <div className="w-40 sm:w-52 md:w-64 mb-4">
                    <Image
                      alt="Empty box"
                      src={emptyBox}
                      className="w-full h-auto object-contain"
                      priority
                    />
                  </div>

                  <p className="font-semibold text-sm sm:text-xl text-core">
                    Hmm... It seems empty here
                  </p>

                  <p className="text-sm sm:text-base text-[#3E51CD]">
                    Select a reservation from the list to view its details.
                  </p>
                </div>
              )}
            </div>
          </div>
        </div>
      </section>
    </>
  );
}
