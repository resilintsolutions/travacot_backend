"use client";

import React, { useState, useMemo, FormEvent, useEffect } from "react";

// NOTE: External packages are causing compilation errors due to unresolved imports.
// We must assume the user has installed @stripe/react-stripe-js and @stripe/stripe-js
// and that the framework correctly provides the necessary context objects.
// We are removing specific type imports from these libraries to bypass the resolver error.
import {
  useStripe,
  useElements,
  Elements,
  CardNumberElement,
  CardExpiryElement,
  CardCvcElement,
} from "@stripe/react-stripe-js";
import { loadStripe, Stripe } from "@stripe/stripe-js";
import { useReservationDetails } from "@/app/reservations/hooks/useReservations";
import {
  CheckoutPayload,
  ReservationDetails as OriginalReservationDetails,
} from "@/app/reservations/types";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogClose,
} from "@/components/ui/dialog";
import { X } from "lucide-react";
import { useParams, useRouter } from "next/navigation";

const inputStyle = {
  iconColor: "#9AA3C1",
  color: "#1F1E33", // Core text
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

// Extend ReservationDetails type to include all possible statuses used in this file
type ReservationStatus =
  | "active"
  | "confirmed"
  | "payment_failed"
  | "cancelled"
  | "completed"
  | "pending"
  | undefined;

interface ReservationDetails extends Omit<
  OriginalReservationDetails,
  "status"
> {
  status: ReservationStatus;
}

// NOTE: Internal path alias is causing errors. We MUST assume the file exists
// and manually use 'any' or fallback types for the imported features if the alias fails.
// Since the structure looks valid, we keep the import but rely on the execution
// environment eventually resolving the local files.

// --- Stripe Initialization ---
// We cannot guarantee the type of loadStripe here due to import resolution failure
const stripePromise: Promise<Stripe | null> = loadStripe(
  process.env.NEXT_PUBLIC_STRIPE_PUBLIC_KEY || ""
);

// --- Component Implementations ---

// Separate component to poll the final booking status (Step 4)
interface BookingStatusPollerProps {
  reservationId: string;
}

function BookingStatusPoller({ reservationId }: BookingStatusPollerProps) {
  // We use a generic UseQueryResult type structure here to suppress complex type errors
  // but the underlying functionality (refetchInterval, enabled) remains the same.
  const { data, isFetching } = useReservationDetails(reservationId);
  const router = useRouter();
  const params = useParams();

  const reservationStatus = data as ReservationDetails | undefined;

  console.log("Polling reservation status:", reservationStatus);

  useEffect(() => {
    if (
      reservationStatus?.status === "confirmed" ||
      reservationStatus?.status === "pending"
    ) {
      console.log(`Current reservation status: ${reservationStatus.status}`);
      router.push(`/hotels/${params.slug}/booking/success`);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [reservationStatus]);

  if (isFetching) {
    return (
      <p className="mt-4 text-blue-600 font-medium animate-pulse">
        Finalizing booking confirmation with Hotelbeds...
      </p>
    );
  }

  if (
    reservationStatus?.status === "active" ||
    reservationStatus?.status === "confirmed"
  ) {
    return (
      <p className="mt-4 text-green-600 font-bold">
        ✅ Reservation #{reservationStatus.id} is confirmed!
      </p>
    );
  }

  if (
    reservationStatus?.status === "payment_failed" ||
    reservationStatus?.status === "cancelled"
  ) {
    return (
      <p className="mt-4 text-red-600 font-bold">
        ❌ Booking Failed. Status: {reservationStatus.status}.
      </p>
    );
  }

  return (
    <p className="mt-4 text-gray-600">
      Status: {reservationStatus?.status || "Pending Backend Confirmation..."}.
    </p>
  );
}

// Component that handles the core logic and requires Stripe context
interface PaymentFormProps {
  bookingData: CheckoutPayload;
  isOpen?: boolean;
  onOpenChange?: (open: boolean) => void;
}

type PaymentPayloadWithSecret = CheckoutPayload & {
  clientSecret?: string;
  client_secret?: string;
  paymentIntentClientSecret?: string;
};

function PaymentForm({ bookingData, isOpen, onOpenChange }: PaymentFormProps) {
  // We must use 'any' for Stripe/Elements objects since their original types failed to resolve
  const stripe: Stripe | null = useStripe();
  const elements = useElements();

  const [status, setStatus] = useState<
    "initial" | "processing" | "succeeded" | "failed"
  >("initial");
  const [reservationId, setReservationId] = useState<string | null>(null);
  const [paymentError, setPaymentError] = useState<string | null>(null);
  const [cardReady, setCardReady] = useState(false);

  // Calculate total amount once
  const totalAmount = useMemo(() => {
    return bookingData?.rooms
      ? bookingData.rooms.reduce((sum, r) => sum + (r.net || 0), 0)
      : bookingData?.net || 0;
  }, [bookingData]);

  // Handle form submission (direct Stripe payment)
  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setPaymentError(null);

    if (!stripe || !elements) return;

    const clientSecret =
      (bookingData as PaymentPayloadWithSecret)?.clientSecret ||
      (bookingData as PaymentPayloadWithSecret)?.client_secret ||
      (bookingData as PaymentPayloadWithSecret)?.paymentIntentClientSecret;

    if (!clientSecret) {
      setStatus("failed");
      setPaymentError("Missing client secret for payment confirmation.");
      return;
    }

    // We render split elements; use CardNumberElement as the card handle
    const cardElement = elements.getElement(CardNumberElement);
    console.log("cardElement:", cardElement);
    if (!cardElement) {
      setStatus("failed");
      setPaymentError(
        "Payment form is not ready yet. Please wait a moment and try again."
      );
      return;
    }

    setStatus("processing");

    try {
      const result = await stripe.confirmCardPayment(clientSecret, {
        payment_method: {
          card: cardElement,
          billing_details: {
            name: `${bookingData?.holder?.name || ""} ${bookingData?.holder?.surname || ""}`.trim(),
          },
        },
      });

      if (result.error) {
        setStatus("failed");
        setPaymentError(
          result.error.message || "An unknown error occurred during payment."
        );
      } else if (result.paymentIntent?.status === "succeeded") {
        setStatus("succeeded");
        setReservationId(result.paymentIntent.id);
      } else if (result.paymentIntent) {
        setStatus("failed");
        setPaymentError(`Payment status: ${result.paymentIntent.status}`);
      }
    } catch (e) {
      setStatus("failed");
      setPaymentError("A network error occurred during payment confirmation.");
      console.error(e);
    }
  };

  return (
    <Dialog open={isOpen} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-md rounded-2xl border border-[#E6E8F2] bg-white px-6 py-5 shadow-xl">
        <DialogHeader className="space-y-1">
          <DialogTitle className="text-lg font-bold text-core">
            Payment Details
          </DialogTitle>
          <DialogClose asChild>
            <button className="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none data-[state=open]:bg-accent data-[state=open]:text-muted-foreground">
              <X className="h-4 w-4" />
              <span className="sr-only">Close</span>
            </button>
          </DialogClose>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="rounded-2xl border border-[#E6E8F2] bg-[#F9FAFF] p-4 shadow-sm">
            <div className="space-y-4">
              <div className="space-y-2">
                <label className="block text-sm font-semibold text-core">
                  Card Number
                </label>
                <div className="rounded-xl border border-[#E0E4F4] bg-white p-3 focus-within:ring-2 focus-within:ring-[#3E51CD] transition-all">
                  <CardNumberElement
                    options={{ showIcon: true, style: { base: inputStyle } }}
                    onReady={() => setCardReady(true)}
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div className="space-y-2">
                  <label className="block text-sm font-semibold text-core">
                    Expiration
                  </label>
                  <div className="rounded-xl border border-[#E0E4F4] bg-white p-3 focus-within:ring-2 focus-within:ring-[#3E51CD] transition-all">
                    <CardExpiryElement
                      options={{ style: { base: inputStyle } }}
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <label className="block text-sm font-semibold text-core">
                    CVC
                  </label>
                  <div className="rounded-xl border border-[#E0E4F4] bg-white p-3 focus-within:ring-2 focus-within:ring-[#3E51CD] transition-all">
                    <CardCvcElement options={{ style: { base: inputStyle } }} />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <button
            type="submit"
            disabled={
              !stripe ||
              !elements ||
              !cardReady ||
              status === "succeeded" ||
              status === "processing"
            }
            className="w-full py-3 text-white font-semibold rounded-[14px] transition duration-200 bg-core hover:bg-[#2E2C59] disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed"
          >
            {status === "processing"
              ? "Processing Payment..."
              : status === "succeeded"
                ? "Payment Complete!"
                : `Pay ${bookingData?.currency} ${totalAmount?.toFixed(2)}`}
          </button>

          {/* Display error messages */}
          {paymentError && (
            <p className="text-red-500 text-sm font-medium">
              Payment Error: {paymentError}
            </p>
          )}

          {/* No checkout mutation errors now; payment runs directly */}
        </form>

        {status === "succeeded" && reservationId && (
          <BookingStatusPoller
            reservationId={bookingData.reservationId as string}
          />
        )}
      </DialogContent>
    </Dialog>
  );
}

// Main wrapper component
interface StripeCheckoutWrapperProps {
  bookingData: CheckoutPayload;
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
}

export default function StripeCheckoutProcessor({
  bookingData,
  isOpen,
  onOpenChange,
}: StripeCheckoutWrapperProps) {
  const stripePublishableKey = process.env.NEXT_PUBLIC_STRIPE_PUBLIC_KEY;

  if (!stripePublishableKey) {
    return (
      <div className="text-red-500 p-4 border border-red-300">
        Error: NEXT_PUBLIC_STRIPE_PUBLIC_KEY is missing. Payment cannot be
        initiated.
      </div>
    );
  }

  return (
    <Elements stripe={stripePromise}>
      <PaymentForm
        bookingData={bookingData}
        isOpen={isOpen}
        onOpenChange={onOpenChange}
      />
    </Elements>
  );
}
