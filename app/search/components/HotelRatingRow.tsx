export default function HotelRatingRow() {
  return (
    <div className="flex items-center gap-3 text-[#2e2e48]">
      <div
        className="inline-flex items-center justify-center w-[39px] h-[30px] px-2.5 py-[3px] gap-2.5 rounded-[61px] border border-[#8B90AA] bg-[#EFF1F9] opacity-100"
        style={{ transform: "rotate(0deg)" }}
      >
        <span className="text-xs font-medium leading-none mt-px">-</span>
      </div>
    </div>
  );
}
