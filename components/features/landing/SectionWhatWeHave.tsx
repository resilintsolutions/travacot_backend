"use client";

import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import WhatWeHaveCarousel from "./WhatWeHaveCarousel";

export default function SectionWhatWeHave() {
  return (
    <section className="overflow-hidden mt-10">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
        <h2 className="font-semibold text-core text-[17px] mb-2">
          Here&apos;s what we have
        </h2>

        <Tabs defaultValue="hotels" className="relative">
          <TabsList className="bg-white h-10 flex gap-4">
            <TabsTrigger
              value="hotels"
              className="font-normal border-l-0 border-r-0 border-t-0 border-b-2 px-0 border-white rounded-none h-full data-[state=active]:font-semibold data-[state=active]:shadow-none data-[state=active]:border-core"
            >
              Hotels
            </TabsTrigger>
            <TabsTrigger
              value="apartments"
              className="font-normal border-l-0 border-r-0 border-t-0 border-b-2 px-0 border-white rounded-none h-full data-[state=active]:font-semibold data-[state=active]:shadow-none data-[state=active]:border-core"
            >
              Apartments
            </TabsTrigger>
            <TabsTrigger
              value="resorts"
              className="font-normal border-l-0 border-r-0 border-t-0 border-b-2 px-0 border-white rounded-none h-full data-[state=active]:font-semibold data-[state=active]:shadow-none data-[state=active]:border-core"
            >
              Resorts
            </TabsTrigger>
            <TabsTrigger
              value="villas"
              className="font-normal border-l-0 border-r-0 border-t-0 border-b-2 px-0 border-white rounded-none h-full data-[state=active]:font-semibold data-[state=active]:shadow-none data-[state=active]:border-core"
            >
              Villas
            </TabsTrigger>
          </TabsList>
          <TabsContent value="hotels">
            <WhatWeHaveCarousel exploreBtnTitle="Explore more hotels" />
          </TabsContent>
          <TabsContent value="apartments">
            <WhatWeHaveCarousel exploreBtnTitle="Explore more apartments" />
          </TabsContent>
          <TabsContent value="resorts">
            <WhatWeHaveCarousel exploreBtnTitle="Explore more resorts" />
          </TabsContent>
          <TabsContent value="villas">
            <WhatWeHaveCarousel exploreBtnTitle="Explore more villas" />
          </TabsContent>
        </Tabs>
      </div>
    </section>
  );
}
