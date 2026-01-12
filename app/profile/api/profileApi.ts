import { apiClient } from "@/lib/apiClient";
import {
  UserProfile,
  UpdateProfilePayload,
  ChangePasswordPayload,
} from "../types";

export const profileApi = {
  getProfile: async (): Promise<UserProfile> => {
    const response = await apiClient.get("/account/profile");
    return response.data;
  },

  updateProfile: async (data: UpdateProfilePayload): Promise<UserProfile> => {
    const response = await apiClient.put("/account/profile", data);
    return response.data;
  },

  changePassword: async (data: ChangePasswordPayload): Promise<void> => {
    await apiClient.put("/account/change-password", data);
  },
};
