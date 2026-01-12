"use client";

import { useState } from "react";
import Image from "next/image";
import { IoIosHeart, IoIosAdd } from "react-icons/io";
import emptyBox from "@/assets/images/empty-box.png";
import { cn } from "@/lib/utils";
import { useFavoriteList, useRemoveFavorite } from "./hooks/useFavorites";
import { Favorite } from "./types";
import hotelSearch1 from "@/assets/images/hotel-search1.png";

export default function Page() {
  const [activeTab, setActiveTab] = useState<"all" | "hotels" | "flights">(
    "all"
  );
  const { data: allFavorites = [], isLoading } = useFavoriteList("hotel");
  const removeFavoriteMutation = useRemoveFavorite("hotel", 0);

  console.log("All Favorites:", allFavorites);

  const handleRemoveHotel = async (favorite: Favorite) => {
    await removeFavoriteMutation.mutateAsync(favorite.id);
  };

  return (
    <>
      <section className="py-10">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
          <div className="text-core">
            <h2 className="text-xl font-bold mb-5">Favorites</h2>

            <div className="bg-[#F5F6FA] rounded-[20px] flex items-center justify-between gap-2.5 max-w-sm mb-4 p-1 overflow-x-auto lg:overflow-visible">
              <button
                onClick={() => setActiveTab("all")}
                className={cn(
                  "border border-transparent rounded-[30px] py-2 px-5 text-core w-24 flex items-center justify-center",
                  activeTab === "all" && "bg-core text-white"
                )}
              >
                <span className="font-bold text-xs ">All</span>
              </button>
              <button
                onClick={() => setActiveTab("hotels")}
                className={cn(
                  "border border-transparent rounded-[30px] py-2 px-5 text-core w-24 flex items-center justify-center",
                  activeTab === "hotels" && "bg-core text-white"
                )}
              >
                <span className="font-bold text-xs">Hotels</span>
              </button>
              <button
                onClick={() => setActiveTab("flights")}
                className={cn(
                  "border border-transparent rounded-[30px] py-2 px-5 text-core w-24 flex items-center justify-center",
                  activeTab === "flights" && "bg-core text-white"
                )}
              >
                <span className="font-bold text-xs">Flights</span>
              </button>
            </div>

            {isLoading ? (
              <div className="my-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 lg:gap-6">
                {[...Array(8)].map((_, index) => (
                  <div
                    key={index}
                    className="relative max-w-sm h-52 rounded-[20px] overflow-hidden bg-[#FAFAFC] animate-pulse"
                  >
                    <div className="w-full h-full bg-linear-to-r from-gray-200 via-gray-100 to-gray-200" />
                    <div className="absolute bottom-0 left-0 right-0 h-20 bg-gray-300">
                      <div className="flex items-center justify-between p-4 h-full">
                        <div className="w-full">
                          <div className="h-3 bg-gray-400 rounded w-3/4 mb-2" />
                          <div className="h-2 bg-gray-400 rounded w-1/2" />
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            ) : allFavorites.length === 0 || activeTab === "flights" ? (
              <div className="flex flex-col items-center justify-center text-center px-4 min-h-[350px] sm:min-h-[400px] md:min-h-[500px]">
                <div className="w-40 sm:w-52 md:w-64 mb-4">
                  <Image
                    alt="Empty box"
                    src={emptyBox}
                    className="w-full h-auto object-contain"
                    priority
                  />
                </div>

                <p className="font-semibold text-sm sm:text-xl text-core">
                  Hmm... It seems empty here
                </p>

                <p className="text-sm sm:text-base text-core/80">
                  You can start filling up your favorites by liking our
                  offerings!
                </p>
              </div>
            ) : (
              <div className=" my-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 lg:gap-6">
                {allFavorites.map((favorite, index) => (
                  <div
                    key={index}
                    className="group relative max-w-sm h-52 rounded-[20px] overflow-hidden bg-[#FAFAFC]"
                  >
                    <div className="w-full h-full bg-linear-to-br from-blue-100 to-blue-50 flex items-center justify-center">
                      {/* <p className="text-sm text-gray-500">{favorite.name}</p> */}
                      <Image
                        src={
                          favorite.images && favorite.images.length > 0
                            ? favorite.images[0]
                            : hotelSearch1
                        }
                        alt={favorite.name}
                        width={200}
                        height={150}
                        className="w-full h-full object-cover"
                      />
                    </div>
                    <div className="absolute inset-0 bg-white opacity-0 rounded-2xl transition-opacity duration-300 group-hover:opacity-10" />

                    <div className="absolute bottom-0 left-0 right-0 h-20 bg-core/50 backdrop-blur-xl">
                      <div className="flex items-center justify-between p-4 text-white h-full">
                        <div>
                          <h3 className="text-xs font-semibold">
                            {favorite.name}
                          </h3>
                          <p className="text-[10px]">{favorite.category}</p>
                        </div>

                        <div>
                          <button
                            className="bg-[#FF7070] rounded-full w-9 h-9 flex items-center justify-center hover:bg-[#FF7070]/90 transition-colors disabled:opacity-50"
                            onClick={() => handleRemoveHotel(favorite)}
                            disabled={removeFavoriteMutation.isPending}
                          >
                            <IoIosHeart className="w-4 h-4" />
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}

            {allFavorites.length > 0 && activeTab !== "flights" && (
              <button className="flex items-center justify-center gap-2 bg-[#F5F6FA] rounded-full py-2 px-4 sm:px-5 sm:py-2.5">
                <IoIosAdd className="size-7" />
                <span className="font-semibold text-xs text-core text-center">
                  Would you like to plan a trip?
                </span>
              </button>
            )}
          </div>
        </div>
      </section>
    </>
  );
}
