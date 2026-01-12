import React from "react";

const SkeletonCard = () => (
  <div className="flex flex-col overflow-hidden rounded-[20px] p-1">
    <div className="relative flex-none max-w-[365px] aspect-square md:h-[268px] rounded-[20px] overflow-hidden bg-gray-200 animate-pulse" />
    <div className="flex-none md:h-[103px] py-2 px-1">
      <div className="flex items-start justify-between">
        <div className="flex flex-col gap-2 w-2/3">
          <div className="h-4 bg-gray-200 rounded animate-pulse" />
          <div className="h-3 bg-gray-200 rounded animate-pulse w-1/2" />
        </div>
        <div className="h-5 w-10 bg-gray-200 rounded-[20px] animate-pulse" />
      </div>
      <div className="flex items-center justify-between mt-4">
        <div className="hidden md:flex flex-col gap-2 w-2/3">
          <div className="h-3 bg-gray-200 rounded animate-pulse w-3/4" />
          <div className="h-5 bg-gray-200 rounded animate-pulse w-1/2" />
        </div>
        <div className="h-[25px] w-14 bg-gray-200 rounded-[20px] animate-pulse" />
      </div>
    </div>
  </div>
);

const HotelsSkeleton = () => {
  return (
    <div>
      <div className="flex items-center justify-end gap-2.5 mb-4">
        <div className="bg-white shadow-sm h-10 w-[68px] rounded-[10px] animate-pulse" />
        <div className="bg-white shadow-sm h-10 w-[68px] rounded-[10px] animate-pulse" />
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        {Array.from({ length: 4 }).map((_, i) => (
          <SkeletonCard key={i} />
        ))}
      </div>
    </div>
  );
};

export default HotelsSkeleton;
