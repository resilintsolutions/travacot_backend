
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { paymentApi } from '../api/paymentApi';
export const paymentKeys = {
  saved: ['saved-payment-methods'] as const,
  propertyOptions: (propertyId: number) => ['property-payment-options', propertyId] as const,
};

export const useSavedPaymentMethods = (isLoggedIn: boolean) => {
  return useQuery({
    queryKey: paymentKeys.saved,
    queryFn: paymentApi.listSavedMethods,
    staleTime: 1000 * 60 * 60, // Saved cards rarely change
    enabled: isLoggedIn,
  });
};

export const usePaymentOptionsForProperty = (propertyId: number) => {
  return useQuery({
    queryKey: paymentKeys.propertyOptions(propertyId),
    queryFn: () => paymentApi.getOptionsForProperty(propertyId),
    enabled: !!propertyId, // Only fetch if propertyId is available
    staleTime: 1000 * 60 * 30,
  });
};

export const useAddPaymentMethod = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: paymentApi.addMethod,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: paymentKeys.saved });
    },
  });
};

export const useUpdatePaymentMethod = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: paymentApi.updateMethod,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: paymentKeys.saved });
    },
  });
};

export const useDeletePaymentMethod = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: paymentApi.deleteMethod,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: paymentKeys.saved });
    },
  });
};