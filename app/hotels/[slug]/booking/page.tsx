"use client";
import { useParams, useRouter } from "next/navigation";
import { IoIosLock, IoIosStar, IoIosStarHalf } from "react-icons/io";
import { FaBed, FaExclamationCircle } from "react-icons/fa";
import paymenChoiceHotel from "@/assets/images/payment-hotel-choice.png";
import Image from "next/image";
// import MainFooter from "@/components/shared/MainFooter";
import { GuestDetails } from "./components/GuestDetails";
import { BillingInfo } from "./components/BillingInfo";
import { CancellationPolicySection } from "./components/CancellationPolicySection";
import { SpecialRequests } from "./components/SpecialRequests";
import { PriceBreakdown } from "./components/PriceBreakdown";
import { useCallback, useEffect, useMemo, useState } from "react";
import { HotelInfo, Room } from "@/app/search/types";
import { Button } from "@/components/ui/button";
import { useCheckout } from "@/app/reservations/hooks/useReservations";
import {
  CheckoutPayload,
  RateFlowType,
  CheckoutResponse,
} from "@/app/reservations/types";
import { useProfile } from "@/app/profile/hooks/useProfile";
import { useSession } from "next-auth/react";
import { getCookie } from "@/lib/cookies";
import { UserProfile } from "@/app/profile/types";
import { useSearch } from "@/app/search/context/SearchContext";
import { PaymentDetails } from "./components/PaymentDetails";
import {
  Elements,
  useStripe,
  useElements,
  CardNumberElement,
} from "@stripe/react-stripe-js";
import { loadStripe, Stripe } from "@stripe/stripe-js";

const stripePromise: Promise<Stripe | null> = loadStripe(
  process.env.NEXT_PUBLIC_STRIPE_PUBLIC_KEY || ""
);
/** * Represents the different types of tax data returned by the API
 */
export type TaxType = "CITY_TAX" | "info" | "comment" | "RESORT_FEE" | string;

/**
 * Base interface for common properties
 */
interface BaseTax {
  type: TaxType;
  included?: boolean;
}

/**
 * Case 1: Numeric Tax
 * Used when a specific amount is known but not included in the base price.
 */
interface NumericTax extends BaseTax {
  included: false;
  amount: number;
  currency: string;
  message?: string;
}

/**
 * Case 2: Included Tax
 * Used when the price already covers all taxes.
 */
interface IncludedTax extends BaseTax {
  type: "info";
  included: true;
  message: string;
  amount?: never;
  currency?: string; // Ensures we don't accidentally try to read a currency here
}

/**
 * Case 3 & 4: Informational/Comment Tax
 * Used for "Middle East" cases or fallbacks where amounts are unknown.
 */
interface InformationalTax extends BaseTax {
  type: "info" | "comment";
  message: string;
  amount?: never;
  currency?: string;
}

/**
 * The final Union Type to be used in your Hotel Rate interface
 */
export type HotelTax = NumericTax | IncludedTax | InformationalTax;
export type BookingInfo = {
  // guest details
  showFormTraveler: boolean;
  // guest inputs
  guestFirstName: string;
  guestLastName: string;
  guestEmail: string;
  guestPhone: string;
  // payment details
  showAddCardForm: boolean;
  selectedPaymentMethodId?: number | null;
  saveCard?: boolean;
  // card inputs
  cardName: string;
  cardNumber: string;
  cardExpiry: string;
  cardCVV: string;
  // billing
  billingCountry: string;
  billingZip: string;
  // cancellation / deal selector
  dealSelectorOpen: boolean;
  // cancellation acceptance required to proceed
  cancellationAccepted: boolean;
  // special requests
  specialRequestsOpen: boolean;
  specialRequestsText: string;
};

export interface SelectedHotel {
  hotel: HotelInfo;
  room: Room;
  selectedRate: {
    net: number;
    taxes: HotelTax[];
    pricing: {
      final_price: number;
      vendor_net: number;
    };
    currency: string;
    rooms: number;
    boardName: string;
    rateKey: string;
    adults: number;
    children: number;
    rateType: string;
    cancellationPolicies: {
      amount: string;
      from: string;
    }[];
  };
}

