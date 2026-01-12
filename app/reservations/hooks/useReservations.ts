// features/reservations/hooks/useReservations.ts
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { reservationApi } from "../api/reservationApi";
import {
  ListReservationsParams,
  PaginatedReservationsResponse,
  ReservationDetails,
  CheckoutPayload,
  CheckoutResponse,
} from "../types";

export const reservationKeys: {
  all: readonly ["reservations"];
  lists: (
    params: ListReservationsParams
  ) => readonly ["reservations", "list", ListReservationsParams];
  details: (id: string) => readonly ["reservations", "detail", string];
} = {
  all: ["reservations"] as const,
  lists: (params: ListReservationsParams) =>
    [...reservationKeys.all, "list", params] as const,
  details: (id: string) => [...reservationKeys.all, "detail", id] as const,
};

export const useReservationsList = (params: ListReservationsParams) => {
  return useQuery<PaginatedReservationsResponse>({
    queryKey: reservationKeys.lists(params),
    queryFn: () => reservationApi.list(params),
    enabled: true,
    staleTime: 1000 * 60 * 5,
  });
};

export const useReservationDetails = (id: string) => {
  return useQuery<ReservationDetails>({
    queryKey: reservationKeys.details(id),
    queryFn: () => reservationApi.getDetails(id),
    enabled: !!id,
  });
};

export const useModifyReservation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: reservationApi.modify,

    onSuccess: (updatedReservation) => {
      const id = updatedReservation.id;

      queryClient.setQueryData(reservationKeys.details(id), updatedReservation);

      queryClient.invalidateQueries({
        queryKey: reservationKeys.all,
        refetchType: "none",
      });
    },
  });
};

export const useCheckout = () => {
  return useMutation<CheckoutResponse, Error, CheckoutPayload>({
    mutationFn: reservationApi.checkout,

    // The onSuccess callback returns the client_secret needed by Stripe.js
    onSuccess: (data) => {
      console.log(
        `Checkout initiated. Reservation ID: ${data.reservation_id}, Client Secret received.`
      );
      // The calling component handles the payment step (Stripe.js init)
    },

    onError: (error) => {
      // Handle critical errors like 422 (missing 'net' price) or 400 (Hotelbeds failure)
      console.error("Checkout failed:", error);
    },
  });
};
