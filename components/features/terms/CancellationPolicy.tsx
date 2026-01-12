"use client";

import { Fragment, useEffect, useState } from "react";
import Modal from "@/components/shared/Modal";
import { cn } from "@/lib/utils";
import { IoClose } from "react-icons/io5";

export default function CancellationPolicy({
  title,
  className,
}: {
  title?: string;
  className?: string;
}) {
  const [isOpen, setIsOpen] = useState(false);
  const [isMobile, setIsMobile] = useState<boolean | null>(null);

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
      <span
        onClick={() => setIsOpen(true)}
        className={cn("text-sm underline text-core cursor-pointer", className)}
      >
        {title ?? "Travacot's rules on cancellation policies"}
      </span>

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
              "fixed bottom-0 left-0 right-0 z-50 bg-white h-[calc(100vh-10rem)] rounded-t-3xl flex flex-col transition-transform duration-500 ease-in-out",
              isOpen ? "translate-y-0" : "translate-y-full"
            )}
          >
            {/* Header */}
            <div className="flex items-center justify-between gap-2 p-4 border-b">
              <h2 className="text-core font-bold">
                Travacot&apos;s Rules on Cancellation Policy
              </h2>

              <button onClick={onClose} className="ml-auto">
                <IoClose className="text-xl" />
              </button>
            </div>

            {/* Content */}
            <TermsContent className="flex-1 flex flex-col overflow-y-auto p-2" />
          </div>
        </>
      ) : (
        <Modal
          isOpen={isOpen}
          onClose={() => setIsOpen(false)}
          title={
            <h2 className="text-lg text-core font-bold px-2">
              Travacot&apos;s Rules on Cancellation Policy
            </h2>
          }
        >
          <TermsContent className="items-start" />
        </Modal>
      )}
    </Fragment>
  );
}

const TermsContent = ({ className }: { className?: string }) => {
  return (
    <div className={cn("flex flex-col", className)}>
      <div className="space-y-4 px-2 text-core text-left">
        <h2 className="text-indigo-600">Last Updated: 09/18/2025</h2>

        <p>
          Welcome to Travacot (“we,” “our,” “us”). By booking through our
          website, mobile app, or customer service channels, you (“you,” “your,”
          “customer,” “traveler”) agree to the following cancellation policy.
          Please read carefully.
        </p>

        <h3 className="font-semibold mt-4">General Rule</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            <p>
              All cancellations and refunds are subject to the supplier&apos;s
              policies (e.g., hotel, airline, activity provider).
            </p>
          </li>
          <li>
            <p>
              Travacot acts as an intermediary. We do not determine or control
              cancellation penalties set by suppliers.
            </p>
          </li>
        </ul>

        <h3 className="font-semibold mt-4">
          Refundable vs. Non-Refundable Bookings
        </h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            <p>
              <strong>Refundable Bookings:</strong> If the supplier allows
              cancellation, the request must be made before the deadline stated
              in the booking confirmation.
            </p>
          </li>
          <li>
            <p>
              <strong>Non-Refundable Bookings:</strong> No refund will be issued
              under any circumstances (including no-shows or early check-out).
            </p>
          </li>
        </ul>

        <h3 className="font-semibold mt-4">Cancellation Deadlines</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            Each booking confirmation will state the latest date and time by
            which you may cancel without penalty.
          </li>
          <li>
            Cancellations made after this deadline may result in full charges.
          </li>
        </ul>

        <h3 className="font-semibold mt-4">No-Show Policy</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            Failure to check in on the scheduled arrival date without notifying
            us or the supplier will be treated as a no-show.
          </li>
          <li>
            No-shows are generally charged 100% of the booking amount and are
            non-refundable.
          </li>
        </ul>

        <h3 className="font-semibold mt-4">Amendments (Changes to Bookings)</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            <p>
              Any amendments (date changes, guest name changes, room type
              changes, etc.) are subject to the supplier&apos;s policies and
              availability.
            </p>
          </li>
          <li>
            <p>Additional fees may apply.</p>
          </li>
        </ul>

        <h3 className="font-semibold mt-4">Refund Process</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            <p>
              If you are eligible for a refund, it will be processed according
              to the supplier&apos;s rules. Refunds may take up to 5 to 7
              business days to appear on your account, depending on the payment
              method and banking processes.
            </p>
          </li>
          <li>
            <p>Our service/processing fees (if any) are non-refundable.</p>
          </li>
        </ul>

        <h3 className="font-semibold mt-4">
          Force Majeure (Unforeseen Events)
        </h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            In cases of natural disasters, political instability, health
            emergencies, or other events beyond control, refunds will depend on
            the supplier&apos;s policy.
          </li>
          <li>
            We will assist in negotiating with the supplier, but we cannot
            guarantee a refund.
          </li>
        </ul>
      </div>
    </div>
  );
};
