"use client";
import Image from "next/image";
import personLogo from "@/assets/images/person-placeholder.svg";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { useProfile } from "./hooks/useProfile";
import { AccountProfileForm } from "./components/AccountProfileForm";
import { CantFindYourTrip } from "./components/CanFindYourTrip";
import { PaymentCardForm } from "./components/PaymentCardForm";
import { TravelerDetailsManager } from "./components/TravelDetailsManager";
import ProfileLoader from "./loading";
// import appStore from "@/assets/images/appstore-logo.png";
// import playstore from "@/assets/images/gogleplay-logo.png";

export default function Page() {
  const { data: profile, isLoading } = useProfile(true);

  if (isLoading) {
    return <ProfileLoader />;
  }

  return (
    <>
      <section className="-mt-0.5 bg-core overflow-hidden">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <div className="flex flex-wrap items-center justify-between text-white py-4 md:py-8 gap-4 md:gap-8">
            <div className="flex items-center gap-4">
              <div className="shrink-0">
                <Image
                  alt="account travacot"
                  src={personLogo}
                  className="w-12 h-12"
                />
              </div>

              <div className="flex flex-col">
                <h2 className="font-bold mb-3">Hello, {profile?.name}</h2>
                <h3 className="font-semibold">Account Email</h3>
                <p>{profile?.email}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="-mt-0.5 overflow-hidden">
        <Tabs defaultValue="general" className="w-full">
          <div className="bg-core">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
              <TabsList className="bg-transparent h-16 py-2 gap-4">
                <TabsTrigger
                  value="general"
                  className="md:w-[190px] text-white data-[state=active]:font-bold data-[state=active]:text-white data-[state=active]:bg-transparent data-[state=active]:border-b-4 data-[state=active]:border-white data-[state=active]:border-t-0 data-[state=active]:border-x-0 data-[state=active]:rounded-none"
                >
                  General{" "}
                  <span className="hidden md:block">Info and Security</span>
                </TabsTrigger>
                <TabsTrigger
                  value="payment"
                  className="md:w-[190px] text-white data-[state=active]:font-bold data-[state=active]:text-white data-[state=active]:bg-transparent data-[state=active]:border-b-4 data-[state=active]:border-white data-[state=active]:border-t-0 data-[state=active]:border-x-0 data-[state=active]:rounded-none"
                >
                  Payment <span className="hidden md:block">Info</span>
                </TabsTrigger>
                <TabsTrigger
                  value="info"
                  className="md:w-[190px] text-white data-[state=active]:font-bold data-[state=active]:text-white data-[state=active]:bg-transparent data-[state=active]:border-b-4 data-[state=active]:border-white data-[state=active]:border-t-0 data-[state=active]:border-x-0 data-[state=active]:rounded-none"
                >
                  Travelers <span className="hidden md:block">& Family</span>
                </TabsTrigger>
              </TabsList>
            </div>
          </div>
          <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
            <TabsContent
              value="general"
              className="flex flex-col lg:flex-row gap-4 py-4"
            >
              <AccountProfileForm
                profile={
                  profile || {
                    id: "",
                    name: "",
                    email: "",
                  }
                }
              />
              <div className="hidden xl:block h-[500px] border xl:ml-48 xl:mr-20 mt-10"></div>
              <CantFindYourTrip />
            </TabsContent>
            <TabsContent
              value="payment"
              className="flex flex-col lg:flex-row gap-4 py-4"
            >
              <PaymentCardForm />
              <div className="hidden xl:block h-[500px] border ml-48 mr-20 mt-10"></div>
              <CantFindYourTrip />
            </TabsContent>
            <TabsContent
              value="info"
              className="flex flex-col lg:flex-row gap-4 py-4"
            >
              <TravelerDetailsManager />
              <div className="hidden xl:block h-[500px] border ml-48 mr-20 mt-10"></div>
              <CantFindYourTrip />
            </TabsContent>
          </div>
        </Tabs>
      </section>
    </>
  );
}
