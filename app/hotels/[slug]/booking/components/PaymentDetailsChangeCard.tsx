import { cn } from "@/lib/utils";
import Image from "next/image";
import { Fragment, useEffect, useState } from "react";
import { IoIosAdd } from "react-icons/io";
import { IoClose } from "react-icons/io5";
import creditCard0 from "@/assets/images/credit-card-0.svg";
import creditCard1 from "@/assets/images/credit-card-1.svg";
import creditCard2 from "@/assets/images/credit-card-2.svg";
import creditCard3 from "@/assets/images/credit-card-3.svg";

import Modal from "@/components/shared/Modal";
import { SavedPaymentMethod } from "@/app/profile/types";

interface PaymentDetailsChangeCardProps {
  savedPaymentMethods?: SavedPaymentMethod[];
  selectedPaymentMethodId: number | null;
  onSelectPaymentMethod: (id: number) => void;
  onAddNewCard: () => void;
}

export const PaymentDetailsChangeCard = ({
  savedPaymentMethods = [],
  selectedPaymentMethodId,
  onSelectPaymentMethod,
  onAddNewCard,
}: PaymentDetailsChangeCardProps) => {
  const [isOpen, setIsOpen] = useState<boolean>(false);
  const [tabs, setTabs] = useState<"my-cards" | "new-card">("my-cards");
  const [isMobile, setIsMobile] = useState<boolean | null>(null);

  // Get card brand icon
  const getCardIcon = (brand: string) => {
    const brandLower = brand?.toLowerCase();
    if (brandLower === 'visa') return creditCard0;
    if (brandLower === 'mastercard') return creditCard1;
    if (brandLower === 'amex' || brandLower === 'american express') return creditCard2;
    return creditCard3;
  };

  const hasSavedCards = savedPaymentMethods.length > 0;

  useEffect(() => {
    const handleResize = () => {
      setIsMobile(window.innerWidth < 768); // Tailwind's `md` breakpoint
    };

    handleResize(); // run once on mount

    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  useEffect(() => {
    const scrollBarWidth =
      window.innerWidth - document.documentElement.clientWidth;

    if (isOpen) {
      document.body.classList.add("overflow-hidden");
      document.body.style.paddingRight = `${scrollBarWidth}px`;
    } else {
      document.body.classList.remove("overflow-hidden");
      document.body.style.paddingRight = "";
    }

    return () => {
      document.body.classList.remove("overflow-hidden");
      document.body.style.paddingRight = "";
    };
  }, [isOpen]);

  const onClose = () => {
    setIsOpen(false);
  };

  return (
    <Fragment>
      <button
        onClick={() => setIsOpen(true)}
        className="font-bold text-xs text-[#3E51CD] h-[35px] px-5 cursor-pointer"
      >
        Change
      </button>

      {isMobile ? (
        <>
          <div
            className={cn(
              "fixed inset-0 bg-core/50 z-40 transition-opacity duration-300 ease-in-out",
              isOpen ? "opacity-100" : "opacity-0 pointer-events-none"
            )}
            onClick={onClose}
          />

          <div
            className={cn(
              "fixed bottom-0 left-0 right-0 z-50 bg-white h-[calc(100vh-20rem)] rounded-t-3xl flex flex-col transition-transform duration-500 ease-in-out",
              isOpen ? "translate-y-0" : "translate-y-full"
            )}
          >
            {/* Header */}
            <div className="flex items-center gap-2 p-4 border-b">
              <button
                onClick={() => setTabs("my-cards")}
                className={cn(
                  "h-10 w-[140px] flex items-center justify-center text-core bg-[#F7F7FF] rounded-[30px]",
                  tabs === "my-cards" && "bg-core text-white"
                )}
              >
                <span className="font-semibold text-xs">My Cards</span>
              </button>
              <button
                onClick={() => setTabs("new-card")}
                className={cn(
                  "h-10 w-[140px] flex items-center justify-center text-core bg-[#F7F7FF] rounded-[30px]",
                  tabs === "new-card" && "bg-core text-white"
                )}
              >
                <IoIosAdd className="size-6" />
                <span className="text-xs font-semibold">Add new card</span>
              </button>

              <button onClick={onClose} className="ml-auto">
                <IoClose className="text-2xl" />
              </button>
            </div>

            {/* Content */}
            <div className="flex-1 overflow-y-auto">
              {tabs === "my-cards" && !hasSavedCards && (
                <div className="bg-[#F7F7FF] h-full w-full flex flex-col items-center justify-center text-core rounded-lg">
                  <p className="font-semibold mb-4">It seems lonely here...</p>
                  <button
                    onClick={() => setTabs("new-card")}
                    className="text-[#3E51CD] underline cursor-pointer text-xs"
                  >
                    Add a card
                  </button>
                </div>
              )}

              {tabs === "my-cards" && hasSavedCards && (
                <div className="h-full flex flex-col">
                  {savedPaymentMethods.map((paymentMethod) => {
                    const isSelected = paymentMethod.id === selectedPaymentMethodId;
                    return (
                      <div key={paymentMethod.id} className="bg-[#F7F7FF] flex gap-4 p-4 text-xs text-core border-b border-white">
                        <div className="flex flex-col gap-1">
                          <h3 className="font-semibold">
                            {paymentMethod.holder_name || 'Card holder'}
                          </h3>
                          <p className="tracking-tight">
                            Ending with {paymentMethod.last4 || '****'}
                          </p>
                        </div>
                        <div className="flex-1 flex items-center justify-between">
                          <Image
                            alt="card"
                            src={getCardIcon(paymentMethod.brand || '')}
                            className="h-[22px] w-[34px]"
                          />
                          {isSelected ? (
                            <span className="bg-[#C8FFC5] rounded-[20px] border border-[#2A8C3C] text-[#23911D] text-xs w-[90px] h-[35px] flex items-center justify-center">
                              Current
                            </span>
                          ) : (
                            <button
                              onClick={() => {
                                onSelectPaymentMethod(paymentMethod.id);
                                onClose();
                              }}
                              className="text-[#3E51CD] text-xs font-bold w-[90px] h-[35px] flex items-center justify-center cursor-pointer"
                            >
                              Select
                            </button>
                          )}
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}

              {tabs === "new-card" && (
                <div className="h-full flex flex-col items-center justify-center">
                  <p className="text-core text-sm font-semibold mb-2">
                    Add a new card
                  </p>
                  <p className="text-core text-xs mb-4 text-center px-4">
                    Please close this dialog and use the form below to add a new card
                  </p>
                  <button
                    onClick={() => {
                      onAddNewCard();
                      onClose();
                    }}
                    className="bg-core rounded-[30px] h-10 px-6 flex items-center justify-center"
                  >
                    <span className="text-xs font-bold text-white">
                      Add new card
                    </span>
                  </button>
                </div>
              )}
            </div>
          </div>
        </>
      ) : (
        <Modal
          isOpen={isOpen}
          onClose={onClose}
          className="max-w-sm w-full h-[510px] overflow-y-auto"
          title={
            <div className="flex items-center gap-2">
              <button
                onClick={() => setTabs("my-cards")}
                className={cn(
                  "h-10 w-[140px] flex items-center justify-center text-core bg-[#F7F7FF] rounded-[30px]",
                  tabs === "my-cards" && "bg-core text-white"
                )}
              >
                <span className="font-semibold text-xs">My Cards</span>
              </button>
              <button
                onClick={() => setTabs("new-card")}
                className={cn(
                  "h-10 w-[140px] flex items-center justify-center text-core bg-[#F7F7FF] rounded-[30px]",
                  tabs === "new-card" && "bg-core text-white"
                )}
              >
                <IoIosAdd className="size-6" />
                <span className="text-xs font-semibold">Add new card</span>
              </button>
            </div>
          }
        >
          {tabs === "my-cards" && !hasSavedCards && (
            <div className="bg-[#F7F7FF] h-[400px] w-full flex flex-col items-center justify-center text-core rounded-lg">
              <p className="font-semibold mb-4">It seems lonely here...</p>
              <button
                onClick={() => setTabs("new-card")}
                className="text-[#3E51CD] underline cursor-pointer text-xs"
              >
                Add a card
              </button>
            </div>
          )}

          {tabs === "my-cards" && hasSavedCards && (
            <div className="-mx-4 -mt-4 h-[400px] flex flex-col overflow-y-auto">
              {savedPaymentMethods.map((paymentMethod) => {
                const isSelected = paymentMethod.id === selectedPaymentMethodId;
                return (
                  <div key={paymentMethod.id} className="bg-[#F7F7FF] flex gap-4 p-4 text-xs text-core border-b border-white">
                    <div className="flex flex-col gap-1">
                      <h3 className="font-semibold">
                        {paymentMethod.holder_name || 'Card holder'}
                      </h3>
                      <p className="tracking-tight">
                        Ending with {paymentMethod.last4 || '****'}
                      </p>
                    </div>
                    <div className="flex-1 flex items-center justify-between">
                      <Image
                        alt="card"
                        src={getCardIcon(paymentMethod.brand || '')}
                        className="h-[22px] w-[34px]"
                      />
                      {isSelected ? (
                        <span className="bg-[#C8FFC5] rounded-[20px] border border-[#2A8C3C] text-[#23911D] text-xs w-[90px] h-[35px] flex items-center justify-center">
                          Current
                        </span>
                      ) : (
                        <button
                          onClick={() => {
                            onSelectPaymentMethod(paymentMethod.id);
                            onClose();
                          }}
                          className="text-[#3E51CD] text-xs font-bold w-[90px] h-[35px] flex items-center justify-center cursor-pointer"
                        >
                          Select
                        </button>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          )}

          {tabs === "new-card" && (
            <div className="h-[400px] flex flex-col items-center justify-center">
              <p className="text-core text-sm font-semibold mb-2">
                Add a new card
              </p>
              <p className="text-core text-xs mb-4 text-center px-4">
                Please close this dialog and use the form below to add a new card
              </p>
              <button
                onClick={() => {
                  onAddNewCard();
                  onClose();
                }}
                className="bg-core rounded-[30px] h-10 px-6 flex items-center justify-center"
              >
                <span className="text-xs font-bold text-white">
                  Add new card
                </span>
              </button>
            </div>
          )}
        </Modal>
      )}
    </Fragment>
  );
};
