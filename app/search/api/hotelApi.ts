import { apiClient } from "@/lib/apiClient";
import {
  SearchParams,
  SearchResultHotel,
  PropertySearchQuery,
  HotelDetails,
  HotelListItem,
  RateKeyPayload,
  PriceQuotePayload,
  PriceQuoteResponse,
  RoomSelectionPayload,
  RoomSelectionResponse,
  TailoredHotelRecommendations,
} from "../types";

export const hotelApi = {
  // 1. POST /search (Hotelbeds Availability)
  runAvailabilitySearch: async (
    params: SearchParams
  ): Promise<HotelListItem[]> => {
    const response = await apiClient.post("/search", {
      ...params,
      guests: {
        adults: params.guests.adults || 2,
        children: params.guests.children,
      },
    });
    return response.data.results;
  },

  // 2. GET /properties/search (Local Property Lookup/Typeahead)
  searchProperties: async (
    params: PropertySearchQuery
  ): Promise<SearchResultHotel[]> => {
    const response = await apiClient.get("/properties/search", { params });
    return response.data;
  },

  getRoomsAndRates: async (
    hotelId: number,
    checkIn: string,
    checkOut: string,
    adults: number = 2,
    children: number = 0
  ): Promise<HotelDetails> => {
    console.log("Fetching rooms for hotel:", hotelId, {
      checkIn,
      checkOut,
      adults,
      children,
    });
    const response = await apiClient.get(`/hotels/${hotelId}/rooms`, {
      params: { checkIn, checkOut, adults, children },
    });
    return response.data;
  },

  getHotelById: async (hotelId: number): Promise<HotelDetails> => {
    const response = await apiClient.get(`/hotels/${hotelId}`);
    return response.data;
  },

  getPriceQuote: async (
    hotelId: number,
    data: PriceQuotePayload
  ): Promise<PriceQuoteResponse> => {
    const response = await apiClient.post(
      `/hotels/${hotelId}/rooms/price`,
      data
    );
    return response.data;
  },

  checkRoomAvailability: async (
    hotelId: number,
    data: RateKeyPayload
  ): Promise<{ isAvailable: boolean }> => {
    // This is often a crucial pre-booking step to ensure the rateKey is still active.
    const response = await apiClient.post(
      `/hotels/${hotelId}/rooms/check-availability`,
      data
    );
    return response.data;
  },

  createRoomSelection: async (
    hotelId: number,
    data: RoomSelectionPayload
  ): Promise<RoomSelectionResponse> => {
    const response = await apiClient.post(
      `/hotels/${hotelId}/rooms/selection`,
      data
    );
    return response.data;
  },

  getTailoredHotelRecommendations: async (): Promise<TailoredHotelRecommendations> => {
    const response = await apiClient.get(`/tailored-hotels`);
    return response.data;
  },
};
