export type FavoriteItemType = "hotel" | "flight" | "all";

export interface Favorite {
  id: number; // The ID of the favorite entry itself
  itemType: FavoriteItemType;
  itemId: number; // The ID of the actual hotel/deal
  name: string; // The name of the favorited item (returned by backend)
  addedAt: string;
  images: string[]; // Array of image URLs
  category: string; // e.g., "hotel", "flight"
}

export interface AddFavoritePayload {
  itemType: FavoriteItemType;
  itemId: number;
}

export interface CheckFavoriteParams {
  itemType: FavoriteItemType;
  itemId: number;
}

export interface CheckFavoriteResponse {
  isFavorite: boolean;
  favoriteId?: number; // The ID of the favorite entry, if it exists
}
