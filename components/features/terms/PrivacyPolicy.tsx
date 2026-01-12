"use client";

import { Fragment, useEffect, useState } from "react";
import Modal from "@/components/shared/Modal";
import { cn } from "@/lib/utils";
import { IoClose } from "react-icons/io5";

export default function PrivacyPolicy() {
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
        className="text-sm underline text-core cursor-pointer"
      >
        Privacy Policy
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
              <h2 className="text-core font-bold">Privacy Policy</h2>

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
            <h2 className="text-lg text-core font-bold px-2">Privacy Policy</h2>
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
          Welcome to Travacot (“we,” “our,” “us”). Your privacy is important to
          us. This Privacy Policy explains how we collect, use, and protect your
          personal data when you use our website, mobile app, and services.
        </p>

        <h3 className="font-semibold">What Information We Collect</h3>

        <ul className="list-disc ml-5">
          <li>
            <h4>Personal Information</h4>
            <ul className="list-disc ml-5">
              <li>Name, email address, phone number</li>
              <li>Billing and payment information</li>
              <li>Passport or ID details (if required for booking)</li>
              <li>Travel preferences and history</li>
            </ul>
          </li>

          <li className="mt-4">
            <h4>Usage Information</h4>
            <ul className="list-disc ml-5">
              <li>IP address, browser type, device information</li>
              <li>Pages visited, search history, clicks</li>
              <li>Location data (if location services are enabled)</li>
            </ul>
          </li>

          <li className="mt-4">
            <h4>Booking Details</h4>
            <ul className="list-disc ml-5">
              <li>Hotel/reservation information</li>
              <li>Dates of travel</li>
              <li>Special requests (e.g., accessibility, preferences)</li>
            </ul>
          </li>
        </ul>

        <h3 className="font-semibold">How We Use Your Information</h3>
        <ul className="list-disc ml-5">
          <li>Process and manage your bookings</li>
          <li>Send confirmations, reminders, and updates</li>
          <li>Personalize your experience and recommend stays</li>
          <li>Provide customer support</li>
          <li>Improve our platform and services</li>
          <li>Send promotional offers (only with your consent)</li>
        </ul>

        <h3 className="font-semibold">Who We Share Your Information With</h3>

        <ul className="list-disc ml-5">
          <li>
            <p>We may share your data with:</p>
            <ul className="list-disc ml-5">
              <li>Accommodation providers to fulfill your bookings</li>
              <li>Payment processors for secure transactions</li>
              <li>Customer support services to assist you</li>
              <li>Marketing partners (with opt-in only)</li>
              <li>Legal authorities (if required by law)</li>
            </ul>
          </li>
          <li className="mt-4">
            <p className="font-semibold">We never sell your personal data.</p>
          </li>
        </ul>

        <h3 className="font-semibold">How We Protect Your Data</h3>
        <ul className="list-disc ml-5">
          <li>SSL encryption</li>
          <li>Secure payment gateways</li>
          <li>Role-based access control</li>
          <li>Regular audits and monitoring</li>
        </ul>
        <p>
          Despite our efforts, no system is 100% secure, but we take every
          reasonable step to protect your data.
        </p>

        <h3 className="font-semibold">Your Rights & Choices</h3>
        <ul className="list-disc ml-5">
          <li>Access, update, or delete your personal data</li>
          <li>Opt-out of marketing emails at any time</li>
          <li>Request a copy of your stored data</li>
          <li>Withdraw consent where applicable</li>
        </ul>

        <h3 className="font-semibold">Cookies & Tracking Technologies</h3>
        <ul className="list-disc ml-5">
          <li>
            <h4>We use cookies to:</h4>
            <ul className="list-disc ml-5">
              <li>Remember your preferences</li>
              <li>Analyze usage to improve performance</li>
              <li>Deliver personalized ads (only with consent)</li>
            </ul>
          </li>
        </ul>

        <p>You can manage cookies via your browser settings.</p>

        <ul className="list-disc ml-5">
          <li>
            Refunds may take up to 5 to 7 business days depending on your
            payment method.
          </li>
          <li className="mt-4">
            Our Service/processing fees (if any) are non-refundable.
          </li>
        </ul>

        <h3 className="font-semibold">International Data Transfers</h3>
        <ul className="list-disc ml-5">
          <li>
            Travacot operates globally. If you’re outside our primary region of
            operation, your data may be transferred and processed in countries
            with different data protection laws.{" "}
          </li>
          <li className="mt-4">
            We ensure adequate safeguards are in place (e.g., Standard
            Contractual Clauses).
          </li>
        </ul>

        <h3 className="font-semibold">Children’s Privacy</h3>
        <ul className="list-disc ml-5">
          <li>
            Our services are not intended for individuals under 16. We do not
            knowingly collect data from children without parental consent.
          </li>
        </ul>

        <h3 className="font-semibold">Contact Us</h3>
        <ul className="list-disc ml-5">
          <li>
            <p>
              If you have questions or concerns about your privacy, contact us:{" "}
              <a
                className="text-blue-600 underline hover:text-indigo-600"
                href="mailto:support@travacot.com"
              >
                support@travacot.com
              </a>
            </p>
          </li>
        </ul>

        <h3 className="font-semibold">Changes to This Policy</h3>
        <ul className="list-disc ml-5">
          <li>
            <p>
              We may update this Privacy Policy from time to time. When we do,
              we&apos;ll notify you via email or through our platform.
            </p>
          </li>
        </ul>
      </div>
    </div>
  );
};
