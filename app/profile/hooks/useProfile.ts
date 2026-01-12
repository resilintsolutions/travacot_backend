export const profileKeys = {
  all: ["profile"] as const,
  details: () => [...profileKeys.all, "detail"] as const,
};

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { profileApi } from "../api/profileApi";

export const useProfile = (isEnabled: boolean) => {
  return useQuery({
    queryKey: profileKeys.details(),
    queryFn: profileApi.getProfile,
    staleTime: 1000 * 60 * 5, // Profile data can be cached for 5 minutes
    enabled: isEnabled,
  });
};

export const useUpdateProfile = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: profileApi.updateProfile,

    // Invalidate/Update Cache on Success
    onSuccess: (updatedUser) => {
      // 1. Invalidate: Marks the 'profile' query as stale, forcing a refetch next time.
      queryClient.invalidateQueries({ queryKey: profileKeys.details() });

      // OR 2. Direct Update (Better UX): Update the cache immediately with the new data
      queryClient.setQueryData(profileKeys.details(), updatedUser);
    },
  });
};

export const useChangePassword = () => {
  return useMutation({
    mutationFn: profileApi.changePassword,

    onSuccess: () => {
      // Password change doesn't usually require cache invalidation,
      // but you might show a success toast here.
    },
  });
};
