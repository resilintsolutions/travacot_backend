"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { getCookie } from "@/lib/cookies";

export default function AuthGuard({ children }: { children: React.ReactNode }) {
  const router = useRouter();
  // const [isAuthorized, setIsAuthorized] = useState(false);

  useEffect(() => {
    // 1. Check if token exists in cookies
    const token = getCookie("access_token");

    if (!token) {
      // 2. If no token, redirect to the public homepage (user can choose to sign in)
      // router.push("/");
    } else {
      // 3. If token exists, allow access
      // setIsAuthorized(true);
    }
  }, [router]);

  // 4. While checking, show nothing (or a loading spinner) to avoid "flashing" protected content
  // if (!isAuthorized) {
  //   return null;
  // }

  // 5. If authorized, render the page
  return <>{children}</>;
}
