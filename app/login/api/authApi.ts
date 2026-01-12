import { apiClient } from "@/lib/apiClient";
import {
  LoginPayload,
  RegisterPayload,
  SocialLoginPayload,
  AuthResponse,
  User,
} from "../types";

export const authApi = {
  register: async (data: RegisterPayload): Promise<AuthResponse> => {
    const response = await apiClient.post("/auth/register", data);
    return response.data;
  },

  login: async (data: LoginPayload): Promise<AuthResponse> => {
    const response = await apiClient.post("/auth/login", data);
    return response.data;
  },

  // Note: Your JSON shows the URL is /auth/google/token
  loginWithGoogle: async (data: SocialLoginPayload): Promise<AuthResponse> => {
    const response = await apiClient.post("/auth/google/token", data);
    return response.data;
  },

  // Note: Your JSON shows the URL is /auth/facebook/token
  loginWithFacebook: async (
    data: SocialLoginPayload
  ): Promise<AuthResponse> => {
    const response = await apiClient.post("/auth/facebook/token", data);
    return response.data;
  },

  getMe: async (): Promise<User> => {
    const response = await apiClient.get("/auth/me");
    return response.data;
  },
};