function BookingPageContent() {
  const params = useParams();
  const slug = params.slug as string;
  const session = useSession();
  const token = session.data?.user.accessToken;
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const stripe = useStripe();
  const elements = useElements();
  const [cardReady, setCardReady] = useState(false);
  const router = useRouter();

  const { data: profile } = useProfile(isLoggedIn);

  // Perform client-side hydration check after component mounts
  useEffect(() => {
    const loggedIn =
      (typeof window !== "undefined" && Boolean(getCookie("access_token"))) ||
      Boolean(token);
    setIsLoggedIn(loggedIn);
  }, [token]);

  console.log("User Profile:", profile);

  const [selectedHotel, setSelectedHotel] = useState<SelectedHotel>({
    hotel: {} as HotelInfo,
    room: {} as Room,
    selectedRate: {
      net: 0,
      taxes: [],
      currency: "USD",
      pricing: {
        final_price: 0,
        vendor_net: 0,
      },
      rooms: 0,
      adults: 0,
      children: 0,
      boardName: "",
      cancellationPolicies: [],
      rateKey: "",
      rateType: "",
    },
  });
  const [searchDataState, setSearchDataState] = useState({
    destination: "",
    checkIn: "",
    checkOut: "",
    guests: { adults: 0, children: 0 },
  });
  const { searchData, setReservationId } = useSearch();

  const [bookingState, setBookingState] = useState<BookingInfo>({
    showFormTraveler: false,
    guestFirstName: "",
    guestLastName: "",
    guestEmail: "",
    guestPhone: "",
    showAddCardForm: false,
    cardName: "",
    cardNumber: "",
    cardExpiry: "",
    cardCVV: "",
    billingCountry: "",
    billingZip: "",
    dealSelectorOpen: false,
    cancellationAccepted: false,
    specialRequestsOpen: false,
    specialRequestsText: "",
  });
  const [checkoutPayload, setCheckoutPayload] =
    useState<CheckoutPayload | null>(null);
  const [isProcessingPayment, setIsProcessingPayment] = useState(false);
  const [paymentError, setPaymentError] = useState<string | null>(null);

  const diffTime =
    new Date(searchDataState?.checkOut).getTime() -
    new Date(searchDataState?.checkIn).getTime();
  const nights = diffTime / (1000 * 60 * 60 * 24);

  const fromDateFormatted = new Date(
    searchDataState.checkIn
  ).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });

  const toDateFormatted = new Date(searchDataState.checkOut).toLocaleDateString(
    "en-US",
    {
      month: "short",
      day: "numeric",
      year: "numeric",
    }
  );

  useEffect(() => {
    const storedSelection = localStorage.getItem("selectedHotelRoom");
    if (storedSelection && searchData) {
      setSelectedHotel(JSON.parse(storedSelection));
      setSearchDataState(searchData);
    }
  }, [searchData]);

  console.log("Selected Hotel:", selectedHotel, slug);

  const handleBookingInfoChange = useCallback(
    (data: BookingInfo) => {
      setBookingState((prev) => ({ ...prev, ...data }));
    },
    [setBookingState]
  );

  const isPayButtonEnabled = useMemo(() => {
    if (isLoggedIn && profile) {
      return (
        (profile?.name || "").trim() !== "" &&
        (profile?.email || "").trim() !== "" &&
        bookingState.cancellationAccepted &&
        cardReady &&
        bookingState.cardName.trim() !== ""
      );
    }

    return (
      bookingState.guestFirstName.trim() !== "" &&
      bookingState.guestLastName.trim() !== "" &&
      bookingState.guestEmail.trim() !== "" &&
      bookingState.guestPhone.trim() !== "" &&
      bookingState.cardName.trim() !== "" &&
      bookingState.cancellationAccepted &&
      cardReady
    );
  }, [bookingState, isLoggedIn, profile, cardReady]);

  function formatCancellationPolicies(
    policies: { amount: string; from: string }[]
  ) {
    return policies.map((policy) => {
      const date = new Date(policy.from);

      const formatted = date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
      });

      const time = date.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
      });

      return `✓ Full Refund until ${formatted}, ${time}.`;
    });
  }

  const formatCancellationText =
    formatCancellationPolicies(
      selectedHotel?.selectedRate?.cancellationPolicies || []
    )[0] || "No cancellation policies available.";

  const checkoutMutation = useCheckout();

  // Automatically confirm payment after checkout succeeds
  useEffect(() => {
    const confirmPayment = async () => {
      if (checkoutMutation.isSuccess && checkoutPayload && stripe && elements) {
        const reservationId = (checkoutMutation.data as CheckoutResponse)
          .reservation_id;
        const clientSecret = (checkoutMutation.data as CheckoutResponse)
          .client_secret;

        setReservationId?.(reservationId as number);
        setIsProcessingPayment(true);
        setPaymentError(null);

        console.log("reservationId:", reservationId);
        console.log("clientSecret:", clientSecret);

        try {
          const cardElement = elements.getElement(CardNumberElement);
          if (!cardElement) {
            setPaymentError(
              "Card details not found. Please refresh and try again."
            );
            setIsProcessingPayment(false);
            return;
          }

          const result = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
              card: cardElement,
              billing_details: {
                name:
                  bookingState.cardName ||
                  `${checkoutPayload?.holder?.name || ""} ${checkoutPayload?.holder?.surname || ""}`.trim(),
              },
            },
          });

          if (result.error) {
            setPaymentError(
              result.error.message || "Payment failed. Please try again."
            );
            setIsProcessingPayment(false);
          } else if (result.paymentIntent?.status === "succeeded") {
            // Payment succeeded, redirect to success page
            setIsProcessingPayment(false);
            router.push(`/hotels/${slug}/booking/success`);
          } else {
            setPaymentError(`Payment status: ${result.paymentIntent?.status}`);
            setIsProcessingPayment(false);
          }
        } catch (error) {
          console.error("Payment confirmation error:", error);
          setPaymentError(
            "An error occurred during payment. Please try again."
          );
          setIsProcessingPayment(false);
        }
      }
    };

    confirmPayment();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [checkoutMutation.isSuccess, checkoutMutation.data, stripe, elements]);

  const handleConfirmBooking = () => {
    const bookingData = {
      hotel_id: selectedHotel.hotel.id,
      rooms: [
        {
          rate_key: selectedHotel.selectedRate.rateKey, // This should be replaced with actual rate key
          rate_type: selectedHotel.selectedRate.rateType,
          paxes: [
            ...Array.from({ length: selectedHotel.selectedRate.adults }).map(
              () => ({
                type: "AD" as const,
                age: 30,
              })
            ),
            ...Array.from({ length: selectedHotel.selectedRate.children }).map(
              () => ({
                type: "CH" as const,
                age: 8,
              })
            ),
          ],
          ...((selectedHotel.selectedRate.rateType as RateFlowType) ===
          "BOOKABLE"
            ? {
                net: selectedHotel.selectedRate.pricing.vendor_net,
              }
            : {}),
        },
      ],
      holder: {
        name: isLoggedIn ? profile?.name : bookingState.guestFirstName,
        surname: isLoggedIn ? profile?.name : bookingState.guestLastName,
      },
      currency: selectedHotel.room.currency,
      countryCode: "ES",
      cityCode: selectedHotel.hotel.destinationCode,
      customer_email: isLoggedIn ? profile?.email : bookingState.guestEmail,
      check_in: searchDataState.checkIn as unknown as Date,
      check_out: searchDataState.checkOut as unknown as Date,
      // client_reference: "WEB-20250301-0001", // Example reference
      remark: bookingState.specialRequestsText,
      channel: "Website",
    };
    setCheckoutPayload(bookingData as CheckoutPayload);
    checkoutMutation.mutate(bookingData as CheckoutPayload);
    console.log("Booking confirmed!", bookingData);
  };

  // Render tax section based on tax data
  const renderTaxSection = (taxes: HotelTax[] = []) => {
    if (!taxes || taxes.length === 0) return null;

    return taxes.map((tax, index) => {
      if (tax.included === false && (tax?.amount || 0) > 0) {
        return (
          <div
            key={index}
            className="flex items-center justify-between text-xs mb-4"
          >
            <span>Tax on fees (due at property)</span>
            <span>
              {tax?.currency || ""} {tax.amount}
            </span>
          </div>
        );
      }
      if (tax.type === "comment" || tax.type === "info") {
        return (
          <div key={index} className="text-xs mb-4">
            <span className="text-amber-600">{tax?.message}</span>
          </div>
        );
      }
      return null;
    });
  };

  return (
    <>
      <main>
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <h2 className="text-core text-xl font-bold my-4">
            You&apos;re at the final step!
          </h2>
          <div className="flex flex-col lg:flex-row md:gap-12 lg:gap-20">
            <div className="flex-1 text-core">
              <GuestDetails
                bookingState={bookingState}
                handleBookingInfoChange={handleBookingInfoChange}
                profile={profile as UserProfile}
              />
              <PaymentDetails
                bookingState={bookingState}
                handleBookingInfoChange={handleBookingInfoChange}
                onCardReady={setCardReady}
              />
              <BillingInfo
                bookingState={bookingState}
                handleBookingInfoChange={handleBookingInfoChange}
              />
              <CancellationPolicySection
                bookingState={bookingState}
                handleBookingInfoChange={handleBookingInfoChange}
                formatCancellationText={formatCancellationText}
                selectedHotel={selectedHotel}
              />
              <SpecialRequests
                bookingState={bookingState}
                handleBookingInfoChange={handleBookingInfoChange}
              />

              <div className="md:hidden -mx-4 mt-auto">
                <div className="p-4 bg-[#F5F7FF] text-core">
                  <h2 className="font-semibold mb-4">Price breakdown</h2>
                  <div className="flex items-center justify-between text-xs mb-4">
                    <span>
                      {selectedHotel?.selectedRate?.rooms} room x {nights} night
                      {nights > 1 ? "s" : ""}
                    </span>
                    <span>
                      {selectedHotel?.room?.currency}
                      {selectedHotel?.room?.currency === "EUR" ? "€" : "$"}{" "}
                      {selectedHotel?.selectedRate?.pricing?.final_price}
                    </span>
                  </div>
                  {renderTaxSection(selectedHotel?.selectedRate?.taxes)}
                  <div className="m-px border border-[#B1C1FF] mb-4"></div>
                  <div className="flex items-center justify-between text-xs font-bold mb-4">
                    <span>Total</span>
                    <span>
                      {selectedHotel?.room?.currency}
                      {selectedHotel?.room?.currency === "EUR" ? "€" : "$"}{" "}
                      {selectedHotel?.selectedRate?.pricing?.final_price}
                    </span>
                  </div>

                  <div className="text-xs flex items-center gap-2">
                    <FaExclamationCircle className="size-4" />
                    <div>
                      <p className="tracking-tight">
                        The total excludes what you have to pay at the property
                      </p>
                      <p className="text-[#3E51CD]">Tell me more</p>
                    </div>
                  </div>
                </div>

                <div className="p-4 bg-[#F9F4FF] text-core border-t-2 border-b-2 border-[#E9D5FF]">
                  <div className="flex items-center gap-2.5 mb-4">
                    <div className="flex gap-2">
                      <div className="p-2 rounded-full bg-core"></div>
                      <div className="p-2 rounded-full bg-core"></div>
                      <div className="p-2 rounded-full bg-white border border-core"></div>
                      <div className="p-2 rounded-full bg-white border border-core"></div>
                    </div>

                    <p className="text-xs italic">2 nights remaining </p>
                  </div>

                  <p className="text-xs font-semibold">
                    You already booked here before!
                  </p>
                  <p className="text-xs">
                    You still need 2 more nights to earn your discount.
                  </p>
                </div>

                <div className="p-4">
                  <div className="flex flex-col text-core mb-4">
                    <h3 className="font-semibold tracking-tight">
                      {selectedHotel.hotel.name ||
                        "Four Seasons by the Bay Beirut"}
                    </h3>
                    {/* <p className="text-xs">
                      Junior Corner Suite with Lounge Access
                    </p> */}
                  </div>

                  <div className="flex gap-4 mb-4">
                    <div className="w-1/2 h-[61px] bg-[#F4F6FA] rounded-lg text-[#818DB1] flex flex-col items-start gap-1 py-2 px-4">
                      <h3 className="font-semibold">Check-in</h3>
                      <p className="text-xs">{fromDateFormatted}</p>
                    </div>
                    <div className="w-1/2 h-[61px] bg-[#F4F6FA] rounded-lg text-[#818DB1] flex flex-col items-start gap-1 py-2 px-4">
                      <h3 className="font-semibold">Check-in</h3>
                      <p className="text-xs">{toDateFormatted}</p>
                    </div>
                  </div>

                  <div>
                    {isPayButtonEnabled ? (
                      <Button
                        onClick={handleConfirmBooking}
                        disabled={
                          checkoutMutation.isPending || isProcessingPayment
                        }
                        aria-disabled={
                          checkoutMutation.isPending || isProcessingPayment
                        }
                        className="bg-core h-[45px] w-full rounded-[30px] flex items-center justify-center gap-2.5 cursor-pointer disabled:cursor-not-allowed disabled:opacity-70 hover:bg-[#2E2C59]"
                      >
                        {checkoutMutation.isPending || isProcessingPayment ? (
                          <>
                            <span className="loader-border-white size-4" />
                            <span className="text-white text-xs font-bold">
                              {checkoutMutation.isPending
                                ? "Processing Checkout…"
                                : "Processing Payment…"}
                            </span>
                          </>
                        ) : (
                          <span className="text-white text-xs font-bold">
                            Pay Now
                          </span>
                        )}
                        <span className="text-white">|</span>
                        <span className="text-white text-xs">
                          {selectedHotel?.room?.currency}
                          {selectedHotel?.room?.currency === "EUR"
                            ? "€"
                            : "$"}{" "}
                          {selectedHotel?.selectedRate?.pricing.final_price}
                        </span>
                      </Button>
                    ) : (
                      <button
                        disabled
                        aria-disabled
                        className="bg-core/40 h-[45px] w-full rounded-[30px] flex items-center justify-center gap-2.5 cursor-not-allowed"
                      >
                        <span className="text-white text-xs font-bold">
                          Pay Now
                        </span>
                        {(checkoutMutation.isPending ||
                          isProcessingPayment) && (
                          <span className="loader-border-white size-4" />
                        )}
                        <span className="text-white">|</span>
                        <span className="text-white text-xs">
                          {selectedHotel?.room?.currency}
                          {selectedHotel?.room?.currency === "EUR"
                            ? "€"
                            : "$"}{" "}
                          {selectedHotel?.selectedRate?.pricing.final_price}
                        </span>
                      </button>
                    )}

                    <p className="inline-flex items-center gap-2 text-[10px] text-[#777777] tracking-tight mt-2">
                      <span>
                        <IoIosLock className="size-5" />
                      </span>
                      Your transaction and personal information are protected
                      with secure SSL encryption.
                    </p>
                  </div>
                </div>
              </div>

              <div className="hidden md:flex gap-6">
                {isPayButtonEnabled ? (
                  <Button
                    onClick={handleConfirmBooking}
                    disabled={checkoutMutation.isPending || isProcessingPayment}
                    aria-disabled={
                      checkoutMutation.isPending || isProcessingPayment
                    }
                    className="bg-core h-[45px] w-[171px] rounded-[30px] flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed disabled:opacity-70 hover:bg-[#2E2C59]"
                  >
                    {checkoutMutation.isPending || isProcessingPayment ? (
                      <>
                        <span className="loader-border-white size-4" />
                        <span className="text-white text-xs font-bold">
                          {checkoutMutation.isPending
                            ? "Checkout…"
                            : "Payment…"}
                        </span>
                      </>
                    ) : (
                      <span className="text-white text-xs font-bold">
                        Pay Now
                      </span>
                    )}
                  </Button>
                ) : (
                  <button
                    disabled
                    aria-disabled
                    className="bg-core/40 h-[45px] w-[171px] rounded-[30px] flex items-center justify-center cursor-not-allowed"
                  >
                    <span className="text-white text-xs font-bold">
                      Pay Now
                    </span>
                    {(checkoutMutation.isPending || isProcessingPayment) && (
                      <span className="loader-border-white size-4" />
                    )}
                  </button>
                )}

                <div className="flex flex-col text-core justify-center">
                  <h3 className="font-semibold text-core">
                    {selectedHotel?.room?.currency}
                    {selectedHotel?.room?.currency === "EUR" ? "€" : "$"}{" "}
                    {selectedHotel?.selectedRate?.pricing.final_price}
                  </h3>
                  {/* <p className="text-core text-xs">
                    Excluding what is due at property.{" "}
                    <span className="underline text-[#3E51CD] cursor-pointer">
                      View breakdown
                    </span>
                  </p> */}
                </div>
              </div>
            </div>

            <div className="hidden md:block flex-1">
              <div className="hidden md:block rounded-[20px] overflow-hidden mb-4 p-4 border border-[#C9D0E7] max-w-xl ml-auto relative">
                <div className="absolute inset-0 p-0.5">
                  <div
                    style={{ backgroundImage: `url(${paymenChoiceHotel.src})` }}
                    className="bg-cover bg-no-repeat size-full rounded-[20px]"
                  ></div>
                </div>

                <div className="absolute inset-0 bg-white/90 backdrop-blur-2xl"></div>

                <div className="relative rounded-[20px] w-full h-[399px] overflow-hidden">
                  <Image
                    alt="hotel choice"
                    src={selectedHotel?.hotel?.images?.[0] || paymenChoiceHotel}
                    fill
                    className="object-cover"
                  />
                </div>

                <div className="relative text-core mt-4">
                  <h2 className="font-bold">
                    {selectedHotel.hotel.name ||
                      "Four Seasons by the Bay Beirut"}
                  </h2>
                  <div className="flex">
                    {Array.from({ length: 4 }).map((_, i) => (
                      <IoIosStar key={i} className="w-4 h-4" />
                    ))}
                    <IoIosStarHalf className="w-4 h-4" />
                  </div>

                  <p className="font-normal text-sm mt-3">
                    {selectedHotel.hotel.address || ""}
                  </p>

                  {/* <div className="flex items-center justify-between mb-8">
                    <div className="flex flex-wrap items-center gap-3 mt-6 text-xs">
                      <div className="flex items-center gap-2 border border-[#A48B05] bg-[#F6F2DA] rounded-full px-2 py-0.5">
                        <div className="bg-[#FFD700] border border-[#A48B05] w-2.5 h-2.5 rounded-full" />
                        <span className="font-normal">Overall Rating 4.53</span>
                      </div>
                      <span>487 reviews</span>
                    </div>
                    <button className="bg-white h-10 w-[46px] shadow-md rounded-[22px] border-l border-t border-[#E6E8F2] flex items-center justify-center">
                      <IoMdShare className="w-5 h-5" />
                    </button>
                  </div> */}

                  <div className="flex flex-col bg-white p-4 gap-4 border border-[#C9D0E7] rounded-[20px]">
                    <h3 className="text-core font-bold text-xs">
                      Your Selection
                    </h3>

                    <div>
                      <h4 className="font-semibold">
                        {selectedHotel.room.name ||
                          "Junior Corner Suite with Lounge Access"}
                      </h4>
                    </div>

                    <div className="flex gap-2">
                      <div className="flex-1 flex items-center justify-between bg-[#F6F7F9] rounded-[10px] py-2 px-3">
                        <div className="flex flex-col gap-2">
                          <h4 className="text-sm text-[#818DB1]">Check-in</h4>
                          <p className="font-medium text-[#394A7E] text-xs">
                            {fromDateFormatted}
                          </p>
                        </div>

                        {/* <div className="flex flex-col gap-2">
                          <h4 className="text-sm text-[#818DB1]">From</h4>
                          <p className="font-medium text-[#394A7E] text-xs">
                            15:00
                          </p>
                        </div> */}
                      </div>

                      <div className="flex-1 flex items-center justify-between bg-[#F6F7F9] rounded-[10px] py-2 px-3">
                        <div className="flex flex-col gap-2">
                          <h4 className="text-sm text-[#818DB1]">Check-out</h4>
                          <p className="font-medium text-[#394A7E] text-xs">
                            {toDateFormatted}
                          </p>
                        </div>

                        {/* <div className="flex flex-col gap-2">
                          <h4 className="text-sm text-[#818DB1]">Until</h4>
                          <p className="font-medium text-[#394A7E] text-xs">
                            12:00
                          </p>
                        </div> */}
                      </div>
                    </div>

                    <p className="text-xs font-medium mb-4">
                      {selectedHotel?.selectedRate?.rooms} room(s) - {nights}{" "}
                      night(s) for {searchDataState?.guests?.adults} adult
                      {searchDataState?.guests?.children > 0
                        ? `, ${searchDataState?.guests?.children} children`
                        : ""}{" "}
                      with {selectedHotel?.room?.bedType || "standard beds"}
                    </p>

                    <p className="text-xs font-medium">
                      Based on your selection, you can:
                    </p>

                    <div className="flex items-center text-xs -mt-1">
                      <p className="flex-none w-[100px] font-bold">
                        Fit
                        <span className="ml-2 font-medium">
                          {searchDataState.guests.adults} Adult
                          {searchDataState.guests.adults > 1 ? "s" : ""}
                          {searchDataState.guests.children > 0 &&
                            `, ${searchDataState.guests.children} Child${searchDataState.guests.children !== 1 ? "ren" : ""}`}
                        </span>
                      </p>
                      {selectedHotel?.room?.bedType && (
                        <div className="flex-none flex gap-2 items-center">
                          <FaBed className="size-4" />
                          <div className="flex flex-col items-start">
                            <span className="font-medium">
                              {selectedHotel.room.bedType || "Standard Beds"}
                            </span>
                            <span className="text-[#065F46] text-xs">
                              Subject to availability
                            </span>
                          </div>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              </div>

              <div className="max-w-xl ml-auto">
                <PriceBreakdown
                  selectedHotel={selectedHotel}
                  checkInDate={searchDataState.checkIn}
                  checkOutDate={searchDataState.checkOut}
                />
              </div>
            </div>
          </div>
        </div>
      </main>
      <div className="hidden md:block py-40"></div>

      {/* Payment error display */}
      {paymentError && (
        <div className="fixed bottom-4 right-4 bg-red-500 text-white p-4 rounded-lg shadow-lg max-w-md z-50">
          <h3 className="font-bold mb-2">Payment Error</h3>
          <p className="text-sm">{paymentError}</p>
          <button
            onClick={() => setPaymentError(null)}
            className="mt-2 text-xs underline"
          >
            Dismiss
          </button>
        </div>
      )}

      {/* <MainFooter className="mb:block" /> */}
    </>
  );
}

export default function Page() {
  return (
    <Elements stripe={stripePromise}>
      <BookingPageContent />
    </Elements>
  );
}
