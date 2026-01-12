import Image from "next/image";
import { useEffect, Fragment, useState } from "react";
import { FaAddressCard, FaMoneyBill } from "react-icons/fa";
import { IoIosAdd } from "react-icons/io";
import creditCard0 from "@/assets/images/credit-card-0.svg";
import creditCard1 from "@/assets/images/credit-card-1.svg";
import creditCard2 from "@/assets/images/credit-card-2.svg";
import creditCard3 from "@/assets/images/credit-card-3.svg";
import { Checkbox } from "@/components/ui/checkbox";
import { PaymentDetailsChangeCard } from "./PaymentDetailsChangeCard";
import { getCookie } from "@/lib/cookies";
import { BookingInfo } from "../page";
import { useSession } from "next-auth/react";
import {
  CardNumberElement,
  CardExpiryElement,
  CardCvcElement,
} from "@stripe/react-stripe-js";
import { useSavedPaymentMethods } from "@/app/profile/hooks/usePayment";

const inputStyle = {
  iconColor: "#9AA3C1",
  color: "#1F1E33",
  fontWeight: "500",
  fontFamily: "Inter, 'Segoe UI', system-ui, sans-serif",
  fontSize: "15px",
  fontSmoothing: "antialiased",
  ":-webkit-autofill": {
    color: "#1F1E33",
  },
  "::placeholder": {
    color: "#AEB7D2",
  },
};

