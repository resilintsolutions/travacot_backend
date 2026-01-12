// features/hotels/hooks/useHotels.ts
import { useQuery } from "@tanstack/react-query";
import { hotelApi } from "../../../../app/search/api/hotelApi";
import { SearchParams, PropertySearchQuery } from "../../../../app/search/types";

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
  rooms: (hotelId: number, checkIn: string, checkOut: string) =>
    [...hotelKeys.all, "rooms", hotelId, checkIn, checkOut] as const,
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
export const useHotelRooms = (
  hotelId: number,
  checkIn: string,
  checkOut: string
) => {
  return useQuery({
    queryKey: hotelKeys.rooms(hotelId, checkIn, checkOut),
    queryFn: () => hotelApi.getRoomsAndRates(hotelId, checkIn, checkOut),
    enabled: !!hotelId && !!checkIn && !!checkOut, // Must have parameters
    staleTime: 1000 * 60 * 5, // Room availability is highly dynamic, keep cache short (5 min)
  });
};
