import { apiClient } from "@/lib/apiClient";
import {
  Traveler,
  CreateTravelerPayload,
  UpdateTravelerPayload,
} from "../types";

export const travelersApi = {
  listTravelers: async (): Promise<Traveler[]> => {
    const response = await apiClient.get("/account/travelers");
    return response.data.data;
  },

  createTraveler: async (data: CreateTravelerPayload): Promise<Traveler> => {
    const response = await apiClient.post("/account/travelers", data);
    return response.data;
  },

  updateTraveler: async ({
    id,
    data,
  }: {
    id: number;
    data: UpdateTravelerPayload;
  }): Promise<Traveler> => {
    const response = await apiClient.put(`/account/travelers/${id}`, data);
    return response.data;
  },

  deleteTraveler: async (id: number): Promise<void> => {
    await apiClient.delete(`/account/travelers/${id}`);
  },
};
