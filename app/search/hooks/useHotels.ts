import { useMutation, useQuery } from "@tanstack/react-query";
import { hotelApi } from "../api/hotelApi";
import {
  SearchParams,
  PropertySearchQuery,
  RateKey,
  PriceQuotePayload,
  RateKeyPayload,
  RoomSelectionPayload,
} from "../types";
import { SearchData } from "@/app/hotels/[slug]/page";

export const hotelKeys = {
  // General tag for hotel data
  all: ["hotels"] as const,
  // Availability search results are cached based on the search payload
  availability: (params: SearchParams) =>
    [...hotelKeys.all, "availability", params] as const,
  // Property search (autocomplete) is based on the query string
  propertySearch: (query: string) =>
    [...hotelKeys.all, "property-search", query] as const,
  // Rooms are cached based on hotel ID and dates
  rooms: (
    hotelId: number,
    checkIn: string,
    checkOut: string,
    adults: number,
    children: number
  ) =>
    [
      ...hotelKeys.all,
      "rooms",
      hotelId,
      checkIn,
      checkOut,
      adults,
      children,
    ] as const,
  // Hotel details cached by hotel ID
  byId: (hotelId: number) => [...hotelKeys.all, "by-id", hotelId] as const,
  priceQuote: (rateKey: RateKey) => ["hotels", "price-quote", rateKey] as const,
};

// Use this hook on the results page after the user submits the main search form.
export const useAvailabilitySearch = (
  params: SearchParams,
  enabled: boolean
) => {
  return useQuery({
    queryKey: hotelKeys.availability(params),
    // Use a custom queryFn wrapper to handle the POST method
    queryFn: () => hotelApi.runAvailabilitySearch(params),
    enabled: enabled, // Only run when needed
    // Disable automatic retries for availability search so a single user
    // click triggers only one network request (user requested behavior)
    retry: 0,
    // Avoid refetching on window focus/mount — caller controls when to fetch
    refetchOnWindowFocus: false,
    refetchOnMount: false,
    staleTime: 1000 * 60 * 15, // Search results can be cached for 15 minutes
    // If the results are massive, consider setting garbageCollectionTime lower.
  });
};

// Use this hook for typeahead/autocomplete search boxes.
export const usePropertySearch = (query: string) => {
  const params: PropertySearchQuery = { query, limit: 10 };

  return useQuery({
    queryKey: hotelKeys.propertySearch(query),
    queryFn: () => hotelApi.searchProperties(params),
    // Only run the query if the search query is at least 3 characters long.
    enabled: query.length > 2,
    staleTime: 1000 * 60 * 60, // Property names are static, can cache long
  });
};

// Use this hook on the specific Hotel Detail page.
export const useHotelRooms = (hotelId: number, searchData: SearchData) => {
  return useQuery({
    queryKey: hotelKeys.rooms(
      hotelId,
      searchData.checkIn,
      searchData.checkOut,
      searchData.adults,
      searchData.children
    ),
    queryFn: () =>
      hotelApi.getRoomsAndRates(
        hotelId,
        searchData.checkIn,
        searchData.checkOut,
        searchData.adults,
        searchData.children
      ),
    enabled:
      !!hotelId &&
      !!searchData.checkIn &&
      !!searchData.checkOut &&
      searchData.adults > 0 &&
      searchData.children >= 0, // Must have parameters
    staleTime: 1000 * 60 * 5, // Room availability is highly dynamic, keep cache short (5 min)
  });
};

export const useHotelById = (hotelId: number) => {
  return useQuery({
    queryKey: hotelKeys.byId(hotelId),
    queryFn: () => hotelApi.getHotelById(hotelId),
    enabled: !!hotelId, // Must have parameters
    staleTime: 1000 * 60 * 60, // Hotel details are relatively static, cache longer (1 hr)
  });
};

// Use this hook to get the final price guarantee on the checkout page.
export const usePriceQuote = (
  hotelId: number,
  payload: PriceQuotePayload,
  enabled: boolean
) => {
  return useQuery({
    queryKey: hotelKeys.priceQuote(payload.rateKey),
    queryFn: () => hotelApi.getPriceQuote(hotelId, payload),
    enabled: enabled && !!payload.rateKey,
    staleTime: 1000 * 60 * 1, // 🚨 Price quotes expire fast, cache for only 1 minute
  });
};

// Mutation 1: Check Availability (Pre-Booking Check)
export const useCheckRoomAvailability = (hotelId: number) => {
  return useMutation({
    mutationFn: (data: RateKeyPayload) =>
      hotelApi.checkRoomAvailability(hotelId, data),
    // onSuccess: Returns { isAvailable: boolean } which dictates if the user can proceed.
  });
};

// Mutation 2: Create Room Selection (The Cart/Intent)
export const useCreateRoomSelection = (hotelId: number) => {
  return useMutation({
    mutationFn: (data: RoomSelectionPayload) =>
      hotelApi.createRoomSelection(hotelId, data),
    // onSuccess: Returns RoomSelectionResponse, which often contains the bookingIntentId
    // This ID is CRITICAL and must be saved to state/context/session for the next step (Booking).
  });
};

export const useTailoredHotelRecommendations = () => {
  return useQuery({
    queryKey: ["tailored-hotel-recommendations"],
    queryFn: () => hotelApi.getTailoredHotelRecommendations(),
    staleTime: 1000 * 60 * 30, // Cache for 30 minutes
  });
};
