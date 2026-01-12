import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { favoriteApi } from "../api/favoriteApi";
import { FavoriteItemType, CheckFavoriteParams } from "../types";

export const favoriteKeys = {
  list: (type: FavoriteItemType) => ["favorites", "list", type] as const,
  check: (params: CheckFavoriteParams) =>
    ["favorites", "check", params.itemType, params.itemId] as const,
};

export const useFavoriteList = (type: FavoriteItemType) => {
  return useQuery({
    queryKey: favoriteKeys.list(type),
    queryFn: async () => {
      try {
        console.log("Fetching favorites for type:", type);
        const hotels = await favoriteApi.listFavorites(type);
        console.log("Fetched favorites:", hotels);
        return hotels;
      } catch (error) {
        console.error("Error fetching favorites:", error);
        return [];
      }
    },
    staleTime: 1000 * 60 * 10,
  });
};

export const useCheckFavorite = (params: CheckFavoriteParams) => {
  return useQuery({
    queryKey: favoriteKeys.check(params),
    queryFn: () => favoriteApi.checkFavorite(params),
    enabled: !!params.itemId,
    staleTime: Infinity, // Status should be static until user acts
  });
};

const invalidateFavoriteQueries = (
  queryClient: ReturnType<typeof useQueryClient>,
  itemType: FavoriteItemType,
  itemId: number
) => {
  queryClient.invalidateQueries({
    queryKey: favoriteKeys.check({ itemType, itemId }),
    exact: true,
  });
  queryClient.invalidateQueries({ queryKey: favoriteKeys.list(itemType) });
};

export const useAddFavorite = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({
      itemType,
      itemId,
    }: {
      itemType: FavoriteItemType;
      itemId: number;
    }) => favoriteApi.addFavorite({ itemType, itemId }),
    onSuccess: (_, { itemType, itemId }) => {
      invalidateFavoriteQueries(queryClient, itemType, itemId);
    },
  });
};

export const useRemoveFavorite = (
  itemType: FavoriteItemType,
  itemId: number
) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (favoriteId: number) => favoriteApi.removeFavorite(favoriteId),
    onSuccess: () => {
      invalidateFavoriteQueries(queryClient, itemType, itemId);
    },
  });
};
