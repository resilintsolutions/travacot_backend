"use client";

import { usePathname } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import { getCookie, removeCookie } from "@/lib/cookies";
import personLogo from "@/assets/images/user-icon.svg";
import travacotLogoNew from "@/assets/images/travacot-logo-new.svg";
import { useEffect, useState } from "react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "../ui/button";
import { signOut, useSession } from "next-auth/react";

export default function MainHeader() {
  const pathname = usePathname(); // current path
  const hideUserIcon = pathname.startsWith("/profile");
  const session = useSession();
  const token = session.data?.user.accessToken;
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const isLoginPage = pathname === "/login";
  const isRegisterPager = pathname === "/register";

  // Perform client-side hydration check after component mounts
  useEffect(() => {
    const loggedIn =
      (typeof window !== "undefined" && Boolean(getCookie("access_token"))) ||
      Boolean(token);
    // eslint-disable-next-line react-hooks/set-state-in-effect
    setIsLoggedIn(loggedIn);
  }, [token]);

  return (
    <header className="sticky top-0 border-b-0 md:border-b md:border-[#C9D0E7] backdrop-blur-[55px] bg-[#FAFAFCE5] z-50">
      <div className="container mx-auto pl-4 sm:pl-6 lg:pl-8 xl:pl-10 pr-2 sm:pr-3 lg:pr-4 xl:pr-5">
        <div className="flex items-center justify-between h-[70px] md:h-20">
          <Link href="/" className="w-[126px] flex items-center h-full">
            <Image
              alt="travacot logo"
              src={travacotLogoNew}
              className="h-10 w-auto object-contain"
            />
          </Link>

          <div className="flex items-center gap-2 sm:hidden">
            {!hideUserIcon && isLoggedIn ? (
              <UserMenu />
            ) : hideUserIcon ? null : (
              <div className="flex items-center gap-2">
                {!isLoginPage && (
                  <Link
                    href="/login"
                    className="font-medium text-xs cursor-pointer text-core border border-core w-[86px] h-10 p-[5px] rounded-[40px] flex items-center justify-center"
                  >
                    Log In
                  </Link>
                )}
                {!isRegisterPager && (
                  <Link
                    href="/register"
                    className="bg-[#F3DFFC] font-bold text-xs cursor-pointer text-core w-[86px] h-10 p-[5px] rounded-[40px] flex items-center justify-center"
                  >
                    Sign Up
                  </Link>
                )}
              </div>
            )}
          </div>

          <div className="hidden sm:flex text-white items-center gap-10 xl:gap-20 text-sm">
            {/* <span className="font-medium">USD</span> */}
            {isLoggedIn ? (
              <>
                <Link
                  href="/reservations"
                  className="font-medium cursor-pointer text-core"
                >
                  Reservations
                </Link>
                <Link
                  href="/favorites"
                  className="font-medium cursor-pointer text-core"
                >
                  Favorites
                </Link>
                <Link
                  href="/messages"
                  className="font-medium cursor-pointer text-core"
                >
                  Messages
                </Link>
                {!hideUserIcon && <UserMenu />}
              </>
            ) : (
              <>
                {/* <span className="font-medium cursor-pointer ">About</span>
                <span className="font-medium cursor-pointer">Partnership</span>
                <span className="font-medium cursor-pointer">
                  Rewards Program
                </span> */}
                <div className="flex items-center gap-2.5">
                  {!isLoginPage && (
                    <Link
                      href="/login"
                      className="font-medium text-xs cursor-pointer text-core border border-core px-4 py-2 rounded-[40px]"
                    >
                      Log In
                    </Link>
                  )}
                  {!isRegisterPager && (
                    <Link
                      href="/register"
                      className="bg-[#F3DFFC] font-bold text-xs cursor-pointer text-core px-4 py-2 rounded-[40px]"
                    >
                      Sign Up
                    </Link>
                  )}
                </div>
              </>
            )}
          </div>
        </div>
      </div>
    </header>
  );
}

const UserMenu = () => {
  const loggedOut = () => {
    removeCookie("access_token");
    signOut({ callbackUrl: "/login" });
  };

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <div className="cursor-pointer">
          <Image
            alt="account travacot"
            src={personLogo}
            width={40}
            height={40}
          />
        </div>
      </DropdownMenuTrigger>

      <DropdownMenuContent align="end" className="min-w-[200px]">
        <DropdownMenuItem className="h-14" asChild>
          <Link href="/profile" className="cursor-pointer">
            Account details
          </Link>
        </DropdownMenuItem>
        <DropdownMenuItem className="h-14 md:hidden" asChild>
          <Link href="/reservations" className="cursor-pointer">
            Reservations
          </Link>
        </DropdownMenuItem>
        <DropdownMenuItem className="h-14 md:hidden" asChild>
          <Link href="/favorites" className="cursor-pointer">
            Favorites
          </Link>
        </DropdownMenuItem>
        <DropdownMenuItem className="h-14 md:hidden" asChild>
          <Link href="/messages" className="cursor-pointer">
            Messages
          </Link>
        </DropdownMenuItem>
        <DropdownMenuItem className="h-14" asChild>
          <Button
            className="cursor-pointer border-none w-full justify-start bg-transparent"
            variant="secondary"
            onClick={loggedOut}
          >
            Sign Out
          </Button>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};
