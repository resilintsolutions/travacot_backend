import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { travelersApi } from "../api/travelersApi";

export const travelerKeys = {
  all: ["travelers"] as const,
  lists: () => [...travelerKeys.all, "list"] as const,
};

export const useTravelers = () => {
  return useQuery({
    queryKey: travelerKeys.lists(),
    queryFn: travelersApi.listTravelers,
    staleTime: 1000 * 60 * 10,
  });
};

export const useCreateTraveler = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: travelersApi.createTraveler,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: travelerKeys.lists() });
    },
  });
};

export const useUpdateTraveler = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: travelersApi.updateTraveler,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: travelerKeys.lists() });
    },
  });
};

export const useDeleteTraveler = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: travelersApi.deleteTraveler,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: travelerKeys.lists() });
    },
  });
};
