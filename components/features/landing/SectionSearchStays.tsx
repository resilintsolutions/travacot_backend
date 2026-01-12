"use client";
import BookingSearchForm from "@/components/shared/booking-search-form";
import { useSession } from "next-auth/react";
import { useEffect, useState } from "react";
import mainBgImage from "@/assets/images/main-page-image.jpg";
import staysIcon from "@/assets/images/stays-icon.svg";
import { Button } from "@/components/ui/button";
import Image from "next/image";

export default function SectionSearchStays() {
  const [userName, setUserName] = useState<string>("There");
  const session = useSession();

  useEffect(() => {
    const sessionName = session.data?.user.name || "";
    // eslint-disable-next-line react-hooks/set-state-in-effect
    setUserName(sessionName || "There");
  }, [session]);

  return (
    <section className="relative mt-0 md:mt-4 w-full">
      <div className="container mx-auto md:pl-4 md:sm:pl-6 lg:pl-8 xl:pl-10 md:pr-2 md:sm:pr-3 lg:pr-4 xl:pr-5">
        <div className="relative w-full h-[305px] md:h-[500px] lg:h-[732px] md:max-w-none md:w-full flex items-center rounded-none md:rounded-[20px] overflow-hidden">
          <div
            aria-hidden
            className="absolute inset-0 overflow-hidden rounded-none md:rounded-[20px]"
          >
            <Image
              src={mainBgImage}
              alt="Background"
              fill
              className="object-cover blur-[2.9px] scale-110"
              priority
            />
            <div className="absolute inset-0 bg-[rgba(34,33,65,0.6)]" />
          </div>
          <div className="relative z-10 w-full pl-4 sm:pl-6 md:pl-6 lg:pl-8 xl:pl-10 pr-2 sm:pr-3 md:pr-3 lg:pr-4 xl:pr-5">
            <div
              className="w-full max-w-xl sm:max-w-2xl md:max-w-3xl lg:max-w-5xl text-white py-4"
              data-username={userName}
            >
              <h2
                className="mb-2 font-semibold text-[17px] leading-none tracking-[0px] md:text-[28px] md:leading-8 lg:font-bold lg:text-[40px] lg:leading-10"
                style={{ fontFamily: "Inter" }}
              >
                Hey {userName}
              </h2>
              <p
                className="mb-4 text-[19px] leading-none tracking-[-0.03em] font-normal md:text-[28px] md:leading-8 lg:text-[40px] lg:leading-10 md:tracking-[-0.05em]"
                style={{ fontFamily: "Inter" }}
              >
                Let&apos;s{" "}
                <span
                  className="text-spot font-bold text-[19px] leading-none tracking-[-0.03em] md:text-[28px] md:leading-8 lg:text-[40px] lg:leading-10 md:tracking-[-0.05em]"
                  style={{ fontFamily: "Inter" }}
                >
                  find
                </span>{" "}
                your perfect stay!
              </p>
              <div className="flex gap-2.5 mb-2.5">
                <Button className="w-[100px] md:w-[140px] lg:w-[180px] h-10 md:h-11 lg:h-10 rounded-[27px] px-5 py-2.5 bg-[#FFFFFF] text-core gap-2.5 hover:bg-white">
                  <Image
                    src={staysIcon}
                    alt="Explore Icon"
                    width={17}
                    height={14}
                    className="inline-block"
                  />
                  <span className="text-xs font-bold">Stays</span>
                </Button>
                <Button className="w-[114px] md:w-[150px] lg:w-[179.5px] h-10 md:h-11 lg:h-10 rounded-[27px] px-5 py-2.5 bg-[#F0EFFF4D] text-[#FFFFFF] gap-2.5 backdrop-blur-[11.199999809265137px] hover:bg-[#F0EFFF4D]">
                  <span
                    className="text-xs font-bold"
                    style={{
                      fontFamily: "Inter",
                      fontWeight: 700,
                      fontStyle: "normal",
                      fontSize: "12px",
                      lineHeight: "12px",
                      letterSpacing: "0px",
                    }}
                  >
                    Marketplace
                  </span>
                </Button>
              </div>
              <BookingSearchForm
                useMobile={true}
                isCompact={true}
                formClassName="hover:bg-[#F3E9FF]"
              />
              <p
                className="hidden lg:block mt-2"
                style={{
                  fontFamily: "Inter",
                  fontWeight: 400,
                  fontStyle: "normal",
                  fontSize: "25px",
                  lineHeight: "25px",
                  letterSpacing: "-0.05em",
                }}
              >
                Make the most out of Travacot by downloading the app!
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
