import Image from "next/image";
import creditCard0 from "@/assets/images/credit-card-0.svg";
import xMark from "@/assets/images/error-mark.svg";
import paymenChoiceHotel from "@/assets/images/payment-hotel-choice.png";

export default function Page() {
  return (
    <div className="min-h-screen flex flex-col">
      <div className="flex-1 flex flex-col md:flex-row items-center justify-center gap-4 md:gap-0 my-2">
        <h2 className="md:hidden text-core text-left font-semibold w-full px-4 text-xl">
          Booking Failed
        </h2>

        <div className="w-full md:max-w-sm md:rounded-[20px] overflow-hidden p-4 border border-[#C9D0E7] relative md:ml-4">
          <div className="absolute inset-0 p-0.5">
            <div
              style={{ backgroundImage: `url(${paymenChoiceHotel.src})` }}
              className="bg-cover bg-no-repeat size-full rounded-[20px]"
            ></div>
          </div>

          <div className="absolute inset-0 bg-white/90 backdrop-blur-2xl"></div>

          <div className="hidden md:block relative rounded-[20px] w-full h-[345px] overflow-hidden mb-4">
            <Image
              alt="Hotel choice image"
              src={paymenChoiceHotel}
              fill
              className="object-cover"
            />
          </div>

          <div className="relative flex items-center gap-4 w-fit mb-4">
            <Image alt="xmark" src={xMark} className="h-8 w-8 object-contain" />
            <h4 className="hidden md:block text-[#A82828] font-semibold">
              Booking failed.
            </h4>
            <h4 className="md:hidden text-[#A82828] font-semibold">
              Booking failed, it happens...
            </h4>
          </div>

          <div className="relative flex flex-col mb-4">
            <h4 className="font-semibold text-core">Confirmation Number</h4>
            <p className="text-core">1112234566</p>
          </div>

          <div className="relative flex gap-4 mb-4">
            <div className="text-core flex flex-col items-start">
              <h3 className="font-semibold">Check-in</h3>
              <p>Feb. 3, 2025</p>
            </div>
            <div className="text-core flex flex-col items-start">
              <h3 className="font-semibold">Check-out</h3>
              <p>Feb. 3, 2025</p>
            </div>
          </div>

          <p className="relative text-core mb-6">You booked 8 nights!</p>
        </div>

        <div className="w-full max-w-xl flex flex-col px-4">
          <div className="hidden md:flex items-center gap-2 mb-4">
            <div className="flex flex-col text-core">
              <h3 className="font-semibold text-lg">
                It looks like a payment problem...
              </h3>
              <p className="text-xs">
                Let&apos;s take a look over the payment details
              </p>
            </div>
          </div>

          <div className="mb-8">
            <div className="flex flex-col mb-4">
              <h3 className="font-semibold text-core">Payment Type</h3>
              <p className="md:hidden text-xs text-core">
                We couldn&apos;t deduct the amount from your card!
              </p>
            </div>

            <div className="flex items-center gap-3 mb-4">
              <button className="bg-[#A82828] text-white h-10 w-[140px] flex items-center justify-center gap-2 rounded-full cursor-pointer">
                <span className="font-semibold text-xs">Card</span>
              </button>

              <button
                disabled={true}
                className="bg-[#F4F6FA] border border-[#D7DCE8] text-[#A9B0C5] h-10 w-[140px] flex items-center justify-center gap-2 rounded-full cursor-pointer"
              >
                <span className="font-semibold text-xs">Cash</span>
              </button>
            </div>

            <div className="bg-[#FFC2C2] md:rounded-full h-16 flex items-center justify-between px-4 mb-4 -mx-4 md:mx-0">
              <div className="flex items-center gap-4">
                <div className="flex flex-col gap-2">
                  <span className="font-semibold text-xs">Jamal Chatila</span>
                  <span className="text-xs">Ending with 4581</span>
                </div>

                <Image
                  alt="credit card"
                  src={creditCard0}
                  className="w-[46px] h-[30px]"
                />
              </div>

              <button className="font-bold text-xs text-[#3E51CD] h-[35px] px-5 cursor-pointer">
                Change
              </button>
            </div>
          </div>

          <div className="flex items-center gap-4">
            <button className="h-10 w-1/2 rounded-[30px] bg-core text-white font-semibold text-xs flex items-center justify-center">
              Retry
            </button>
            <button className="h-10 w-1/2 rounded-[30px] bg-white border border-core text-core font-semibold text-xs flex items-center justify-center">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
