import { apiClient } from "@/lib/apiClient";
import {
  Favorite,
  FavoriteItemType,
  AddFavoritePayload,
  CheckFavoriteParams,
  CheckFavoriteResponse,
} from "../types";

export const favoriteApi = {
  listFavorites: async (type: FavoriteItemType): Promise<Favorite[]> => {
    const response = await apiClient.get("/favorites", { params: { type } });
    console.log("API Response for listFavorites:", response);
    return response.data.results || [];
  },

  checkFavorite: async (
    params: CheckFavoriteParams
  ): Promise<CheckFavoriteResponse> => {
    const response = await apiClient.get("/favorites/check", { params });
    return response.data;
  },

  addFavorite: async (data: AddFavoritePayload): Promise<{ id: number }> => {
    const response = await apiClient.post("/favorites", data);
    return response.data;
  },

  removeFavorite: async (favoriteId: number): Promise<void> => {
    await apiClient.delete(`/favorites/${favoriteId}`);
  },
};
