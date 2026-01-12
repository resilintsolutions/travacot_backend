"use client";
export const CantFindYourTrip = () => {
  return (
    <div className="h-[500px] mt-10 xl:mt-20 flex xl:items-center lg:justify-center">
      <div className="flex flex-col gap-4 max-w-md text-core">
        <h2 className="font-bold text-base xl:text-[20px]">
          Can&apos;t find your trip?
        </h2>
        <p>
          No problem! Just fill in the field below and we will send you the
          details.
        </p>
        <p>
          or <span className="text-[#5753CE]">view your reservations here</span>
        </p>
        <input
          type="text"
          placeholder="Email"
          className="w-full h-[55px] lg:h-[65px] text-xs px-4 rounded-[30px] bg-[#F8F9FC] focus:outline-none focus:ring-0 placeholder:text-[#7F7F93]"
        />

        <button className="w-20 h-10 rounded-[30px] bg-core text-white text-xs flex items-center justify-center">
          Send
        </button>
      </div>
    </div>
  );
};