import { Checkbox } from "@/components/ui/checkbox";
import { cn } from "@/lib/utils";
import { useEffect, useState } from "react";
import { IoClose } from "react-icons/io5";
import { SelectedHotel } from "../page";
import { formatDate } from "date-fns";
import { FaCheck } from "react-icons/fa";

export const MobileDealSelector = ({
  isOpen,
  onClose,
  selectedHotel,
}: {
  isOpen: boolean;
  onClose: () => void;
  selectedHotel: SelectedHotel;
}) => {
  const [selectedIndex, setSelectedIndex] = useState<number | null>(null);
  const [viewport, setViewport] = useState<
    "mobile" | "tablet" | "desktop" | null
  >(null);

  // Pre-select the currently chosen rate (selectedHotel.selectedRate) when dialog opens
  useEffect(() => {
    if (!isOpen) return;
    const rates = selectedHotel?.room?.hb_raw?.rates;
    if (!rates || !rates.length) return;

    const currentRateKey = selectedHotel?.selectedRate?.rateKey;
    const matchedIndex = rates.findIndex(
      (rate: { rateKey?: string }) => rate.rateKey === currentRateKey
    );

    const defaultIndex = matchedIndex >= 0 ? matchedIndex : 0;
    setSelectedIndex(defaultIndex);
  }, [isOpen, selectedHotel]);

  useEffect(() => {
    if (selectedIndex !== null && selectedIndex !== undefined) {
      const selectedRoomRate =
        selectedHotel?.room?.hb_raw?.rates[selectedIndex ?? 0];
      const existingSelection = JSON.parse(
        localStorage.getItem("selectedHotelRoom") || "{}"
      );
      localStorage.setItem(
        "selectedHotelRoom",
        JSON.stringify({
          ...existingSelection,
          selectedRate: selectedRoomRate,
        })
      );
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedIndex]);

  useEffect(() => {
    const getViewport = () => {
      // Prefer matchMedia for reliable breakpoint detection, fallback to innerWidth
      const isDesktop = window.matchMedia?.("(min-width: 1100px)").matches;
      const isTablet = window.matchMedia?.(
        "(min-width: 640px) and (max-width: 1099px)"
      ).matches;
      const isMobile = window.matchMedia?.("(max-width: 639px)").matches;

      if (isDesktop) return "desktop" as const;
      if (isTablet) return "tablet" as const;
      if (isMobile) return "mobile" as const;

      const width = window.innerWidth;
      if (width >= 1100) return "desktop" as const;
      if (width >= 640) return "tablet" as const;
      return "mobile" as const;
    };

    const handleResize = () => {
      setViewport(getViewport());
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

  if (viewport === null) return null;

  const isMobile = viewport === "mobile";
  const isTablet = viewport === "tablet";
  const isDesktop = viewport === "desktop";

  return (
    <>
      <div
        className={cn(
          "fixed inset-0 bg-core/50 z-40 transition-opacity duration-300 ease-in-out",
          isOpen ? "opacity-100" : "opacity-0 pointer-events-none"
        )}
        onClick={onClose}
      />

      {isMobile ? (
        <div
          className={cn(
            "fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-3xl flex flex-col transition-transform duration-500 ease-in-out",
            "max-h-[85vh] h-auto w-full",
            isOpen ? "translate-y-0 opacity-100" : "translate-y-full opacity-0"
          )}
        >
          <div className="shrink-0 p-4 border-b">
            <div className="flex items-start justify-between gap-3 text-core text-sm">
              <div className="flex flex-col min-w-0">
                <h4 className="font-semibold leading-tight">
                  Don&apos;t get overwhelmed.
                </h4>
                <p className="text-xs text-gray-600">
                  We already chose the top {selectedHotel?.room?.hb_raw?.rates.length}!
                </p>
              </div>
              <button onClick={onClose} className="cursor-pointer shrink-0">
                <IoClose className="text-2xl" />
              </button>
            </div>
          </div>

          <div className="flex-1 overflow-y-auto overscroll-contain p-4 space-y-4 pb-[max(env(safe-area-inset-bottom),16px)]">
            {selectedHotel?.room?.hb_raw?.rates.map((rate, index) => {
              const cancellationDate = formatDate(
                new Date(rate.cancellationPolicies[0]?.from),
                "dd/MM/yy"
              );

              const cancellationTime = formatDate(
                new Date(rate.cancellationPolicies[0]?.from),
                "HH:mm"
              );
              return (
                <div
                  key={index}
                  className={cn(
                    "shrink-0 cursor-pointer rounded-[20px] border p-4 w-full transition",
                    "min-w-0 overflow-visible", // allow natural height growth
                    index === 0 ? "border-[#8FD8B4]" : "border-[#C9D0E7]",
                    selectedIndex === index
                      ? index === 0
                        ? "bg-[#F4FBF7]" // green for first card
                        : "bg-[#F6F7FF]" // blue for other selected cards
                      : "bg-white" // default background
                  )}
                  onClick={() => setSelectedIndex(index)}
                >
                  <div className="flex flex-wrap items-start gap-3 justify-between mb-4">
                    <div className="flex items-start gap-2 min-w-0 flex-1">
                      <Checkbox
                        id={`deal-${index}`}
                        checked={selectedIndex === index}
                        onCheckedChange={() => setSelectedIndex(index)}
                        className="rounded-full mt-0.5"
                      />
                      <label
                        htmlFor={`deal-${index}`}
                        className="text-core leading-snug cursor-pointer flex flex-col min-w-0"
                      >
                        <span className="font-medium text-[16px] truncate">
                          {rate.boardName === "BED AND BREAKFAST"
                            ? "Breakfast Included"
                            : rate.boardName === "ROOM ONLY"
                              ? "Room Only"
                              : rate.boardName}
                        </span>
                      </label>
                    </div>
                  </div>

                  <div className="mb-2 text-xs">
                    <p
                      className={cn(
                        "flex items-start gap-2",
                        selectedIndex === index
                          ? "text-[#065F46]"
                          : "text-[#107567]"
                      )}
                    >
                      <span className="text-sm">
                        <FaCheck className="w-4 h-4" />
                      </span>
                      <span className="flex-1 leading-snug">
                        Free Cancellation until{" "}
                        <span className="font-bold">
                          {cancellationDate} before {cancellationTime}
                        </span>
                        .
                      </span>
                    </p>
                  </div>
                  {rate.boardName === "BED AND BREAKFAST" && (
                    <div className="mb-4 text-xs">
                      <p
                        className={cn(
                          "flex items-start gap-2",
                          selectedIndex === index
                            ? "text-[#065F46]"
                            : "text-[#107567]"
                        )}
                      >
                        <span className="text-sm">
                          <FaCheck className="w-4 h-4" />
                        </span>
                        <span className="flex-1 leading-snug">
                          Breakfast Included
                        </span>
                      </p>
                    </div>
                  )}
                  <div className="mt-auto pt-2 border-t border-gray-100 flex justify-between items-end gap-3">
                    <div className="flex flex-col min-w-0 flex-1">
                      <span className="text-xs font-medium text-gray-500 mb-0.5">
                        Total Stay Price:
                      </span>
                      <div
                        className={cn(
                          // Prevent overflow at all costs
                          "font-semibold text-[17px] max-w-full min-w-0 overflow-hidden",
                          "wrap-break-word break-all hyphens-auto",
                          selectedIndex === index
                            ? "text-[#065F46]"
                            : "text-gray-900"
                        )}
                      >
                        <span className="block max-w-full wrap-break-word break-all hyphens-auto">
                          {selectedHotel?.room?.currency} {rate.pricing.final_price}
                        </span>
                      </div>
                    </div>
                    <div className="flex flex-col items-end flex-none">
                      <span className="text-xs text-gray-400 mb-0.5">
                        Taxes Incl.
                      </span>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>

          <div className="shrink-0 p-4 border-t bg-white">
            <button
              onClick={onClose}
              className="bg-[#2D2A7B] w-full h-10 rounded-[20px] flex items-center justify-center hover:bg-[#252167] transition-colors"
            >
              <span className="text-white font-bold">Change</span>
            </button>

            <p className="text-xs text-core text-center mt-1">
              You won&apos;t be charged yet
            </p>
          </div>
        </div>
      ) : (
        <div
          className={cn(
            "fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50 w-full mx-4 transition-all duration-500 ease-in-out",
            isDesktop || isTablet ? "max-w-4xl" : "max-w-[600px]",
            isDesktop || isTablet ? "max-w-[520px]" : "max-w-[600px]",
            isOpen
              ? "opacity-100 scale-100"
              : "opacity-0 scale-95 pointer-events-none"
          )}
        >
          <div className="rounded-[20px] bg-white text-core flex flex-col shadow-2xl overflow-hidden max-h-[90vh]">
            <div className="shrink-0 flex items-center justify-between p-4 md:p-5 border-b">
              <div className="flex flex-col text-sm md:text-base">
                <h4 className="font-semibold">Don&apos;t get overwhelmed.</h4>
                <p className="text-xs md:text-sm">
                  We already chose the top{" "}
                  {selectedHotel?.room?.hb_raw?.rates?.length}!
                </p>
              </div>

              <button
                onClick={onClose}
                className="cursor-pointer hover:opacity-70 transition-opacity"
              >
                <IoClose className="text-2xl" />
              </button>
            </div>

            <div
              className={cn(
                "flex-1 overflow-y-auto overscroll-contain p-4",
                // Force single column in desktop dialog for narrow width
                "grid grid-cols-1 gap-4"
              )}
            >
              {selectedHotel?.room?.hb_raw?.rates.map((rate, index) => {
                const cancellationDate = formatDate(
                  new Date(rate.cancellationPolicies[0]?.from),
                  "dd/MM/yy"
                );

                const cancellationTime = formatDate(
                  new Date(rate.cancellationPolicies[0]?.from),
                  "HH:mm"
                );
                return (
                  <div
                    key={index}
                    className={cn(
                      "shrink-0 cursor-pointer rounded-[20px] border p-4 w-full transition-all",
                      "min-w-0 overflow-visible",
                      selectedIndex === index
                        ? "border-[#8FD8B4] bg-[#F4FBF7]"
                        : "border-[#C9D0E7] bg-white hover:border-[#A0AED7]"
                    )}
                    onClick={() => setSelectedIndex(index)}
                  >
                    <div className="flex flex-wrap items-start gap-3 justify-between mb-4">
                      <div className="flex items-start gap-2 min-w-0 flex-1">
                        <Checkbox
                          id={`deal-desktop-${index}`}
                          checked={selectedIndex === index}
                          onCheckedChange={() => setSelectedIndex(index)}
                          className="rounded-full mt-0.5"
                        />
                        <label
                          htmlFor={`deal-desktop-${index}`}
                          className="text-core leading-snug cursor-pointer flex flex-col min-w-0 overflow-hidden"
                        >
                          <span className="font-medium text-[16px] truncate wrap-break-word">
                            {rate.boardName}
                          </span>
                          {rate.boardCode && (
                            <span className="text-xs text-gray-500 truncate wrap-break-word">
                              {rate.boardCode}
                            </span>
                          )}
                        </label>
                      </div>
                    </div>

                    <div className="mb-2 text-xs">
                      <p
                        className={cn(
                          "flex items-start gap-2 wrap-break-word",
                          selectedIndex === index
                            ? "text-[#065F46]"
                            : "text-[#107567]"
                        )}
                      >
                        <span className="text-sm">
                          <FaCheck className="w-4 h-4" />
                        </span>
                        <span className="flex-1 leading-snug wrap-break-word">
                          Free Cancellation until{" "}
                          <span className="font-bold">
                            {cancellationDate} before {cancellationTime}
                          </span>
                          .
                        </span>
                      </p>
                    </div>

                    {rate.boardName === "BED AND BREAKFAST" && (
                      <div className="mb-4 text-xs">
                        <p
                          className={cn(
                            "flex items-start gap-2",
                            selectedIndex === index
                              ? "text-[#065F46]"
                              : "text-[#107567]"
                          )}
                        >
                          <span className="text-sm">
                            <FaCheck className="w-4 h-4" />
                          </span>
                          <span className="flex-1 leading-snug">
                            Breakfast Included
                          </span>
                        </p>
                      </div>
                    )}

                    <div className="mt-auto pt-2 border-t border-gray-100 flex justify-between items-end gap-3">
                      <div className="flex flex-col min-w-0 flex-1">
                        <span className="text-xs font-medium text-gray-500 mb-0.5">
                          Total Stay Price:
                        </span>
                        <div
                          className={cn(
                            // Prevent overflow at all costs
                            "font-semibold text-[17px] max-w-full min-w-0 overflow-hidden",
                            "wrap-break-word break-all hyphens-auto",
                            selectedIndex === index
                              ? "text-[#065F46]"
                              : "text-gray-900"
                          )}
                        >
                          <span className="block max-w-full wrap-break-word break-all hyphens-auto">
                            {selectedHotel?.room?.currency} {rate.pricing.final_price}
                          </span>
                        </div>
                      </div>
                      <div className="flex flex-col items-end flex-none">
                        <span className="text-xs text-gray-400 mb-0.5">
                          Taxes Incl.
                        </span>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>

            <div className="shrink-0 flex flex-col items-center px-4 py-4 border-t bg-white">
              <button
                onClick={onClose}
                className="bg-[#2D2A7B] w-full h-10 rounded-[20px] flex items-center justify-center cursor-pointer hover:bg-[#252167] transition-colors"
              >
                <span className="text-white font-bold text-sm">Change</span>
              </button>

              <p className="text-xs text-core mt-2">
                You won&apos;t be charged yet
              </p>
            </div>
          </div>
        </div>
      )}
    </>
  );
};
