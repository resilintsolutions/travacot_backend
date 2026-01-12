import { apiClient } from "@/lib/apiClient";
import {
  ListReservationsParams,
  PaginatedReservationsResponse,
  ReservationDetails,
  ModifyReservationPayload,
  CheckoutPayload,
  CheckoutResponse,
} from "../types";

export const reservationApi = {
  list: async (
    params: ListReservationsParams
  ): Promise<PaginatedReservationsResponse> => {
    // Note: Axios automatically serializes params object into URL query string
    const response = await apiClient.get("/reservations", { params });
    return response.data;
  },

  getDetails: async (id: string): Promise<ReservationDetails> => {
    const response = await apiClient.get(`/reservations/${id}`);
    return response.data.data;
  },

  modify: async ({
    id,
    data,
  }: {
    id: string;
    data: ModifyReservationPayload;
  }): Promise<ReservationDetails> => {
    const response = await apiClient.put(`/reservations/${id}`, data);
    return response.data;
  },
  checkout: async (data: CheckoutPayload): Promise<CheckoutResponse> => {
    // This starts the transaction: re-checking price/availability and creating the PaymentIntent.
    const response = await apiClient.post('/reservations/checkout', data);
    return response.data;
  },
};