export const PaymentDetails = ({
  bookingState,
  handleBookingInfoChange,
  onCardReady,
}: {
  bookingState: BookingInfo;
  handleBookingInfoChange: (u: BookingInfo) => void;
  onCardReady?: (ready: boolean) => void;
}) => {
  const session = useSession();
  const token = session.data?.user.accessToken;
  const isLoggedIn = getCookie("access_token") || Boolean(token);

  const { data: savedPaymentMethods } = useSavedPaymentMethods(Boolean(isLoggedIn));
  const [selectedPaymentMethodId, setSelectedPaymentMethodId] = useState<number | null>(null);

  // Get card brand icon
  const getCardIcon = (brand: string) => {
    const brandLower = brand?.toLowerCase();
    if (brandLower === 'visa') return creditCard0;
    if (brandLower === 'mastercard') return creditCard1;
    if (brandLower === 'amex' || brandLower === 'american express') return creditCard2;
    return creditCard3;
  };
  console.log('selectedPaymentMethodId', savedPaymentMethods);

  useEffect(() => {
    if (!isLoggedIn) {
      handleBookingInfoChange({
        ...bookingState,
        showAddCardForm: true,
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    // Auto-select first saved payment method if available
    if (isLoggedIn && savedPaymentMethods && savedPaymentMethods.length > 0 && !selectedPaymentMethodId) {
      setSelectedPaymentMethodId(savedPaymentMethods[0].id);
      onCardReady?.(true);
      handleBookingInfoChange({
        ...bookingState,
        showAddCardForm: false,
        selectedPaymentMethodId: savedPaymentMethods[0].id,
      });
    } else if (isLoggedIn && savedPaymentMethods && savedPaymentMethods.length === 0) {
      // No saved cards, show add card form
      handleBookingInfoChange({
        ...bookingState,
        showAddCardForm: true,
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [savedPaymentMethods, isLoggedIn]);

  const showAddCardForm = bookingState.showAddCardForm;

  return (
    <Fragment>
      <h2 className="font-semibold mb-4">Payment Details</h2>
      <span className="bg-[#FCF3F3] text-[#DB4A4A] font-semibold text-xs flex items-center justify-center gap-2 w-fit py-1.5 px-3 rounded-full mb-6">
        <FaMoneyBill className="w-6 h-[13px]" />
        Cash is not available for this property
      </span>

      <div className="flex items-center gap-3 mb-4">
        <button className="bg-[#065F46] text-white h-10 w-[140px] flex items-center justify-center gap-2 rounded-full cursor-pointer">
          <FaAddressCard className="w-[22px] h-[15px]" />
          <span className="font-semibold text-xs">Card</span>
        </button>

        <button
          disabled={true}
          className="bg-[#F4F6FA] border border-[#D7DCE8] text-[#A9B0C5] h-10 w-[140px] flex items-center justify-center gap-2 rounded-full cursor-pointer"
        >
          <FaMoneyBill className="w-[22px] h-[15px]" />
          <span className="font-semibold text-xs">Cash</span>
        </button>
      </div>

      {/* user with saved credit cards */}
      {!showAddCardForm && isLoggedIn && savedPaymentMethods && savedPaymentMethods.length > 0 && (
        <>
          <div className="bg-[#F4FBF7] sm:rounded-full h-16 flex items-center justify-between px-4 mb-4 -mx-4 sm:mx-0">
            <div className="flex gap-4">
              <div className="flex flex-col gap-2">
                <span className="font-semibold text-xs">
                  {savedPaymentMethods.find(pm => pm.id === selectedPaymentMethodId)?.holder_name || 'Card holder'}
                </span>
                <span className="text-xs">
                  Ending with {savedPaymentMethods.find(pm => pm.id === selectedPaymentMethodId)?.last4 || '****'}
                </span>
              </div>

              <Image
                alt="credit card"
                src={getCardIcon(savedPaymentMethods.find(pm => pm.id === selectedPaymentMethodId)?.brand || '')}
                className="w-[46px] h-[30px]"
              />
            </div>

            <PaymentDetailsChangeCard 
              savedPaymentMethods={savedPaymentMethods}
              selectedPaymentMethodId={selectedPaymentMethodId}
              onSelectPaymentMethod={(id) => {
                setSelectedPaymentMethodId(id);
                handleBookingInfoChange({
                  ...bookingState,
                  selectedPaymentMethodId: id,
                });
              }}
              onAddNewCard={() => {
                handleBookingInfoChange({
                  ...bookingState,
                  showAddCardForm: true,
                });
              }}
            />
          </div>

          <button
            onClick={() =>
              handleBookingInfoChange({
                ...bookingState,
                showAddCardForm: true,
              })
            }
            className="flex items-center justify-center border border-[#1A255A] rounded-full h-10 px-6 mb-6 cursor-pointer"
          >
            <IoIosAdd className="size-6" />
            <span className="font-semibold text-xs">Add Card</span>
          </button>
        </>
      )}

      {/* Add new card form */}
      {showAddCardForm && (
        <>
          <div className="bg-[#F8F9FC] rounded-2xl flex flex-col mb-4">
            <input
              type="text"
              placeholder="Name on Card"
              value={bookingState.cardName}
              onChange={(e) =>
                handleBookingInfoChange({
                  ...bookingState,
                  cardName: e.target.value,
                })
              }
              className="focus:ring-0 focus:outline-none text-xs p-4 border-b border-[#D9D9D9] placeholder:text-[#7F7F93]"
            />

            <div className="relative p-4 border-b border-[#D9D9D9]">
              <CardNumberElement
                options={{ 
                  showIcon: true, 
                  style: { base: inputStyle },
                  placeholder: "Card number"
                }}
                onReady={() => onCardReady?.(true)}
              />
            </div>

            <div className="flex">
              <div className="w-1/2 p-4 border-r border-[#D9D9D9]">
                <CardExpiryElement
                  options={{ 
                    style: { base: inputStyle },
                    placeholder: "MM / YY"
                  }}
                />
              </div>

              <div className="w-1/2 p-4">
                <CardCvcElement 
                  options={{ 
                    style: { base: inputStyle },
                    placeholder: "CVV"
                  }} 
                />
              </div>
            </div>
          </div>

          {isLoggedIn && (
            <>
              <div className="flex items-center space-x-2 mb-6">
                <Checkbox 
                  id="card-details" 
                  checked={bookingState.saveCard || false}
                  onCheckedChange={(checked) =>
                    handleBookingInfoChange({
                      ...bookingState,
                      saveCard: checked as boolean,
                    })
                  }
                />
                <label
                  htmlFor="card-details"
                  className="text-xs font-semibold leading-none cursor-pointer"
                >
                  Save card details for future use
                </label>
              </div>
              {savedPaymentMethods && savedPaymentMethods.length > 0 && (
                <button
                  onClick={() => {
                    handleBookingInfoChange({
                      ...bookingState,
                      showAddCardForm: false,
                    });
                  }}
                  className="h-10 w-[100px] flex items-center justify-center rounded-full border border-core mb-6 cursor-pointer"
                >
                  <span className="font-semibold text-xs">Cancel</span>
                </button>
              )}
            </>
          )}
        </>
      )}
    </Fragment>
  );
};
