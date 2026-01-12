"use client";

import { Fragment, useEffect, useState } from "react";
import Modal from "@/components/shared/Modal";
import { cn } from "@/lib/utils";
import { IoClose } from "react-icons/io5";

export default function TermsConditions({
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
        {title ?? "Terms & Conditions"}
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
              <h2 className="text-core font-bold">Terms & Conditions</h2>

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
              Terms & Conditions
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
          “customer,” “traveler”) agree to the following Terms & Conditions.
          Please read them carefully before making any booking.
        </p>

        <h3 className="font-semibold mt-4">1. Scope of Service</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            <p>
              We act as an online travel agency (OTA), providing booking
              services for hotels, flights, and related travel products
              (“Services”).
            </p>
          </li>
          <li>
            <p>
              The travel products offered are provided by third-party suppliers
              (e.g., hotels, airlines, car rental companies). We do not own or
              operate these services.
            </p>
          </li>
        </ul>

        <h3 className="font-semibold mt-4">2. Contractual Relationship</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            <p>
              When you make a booking, your contract is with the supplier (e.g.,
              hotel or airline), not with Travacot.
            </p>
          </li>
          <li>
            <p>
              We facilitate your booking as an intermediary. Each supplier’s
              terms and conditions also apply.
            </p>
          </li>
        </ul>

        <h3 className="font-semibold mt-4">3. Bookings & Payments</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>All bookings are subject to availability and confirmation.</li>
          <li>
            Prices displayed are subject to change until payment is completed.
          </li>
          <li>
            You must provide accurate payment details at the time of booking.
          </li>
          <li>
            Travacot may charge your payment method directly or through a
            virtual card payment system depending on supplier arrangements.
          </li>
        </ul>

        <h3 className="font-semibold mt-4">
          4. Cancellations, Amendments & Refunds
        </h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            Cancellation and amendment policies are determined by the supplier.
          </li>
          <li>Non-refundable bookings cannot be canceled or refunded.</li>
          <li>
            Refunds (if applicable) will be processed according to the
            supplier’s rules and may take up to what the supplier has set the
            business days to be.
          </li>
          <li>Our service fees (if any) are non-refundable.</li>
        </ul>

        <h3 className="font-semibold mt-4">5. Rates & Parity</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            Rates are provided by suppliers or distributors (e.g., Expedia EPS,
            hotel partners).
          </li>
          <li>
            We strive for rate accuracy but cannot guarantee that rates are
            always the lowest available.
          </li>
        </ul>

        <h3 className="font-semibold mt-4">6. Travel Requirements</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            It is your responsibility to ensure that you have valid passports,
            visas, travel insurance, and health documents required for your
            trip.
          </li>
          <li>
            We are not liable if you are denied boarding or entry due to missing
            documents.
          </li>
        </ul>

        <h3 className="font-semibold mt-4">7. Liability</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            Travacot is not responsible for:
            <ul className="list-disc ml-5 space-y-2">
              <li>
                Service failures, overbookings, delays, or cancellations by
                suppliers
              </li>
              <li>Personal injury, loss, or damage during your travel.</li>
            </ul>
          </li>
          <li>
            Our liability is limited to the amount paid to us for your booking.
          </li>
        </ul>

        <h3 className="font-semibold mt-4">8. User Responsibilities</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>You agree to use our Services only for lawful purposes.</li>
          <li>
            You may not make speculative, false, or fraudulent reservations.
          </li>
          <li>
            You are responsible for the accuracy of all information entered
            during booking.
          </li>
        </ul>

        <h3 className="font-semibold mt-4">9. Intellectual Property</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            All content on our website and app (logos, text, images, software)
            is owned by or licensed to Travacot.
          </li>
          <li>
            You may not reproduce, distribute, or modify our content without
            permission.
          </li>
        </ul>

        <h3 className="font-semibold mt-4">10. Governing Law & Jurisdiction</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            These Terms & Conditions are governed by the laws of England and
            Wales.
          </li>
          <li>
            Any disputes will be subject to the exclusive jurisdiction of the
            courts of England and Wales.
          </li>
        </ul>

        <h3 className="font-semibold mt-4">11. Changes to Terms</h3>
        <ul className="list-disc ml-5 space-y-2">
          <li>
            {" "}
            We may update these Terms & Conditions at any time. The updated
            version will be effective as soon as it is published on our website.
          </li>
        </ul>
      </div>
    </div>
  );
};
