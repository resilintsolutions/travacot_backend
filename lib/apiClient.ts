import axios, {
  AxiosError,
  AxiosResponse,
  InternalAxiosRequestConfig,
} from "axios";
import { getCookie, removeCookie } from "./cookies";
import { getSession, signOut } from "next-auth/react";

const baseURL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

// Axios instance
export const apiClient = axios.create({
  baseURL,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  // timeout: 10000, // Optional: Timeout after 10 seconds
});

// -----------------------------------------------------------------------------
// Interceptor 1: Request (Injecting the Token)
// -----------------------------------------------------------------------------
// This runs *before* every request is sent.
apiClient.interceptors.request.use(
  async (config: InternalAxiosRequestConfig) => {
    // Only access cookies in the browser (client-side)
    if (typeof window !== "undefined") {
      const token = getCookie("access_token");
      const session = await getSession();

      const access_token = session?.user?.accessToken;

      // If a token exists, add it to the Authorization header
      if (token || access_token) {
        config.headers.Authorization = `Bearer ${token || access_token}`;
      }
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// -----------------------------------------------------------------------------
// Interceptor 2: Response (Global Error Handling)
// -----------------------------------------------------------------------------
// This runs *after* every response is received.
apiClient.interceptors.response.use(
  (response: AxiosResponse) => {
    // Any status code within the range of 2xx causes this function to trigger
    return response;
  },
  (error: AxiosError) => {
    // Any status code that falls outside the range of 2xx causes this function to trigger

    // Handle 401 Unauthorized (Token expired or invalid)
    if (error.response?.status === 401) {
      if (typeof window !== "undefined") {
        // Optional: Clear cookie and redirect to the public homepage
        removeCookie("access_token");
        // signOut({ callbackUrl: "/login" });
        // window.location.href = "/";
      }
      console.error("Unauthorized access - Redirecting to homepage...");
    }

    // Handle 403 Forbidden (User logged in but doesn't have permission)
    if (error.response?.status === 403) {
      console.error("You do not have permission to access this resource.");
    }

    // Handle 500 Server Errors
    if (error.response?.status && error.response.status >= 500) {
      console.error("Server error - Please try again later.");
    }

    // Reject the promise so React Query knows an error happened
    return Promise.reject(error);
  }
);
