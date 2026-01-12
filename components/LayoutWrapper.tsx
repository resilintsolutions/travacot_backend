"use client";

import { usePathname, useRouter } from "next/navigation";
import { useEffect } from "react";
import AuthGuard from "./AuthGuard";
import { getCookie } from "@/lib/cookies";
import { useSession } from "next-auth/react";

const PUBLIC_ROUTES = ["/login", "/register"];

export default function LayoutWrapper({
  children,
}: {
  children: React.ReactNode;
}) {
  const pathname = usePathname();
  const router = useRouter();

  const isPublicRoute = PUBLIC_ROUTES.includes(pathname);
  const session = useSession();
  const token = session.data?.user.accessToken;
  const isLoggedIn =
    (typeof window !== "undefined" && Boolean(getCookie("access_token"))) ||
    Boolean(token);

  // If visiting a public route while logged in, redirect to home.
  useEffect(() => {
    if (isPublicRoute && isLoggedIn) {
      router.push("/");
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isPublicRoute, isLoggedIn]);

  if (isPublicRoute && !isLoggedIn) {
    return <>{children}</>;
  }

  // If it's NOT a public route (e.g., /dashboard), wrap it with AuthGuard.
  return <AuthGuard>{children}</AuthGuard>;
}
