import { apiClient } from "@/lib/apiClient";
import {
  SavedPaymentMethod,
  AddPaymentMethodPayload,
  UpdatePaymentMethodPayload,
  PropertyPaymentOption,
} from "../types";

export const paymentApi = {
  listSavedMethods: async (): Promise<SavedPaymentMethod[]> => {
    const response = await apiClient.get("/payment-methods");
    return response.data.paymentMethods;
  },

  addMethod: async (
    data: AddPaymentMethodPayload
  ): Promise<SavedPaymentMethod> => {
    const response = await apiClient.post("/payment-methods", data);
    return response.data;
  },

  updateMethod: async ({
    id,
    data,
  }: {
    id: number;
    data: UpdatePaymentMethodPayload;
  }): Promise<SavedPaymentMethod> => {
    const response = await apiClient.patch(`/payment-methods/${id}`, data);
    return response.data;
  },

  deleteMethod: async (id: number): Promise<void> => {
    await apiClient.delete(`/payment-methods/${id}`);
  },

  getOptionsForProperty: async (
    propertyId: number
  ): Promise<PropertyPaymentOption[]> => {
    const response = await apiClient.get(
      `/properties/${propertyId}/payment-options`
    );
    return response.data;
  },
};
