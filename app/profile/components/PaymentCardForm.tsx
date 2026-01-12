"use client";
import React, { useState, useEffect } from "react";
import creditCard0 from "@/assets/images/credit-card-0.svg";
import creditCard1 from "@/assets/images/credit-card-1.svg";
import creditCard2 from "@/assets/images/credit-card-2.svg";
import creditCard3 from "@/assets/images/credit-card-3.svg";
import creditCardCVC from "@/assets/images/CVC.svg";
import Image from "next/image";
import { AddPaymentMethodPayload } from "../types";
import {
  useAddPaymentMethod,
  useDeletePaymentMethod,
  useSavedPaymentMethods,
} from "../hooks/usePayment";
import { toast } from "sonner";
import { Checkbox } from "@/components/ui/checkbox";

// Small helper component to manage MM / YYYY input without interfering with cursor
function ExpirationInput({
  valueMonth,
  valueYear,
  onChangeMonth,
  onChangeYear,
}: {
  valueMonth: number;
  valueYear: number;
  onChangeMonth: (m: number) => void;
  onChangeYear: (y: number) => void;
}) {
  const [monthStr, setMonthStr] = useState<string>(
    valueMonth ? String(valueMonth).padStart(2, "0") : ""
  );
  const [yearStr, setYearStr] = useState<string>(
    valueYear ? String(valueYear) : ""
  );

  useEffect(() => {
    // only sync from props when the incoming numeric value differs from what's currently typed
    const propMonth = valueMonth ? String(valueMonth).padStart(2, "0") : "";

    if (propMonth !== monthStr) setMonthStr(propMonth);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [valueMonth]);

  useEffect(() => {
    const propYear = valueYear ? String(valueYear) : "";
    if (propYear !== yearStr) setYearStr(propYear);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [valueYear]);

  return (
    <div className="flex items-center gap-2">
      <input
        type="text"
        inputMode="numeric"
        maxLength={2}
        value={monthStr}
        onChange={(e) => {
          const raw = e.target.value.replace(/[^0-9]/g, "");
          const trimmed = raw.slice(0, 2);
          setMonthStr(trimmed);
          // commit only when user finished typing 2 digits
          if (trimmed.length === 2) {
            onChangeMonth(parseInt(trimmed, 10));
          }
        }}
        onBlur={() => {
          const m = monthStr ? parseInt(monthStr, 10) : 0;
          onChangeMonth(m);
        }}
        placeholder="MM"
        className="w-16 text-center focus:ring-0 focus:outline-none text-xs placeholder:text-[#7F7F93] px-2 py-1"
      />

      <span className="text-xs">/</span>

      <input
        type="text"
        inputMode="numeric"
        maxLength={4}
        value={yearStr}
        onChange={(e) => {
          const raw = e.target.value.replace(/[^0-9]/g, "");
          const trimmed = raw.slice(0, 4);
          setYearStr(trimmed);
          // commit when user typed 4 digits
          if (trimmed.length === 4) {
            onChangeYear(parseInt(trimmed, 10));
          }
        }}
        onBlur={() => {
          const y = yearStr ? parseInt(yearStr, 10) : 0;
          onChangeYear(y);
        }}
        placeholder="YYYY"
        className="w-20 text-center focus:ring-0 focus:outline-none text-xs placeholder:text-[#7F7F93] px-2 py-1"
      />
    </div>
  );
}

export const PaymentCardForm = () => {
  const [showPaymentAddForm, setShowPaymentAddForm] = useState<boolean>(false);
  const [cardNumberError, setCardNumberError] = useState<string>("");
  const [card, setCard] = useState<AddPaymentMethodPayload>({
    holder_name: "",
    card_number: "",
    expiry_month: 0,
    expiry_year: 0,
    cvv: "",
    saveCard: true,
  });
  const MAX_CARD = BigInt("4242424242424242");
  const addPaymentCard = useAddPaymentMethod();
  const { data: savedPaymentMethods } = useSavedPaymentMethods(true);
  const handleShowAddPaymentForm = () => {
    setShowPaymentAddForm((prev) => !prev);
  };
  const handleAddPaymentCard = async () => {
    if (
      !card.holder_name ||
      !card.card_number ||
      !card.expiry_month ||
      !card.expiry_year ||
      !card.cvv
    ) {
      return toast.error("Please fill in all required fields", {
        duration: 2000,
        position: "top-center",
      });
    } else {
      // final validation: ensure card number does not exceed allowed max
      try {
        const digits = (card.card_number || "").replace(/[^0-9]/g, "");
        if (digits.length === 16 && BigInt(digits) > MAX_CARD) {
          return toast.error("Card number exceeds allowed maximum", {
            duration: 2000,
            position: "top-center",
          });
        }
      } catch (err) {
        // if BigInt parsing fails, allow other validations to surface
      }
      await addPaymentCard.mutateAsync(card);
      toast.success("Payment method added successfully!", {
        duration: 2000,
        position: "top-center",
      });
      setShowPaymentAddForm(false);
    }
  };
  const handleCancelProcess = () => {
    setShowPaymentAddForm(false);
  };
  const deletePaymentCard = useDeletePaymentMethod();
  const handleDeleteCard = async (id: number) => {
    await deletePaymentCard.mutateAsync(id);
    toast.success("Payment method removed successfully!", {
      duration: 2000,
      position: "top-center",
    });
  };

  return (
    <div className="w-full max-w-xl">
      <div className="flex flex-col gap-4 p-5 rounded-[30px] bg-[#FAFAFC] text-core">
        <h2 className="text-xs font-semibold">Payment Type</h2>
        <p className="text-xs text-[#8C8CA0]">
          Add a payment method to speed up your reservation process
        </p>
        <p className="text-xs">
          Credit Card, Debit Card, AMEX, Visa, Apple Pay, PayPal
        </p>

        {savedPaymentMethods?.map((method) => (
          <div
            key={method.id}
            className="h-[60px] px-4 rounded-[30px] bg-[#E9F8F1] flex items-center justify-between text-xs"
          >
            <div className="flex gap-2">
              <Image
                alt="credit card"
                src={
                  method.brand === "visa"
                    ? creditCard0
                    : method.brand === "mastercard"
                      ? creditCard1
                      : method.brand === "amex"
                        ? creditCard3
                        : creditCard0
                }
                className="w-[23px] h-4"
              />

              <span>Ending with {method.last4}</span>
            </div>

            <div className="flex gap-4">
              <span className="font-bold text-[#463DBC]">
                {method.is_default ? "Main Card" : "Other Card"}
              </span>
              <span
                onClick={() => handleDeleteCard(method.id)}
                className="text-[#FF3636] cursor-pointer"
              >
                Remove Card
              </span>
            </div>
          </div>
        ))}

        <div className="border border-[#C9D0E7]"></div>

        {showPaymentAddForm && (
          <div>
            <h2 className="text-xs font-semibold mb-4">Payment Details</h2>

            <div className="flex flex-col rounded-[30px] overflow-hidden">
              <div className="bg-white flex flex-col gap-2 p-4 text-xs text-core border-b">
                <label className="font-semibold">Name on Card *</label>
                <input
                  value={card?.holder_name || ""}
                  onChange={(e) =>
                    setCard((prev) => ({
                      ...prev,
                      holder_name: e.target.value || "",
                    }))
                  }
                  type="text"
                  placeholder="Full Name"
                  className="focus:outline-none focus:ring-0 placeholder:text-[#7F7F93]"
                />
              </div>

              <div className="bg-white flex flex-col gap-2 p-4 text-xs text-core border-b">
                <label className="font-semibold">Card number *</label>
                <div className="flex">
                  <input
                    value={card?.card_number || ""}
                    onChange={(e) => {
                      const digits = e.target.value
                        .replace(/[^0-9]/g, "")
                        .slice(0, 16);
                      setCard((prev) => ({ ...prev, card_number: digits }));

                      if (digits.length === 16) {
                        try {
                          if (BigInt(digits) > MAX_CARD) {
                            setCardNumberError(
                              "Card number exceeds allowed maximum"
                            );
                          } else {
                            setCardNumberError("");
                          }
                        } catch (err) {
                          setCardNumberError("");
                        }
                      } else {
                        setCardNumberError("");
                      }
                    }}
                    type="text"
                    placeholder="Card Number"
                    className="w-full focus:outline-none focus:ring-0 placeholder:text-[#7F7F93]"
                  />
                  {cardNumberError && (
                    <div className="text-red-500 text-xs ml-2 self-center">
                      {cardNumberError}
                    </div>
                  )}
                  <div className="flex">
                    <Image
                      alt="credit card"
                      src={creditCard0}
                      className="w-[23px] h-4"
                    />
                    <Image
                      alt="credit card"
                      src={creditCard1}
                      className="w-[23px] h-4"
                    />
                    <Image
                      alt="credit card"
                      src={creditCard2}
                      className="w-[23px] h-4"
                    />
                    <Image
                      alt="credit card"
                      src={creditCard3}
                      className="w-[23px] h-4"
                    />
                  </div>
                </div>
              </div>

              <div className="flex bg-white">
                <div className="w-1/2 p-4 flex flex-col gap-2 border-r">
                  <label className="font-semibold text-xs">Expiration *</label>
                  <ExpirationInput
                    valueMonth={card.expiry_month}
                    valueYear={card.expiry_year}
                    onChangeMonth={(m) =>
                      setCard((prev) => ({ ...prev, expiry_month: m }))
                    }
                    onChangeYear={(y) =>
                      setCard((prev) => ({ ...prev, expiry_year: y }))
                    }
                  />
                </div>

                <div className="w-1/2 flex flex-col p-4 gap-2 relative">
                  <label className="font-semibold text-xs">CVC *</label>
                  <input
                    value={card?.cvv || ""}
                    onChange={(e) =>
                      setCard((prev) => ({
                        ...prev,
                        cvv: e.target.value || "",
                      }))
                    }
                    type="text"
                    placeholder="CVC"
                    className="focus:ring-0 focus:outline-none text-xs placeholder:text-[#7F7F93]"
                  />
                  <div className="absolute right-4 top-1/2 -translate-y-1/2">
                    <Image
                      alt="credit card"
                      src={creditCardCVC}
                      className="w-[23px] h-4"
                    />
                  </div>
                </div>
              </div>
              {/* <div className="flex items-center p-2 gap-2 mt-1">
                <Checkbox
                  id="billing-info"
                  checked={card.saveCard}
                  onCheckedChange={(checked) =>
                    setCard((prev) => ({
                      ...prev,
                      saveCard: checked as boolean,
                    }))
                  }
                />
                <label
                  htmlFor="billing-info"
                  className="text-xs font-semibold leading-none cursor-pointer"
                >
                  Save billing info for future use
                </label>
              </div> */}
            </div>
          </div>
        )}

        <div className="flex gap-4">
          <button
            onClick={
              showPaymentAddForm
                ? handleAddPaymentCard
                : handleShowAddPaymentForm
            }
            className="w-[103px] p-3 bg-core text-xs text-white rounded-[30px] flex items-center justify-center cursor-pointer"
          >
            Add Card
            {addPaymentCard.isPending && (
              <span className="ml-2 inline-block w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin" />
            )}
          </button>

          {showPaymentAddForm && (
            <button
              onClick={handleCancelProcess}
              className="text-[#FF3636] cursor-pointer text-xs"
            >
              Cancel Process
            </button>
          )}
        </div>
      </div>
    </div>
  );
};
