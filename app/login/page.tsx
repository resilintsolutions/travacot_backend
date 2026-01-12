"use client";
import { useState } from "react";
import { IoIosMail } from "react-icons/io";
import { motion, AnimatePresence } from "motion/react";
import Image from "next/image";
// import Link from "next/link";
// import travacotLogoNew from "@/assets/images/travacot-logo-login.svg";
import googleLogo from "@/assets/images/logo-google.png";
// import facebookLogo from "@/assets/images/logo-facebook.jpg";
import AnimatedMarquee from "@/components/features/login/AnimatedMarquee";
import PrivacyPolicy from "@/components/features/terms/PrivacyPolicy";
import TermsConditions from "@/components/features/terms/TermsAndConditions";
import CancellationPolicy from "@/components/features/terms/CancellationPolicy";
import { useLogin } from "./hooks/useAuth";
import { toast } from "sonner";
import { signIn } from "next-auth/react";
// import MainHeader from "@/components/shared/MainHeader";
export default function Page() {
  const loginMutation = useLogin();

  const [showLoginForm, setShowLoginForm] = useState<boolean>(false);
  const [showInputPass, setShowInputPass] = useState<boolean>(false);
  const [direction, setDirection] = useState<"forward" | "backward">("forward");
  const [loginCredentials, setLoginCredentials] = useState<{
    email: string;
    password: string;
  }>({
    email: "",
    password: "",
  });

  const handleLogin = () => {
    if (!loginCredentials.password) {
      return toast.error("Please enter password", {
        duration: 2000,
        position: "top-center",
      });
    }
    if ((loginCredentials.password || "").length < 8) {
      return toast.error("Password must be at least 8 characters", {
        duration: 2000,
        position: "top-center",
      });
    }
    loginMutation.mutate(loginCredentials);
  };

  const handleEmailNext = () => {
    if (!loginCredentials.email) {
      return toast.error("Please enter your email address", {
        duration: 2000,
        position: "top-center",
      });
    }
    setDirection("forward");
    setShowInputPass(true);
  };

  const handleGoogleLogin = () => {
    // Calling signIn with the provider name ('google') starts the OAuth flow.
    // The callbackUrl specifies where to redirect after successful login.
    signIn("google", { callbackUrl: "/" });
  };

  // const handleFacebookLogin = () => {
  //   signIn("facebook", { callbackUrl: "/" });
  // };

  return (
    <div className="flex flex-col h-screen overflow-hidden">
      {/* <MainHeader /> */}
      {/* <header className="bg-[#FAFAFCE5] z-50 border border-[#C9D0E7]">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <div className="flex items-center justify-between h-[70px] md:h-20">
            <Link href="/" className="w-[126px] flex items-center h-full">
              <Image
                alt="travacot logo"
                src={travacotLogoNew}
                className="h-10 w-auto object-contain"
              />
            </Link>

            <div>
              <Link
                href="/register"
                className="bg-[#F3DFFC] font-bold text-xs cursor-pointer text-core px-4 py-2 rounded-[40px]"
              >
                Sign Up
              </Link>
            </div>
          </div>
        </div>
      </header> */}

      <main className="h-screen flex items-center justify-center text-core">
        <div className="container mx-auto px-5 lg:px-20">
          <div className="flex flex-col-reverse lg:flex-row items-center justify-center md:gap-20 lg:gap-40 lg:justify-between">
            <div className="flex flex-col items-center gap-10 w-full lg:w-[360px]">
              <h2 className="font-bold text-2xl">Let&apos;s login!</h2>
              <p className="font-semibold text-lg">
                {showLoginForm
                  ? "Sign in or create an account"
                  : "How would you like to login?"}
              </p>

              <div className="w-[298px] relative h-[170px]">
                <AnimatePresence mode="wait">
                  {!showLoginForm && (
                    <motion.div
                      key="loginButtons"
                      className="flex flex-col gap-3"
                      initial={{
                        opacity: 0,
                        x: direction === "forward" ? -50 : 50,
                      }}
                      animate={{ opacity: 1, x: 0 }}
                      exit={{
                        opacity: 0,
                        x: direction === "forward" ? 50 : -50,
                      }}
                      transition={{ duration: 0.5, ease: "easeInOut" }}
                    >
                      <button
                        onClick={handleGoogleLogin}
                        className="cursor-pointer w-full flex items-center border border-gray-400 rounded-full py-3 px-6"
                      >
                        <div className="flex ml-8 items-center gap-3 w-64 mx-auto justify-start">
                          <Image
                            alt="Google logo"
                            src={googleLogo}
                            width={24}
                            height={24}
                            className="w-6 h-6 object-contain shrink-0"
                          />
                          <span className="text-sm font-semibold leading-none">
                            Continue with Google
                          </span>
                        </div>
                      </button>

                      {/* <button
                        onClick={handleFacebookLogin}
                        className="cursor-pointer w-full flex items-center border border-gray-400 rounded-full py-3 px-6"
                      >
                        <div className="flex ml-8 items-center gap-3 w-64 mx-auto justify-start">
                          <Image
                            alt="Facebook logo"
                            src={facebookLogo}
                            width={24}
                            height={24}
                            className="w-6 h-6 object-contain shrink-0"
                          />
                          <span className="text-sm font-semibold leading-none">
                            Continue with Facebook
                          </span>
                        </div>
                      </button> */}

                      <button
                        onClick={() => {
                          setDirection("forward");
                          setShowLoginForm(true);
                        }}
                        className="cursor-pointer w-full flex items-center border border-gray-400 rounded-full py-3 px-6"
                      >
                        <div className="flex ml-8 items-center gap-3 w-64 mx-auto justify-start">
                          <IoIosMail className="w-6 h-6 shrink-0" />
                          <span className="text-sm font-semibold leading-none">
                            Continue with Email
                          </span>
                        </div>
                      </button>
                    </motion.div>
                  )}

                  {showLoginForm && !showInputPass && (
                    <motion.div
                      key="emailStep"
                      className="flex flex-col gap-4"
                      initial={{
                        opacity: 0,
                        x: direction === "forward" ? -50 : 50,
                      }}
                      animate={{ opacity: 1, x: 0 }}
                      exit={{
                        opacity: 0,
                        x: direction === "forward" ? 50 : -50,
                      }}
                      transition={{ duration: 0.5, ease: "easeInOut" }}
                    >
                      <input
                        type="email"
                        placeholder="Email"
                        required
                        value={loginCredentials.email}
                        onChange={(e) =>
                          setLoginCredentials({
                            ...loginCredentials,
                            email: e.target.value,
                          })
                        }
                        className="w-full py-2.5 px-4 rounded-full border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400"
                      />
                      <button
                        onClick={handleEmailNext}
                        className="cursor-pointer w-full py-2.5 rounded-full bg-core text-white font-medium hover:bg-[#2E2C59] transition"
                      >
                        Next
                      </button>
                      <button
                        onClick={() => {
                          setDirection("backward");
                          setShowLoginForm(false);
                        }}
                        className="cursor-pointer w-full py-2.5 rounded-full border border-gray-200 bg-surface font-medium"
                      >
                        Back
                      </button>
                    </motion.div>
                  )}

                  {/* Step 2: Password input */}
                  {showLoginForm && showInputPass && (
                    <motion.div
                      key="passwordStep"
                      className="flex flex-col gap-4"
                      initial={{
                        opacity: 0,
                        x: direction === "forward" ? -50 : 50,
                      }}
                      animate={{ opacity: 1, x: 0 }}
                      exit={{
                        opacity: 0,
                        x: direction === "forward" ? 50 : -50,
                      }}
                      transition={{ duration: 0.5, ease: "easeInOut" }}
                    >
                      <input
                        type="password"
                        placeholder="Password"
                        value={loginCredentials.password}
                        onChange={(e) =>
                          setLoginCredentials({
                            ...loginCredentials,
                            password: e.target.value,
                          })
                        }
                        className="w-full py-2.5 px-4 rounded-full border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400"
                      />
                      <button
                        type="button"
                        onClick={handleLogin}
                        disabled={loginMutation.isPending}
                        className={`w-full py-2.5 rounded-full bg-core text-white font-medium transition flex items-center justify-center ${
                          loginMutation.isPending
                            ? "opacity-70 cursor-not-allowed"
                            : "hover:bg-[#2E2C59]"
                        }`}
                      >
                        <span>Login</span>
                        {loginMutation.isPending && (
                          <span className="ml-2 inline-block w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin" />
                        )}
                      </button>
                      <button
                        onClick={() => {
                          setDirection("backward");
                          setShowInputPass(false);
                        }}
                        className="cursor-pointer w-full py-2.5 rounded-full border border-gray-200 bg-surface font-medium"
                      >
                        Back
                      </button>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>

              <div className="text-center">
                <a
                  href="#"
                  className="text-xs md:text-sm text-blue-400 underline hover:text-blue-500 transition"
                >
                  Forgot Password?
                </a>
              </div>
            </div>

            <div
              className="hidden lg:block w-px h-[570px] bg-gray-300"
              aria-hidden="true"
            ></div>

            <div className="hidden md:flex-1 md:flex md:flex-col max-w-2xl">
              <h2 className="text-xl sm:text-2xl">
                We think you need to know this, <br /> We&apos;re bringing{" "}
                <strong>flights</strong> and <strong>transports</strong> soon!
              </h2>

              <div className="mt-10 flex flex-col gap-2">
                <AnimatedMarquee duration={25} />
                <AnimatedMarquee duration={40} />
                <AnimatedMarquee duration={30} />
              </div>
            </div>
          </div>
        </div>
      </main>

      <footer className="mt-auto py-4 max-w-xs sm:max-w-full mx-auto text-sm text-gray-500 text-center sm:text-left">
        By signing up you accept our <PrivacyPolicy />, <TermsConditions />, and{" "}
        <CancellationPolicy />.
      </footer>
    </div>
  );
}
