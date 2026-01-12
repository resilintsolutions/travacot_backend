import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { authApi } from "../api/authApi";
import { useRouter } from "next/navigation";
import { setCookie } from "@/lib/cookies";

export const authKeys = {
  user: ["auth-user"] as const,
};

export const useUser = () => {
  return useQuery({
    queryKey: authKeys.user,
    queryFn: authApi.getMe,
    retry: false, // Don't retry if 401 (not logged in)
    staleTime: 1000 * 60 * 30, // User data rarely changes, cache for 30 mins
  });
};

export const useLogin = () => {
  const queryClient = useQueryClient();
  const router = useRouter();

  return useMutation({
    mutationFn: authApi.login,
    onSuccess: (data) => {
      // 1. Save Token in cookie
      setCookie("access_token", data.token, 7);
      setCookie("user_name", data.user.name, 7);

      // 2. Update the "User" cache instantly with the user data we just got
      queryClient.setQueryData(authKeys.user, data.user);

      // 3. Redirect to dashboard
      router.push("/");
    },
  });
};

export const useRegister = () => {
  const queryClient = useQueryClient();
  const router = useRouter();

  return useMutation({
    mutationFn: authApi.register,
    onSuccess: (data) => {
      setCookie("access_token", data.token, 7);
      queryClient.setQueryData(authKeys.user, data.user);
      router.push("/");
    },
  });
};

export const useSocialLogin = (provider: "google" | "facebook") => {
  const queryClient = useQueryClient();
  const router = useRouter();

  const fn =
    provider === "google" ? authApi.loginWithGoogle : authApi.loginWithFacebook;

  return useMutation({
    mutationFn: fn,
    onSuccess: (data) => {
      setCookie("access_token", data.token, 7);
      queryClient.setQueryData(authKeys.user, data.user);
      router.push("/");
    },
  });
};
