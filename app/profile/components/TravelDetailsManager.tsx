"use client";
import { useState } from "react";
import { IoIosAdd, IoIosArrowDown } from "react-icons/io";
import {
  useCreateTraveler,
  useDeleteTraveler,
  useTravelers,
  useUpdateTraveler,
} from "../hooks/useTravelers";
import EditTravelerDialog from "@/components/profile/EditTravelerDialog";
import { CreateTravelerPayload, Traveler } from "../types";
import { formatDate } from "date-fns";

export const TravelerDetailsManager = () => {
  const [traveler, setTraveler] = useState<CreateTravelerPayload>({
    full_name: "Guest One",
    dob: "1995-01-01",
    passport_number: "P123456",
    nationality: "PK",
  });
  const [showForm, setShowForm] = useState<boolean>(false);
  const createTraveler = useCreateTraveler();
  const deleteTraveler = useDeleteTraveler();
  const { data: travelers = [] } = useTravelers();
  const updateTraveler = useUpdateTraveler();

  const handleAddTraveler = async (e: React.FormEvent) => {
    e.preventDefault();
    await createTraveler.mutateAsync(traveler);
    setShowForm(false);
  };

  const handleDeleteTraveler = (id: number) => {
    deleteTraveler.mutate(id);
  };

  return (
    <div className="w-full max-w-xl">
      <div className="flex flex-col gap-4 p-5 rounded-[30px] bg-[#FAFAFC] text-core">
        <h2 className="text-xs font-semibold">Jamal Chatila (Main Traveler)</h2>
        <p className="text-xs text-[#8C8CA0]">
          Add a payment method to speed up your reservation process
        </p>

        <div className="flex gap-6 md:gap-2 text-xs">
          <div className="flex flex-col sm:flex-row">
            <p className="sm:w-[120px]">Date of birth:</p>
            <span className="sm:w-20">N/A</span>
          </div>
          <div className="flex flex-col sm:flex-row">
            <p className="sm:w-[140px]">Gender:</p>
            <span className="shrink-0">N/A</span>
          </div>
          <div className="text-[#3E51CD] ml-auto cursor-pointer">Edit</div>
        </div>

        <div className="flex gap-2 text-xs">
          <div className="flex flex-col sm:flex-row">
            <p className="w-[120px]">Travel Preferences:</p>
            <span className="sm:w-20">N/A</span>
          </div>
          <div className="flex flex-col sm:flex-row">
            <p className="w-[140px]">Wheelchair assistance:</p>
            <span>N/A</span>
          </div>
        </div>

        <div className="border border-[#C9D0E7]"></div>

        {travelers?.length > 0 &&
          travelers.map((travelerItem: Traveler) => (
            <div className="flex flex-col gap-4" key={travelerItem.id}>
              <h2 className="text-xs font-semibold">
                {travelerItem.full_name}
              </h2>
              <p className="text-xs text-[#8C8CA0]">
                Add a payment method to speed up your reservation process
              </p>

              <div className="flex gap-6 md:gap-2 text-xs">
                <div className="flex flex-col sm:flex-row">
                  <p className="sm:w-[120px]">Date of birth:</p>
                  <span className="sm:w-20">
                    {formatDate(new Date(travelerItem.dob), "MM/dd/yyyy")}
                  </span>
                </div>
                <div className="flex flex-col sm:flex-row">
                  <p className="sm:w-[140px]">Gender:</p>
                  <span>{travelerItem.gender || "N/A"}</span>
                </div>
                <div className="text-[#3E51CD] ml-auto">
                  <EditTravelerDialog
                    traveler={travelerItem}
                    trigger={<div className="cursor-pointer">Edit</div>}
                    onSave={async ({ id, data }) => {
                      await updateTraveler.mutateAsync({ id, data });
                    }}
                  />
                </div>
              </div>

              <div className="flex gap-2 text-xs">
                <div className="flex flex-col sm:flex-row">
                  <p className="w-[120px]">Travel Preferences:</p>
                  <span className="sm:w-20">Window</span>
                </div>
                <div className="flex flex-col sm:flex-row">
                  <p className="w-[140px]">Wheelchair assistance:</p>
                  <span>Yes</span>
                </div>
              </div>
              <button
                onClick={() => handleDeleteTraveler(travelerItem.id)}
                className="cursor-pointer w-[120px] bg-[#FF3636] p-2 text-xs text-white rounded-[30px] flex items-center justify-center gap-2"
              >
                Remove traveler
                {deleteTraveler.isPending ? (
                  <span className="loader-border-loader-border-white w-4 h-4 rounded-full border-2 border-t-2 border-white border-t-transparent animate-spin"></span>
                ) : null}
              </button>

              <div className="border border-[#C9D0E7]"></div>
            </div>
          ))}

        {showForm && (
          <div className="flex flex-col rounded-[30px] overflow-hidden">
            <div className="bg-[#F8F9FC] flex flex-col gap-2 p-4 text-xs text-core border-b">
              <label className="font-semibold">First name</label>
              <input
                type="text"
                placeholder="Your name"
                className="focus:outline-none focus:ring-0 placeholder:text-[#7F7F93]"
                value={traveler.full_name.split(" ")[0]}
                onChange={(e) =>
                  setTraveler({
                    ...traveler,
                    full_name:
                      e.target.value +
                      " " +
                      traveler.full_name.split(" ").slice(1).join(" "),
                  })
                }
              />
            </div>
            <div className="bg-[#F8F9FC] flex flex-col gap-2 p-4 text-xs text-core border-b">
              <label className="font-semibold">Middle name</label>
              <input
                type="text"
                placeholder="Your middle name"
                className="focus:outline-none focus:ring-0 placeholder:text-[#7F7F93]"
                value={traveler.full_name.split(" ")[1] || ""}
                onChange={(e) =>
                  setTraveler({
                    ...traveler,
                    full_name:
                      traveler.full_name.split(" ")[0] +
                      " " +
                      e.target.value +
                      " " +
                      traveler.full_name.split(" ").slice(2).join(" "),
                  })
                }
              />
            </div>
            <div className="bg-[#F8F9FC] flex flex-col gap-2 p-4 text-xs text-core border-b">
              <label className="font-semibold">Second name</label>
              <input
                type="text"
                placeholder="Your second name"
                className="focus:outline-none focus:ring-0 placeholder:text-[#7F7F93]"
                value={traveler.full_name.split(" ")[2] || ""}
                onChange={(e) =>
                  setTraveler({
                    ...traveler,
                    full_name:
                      traveler.full_name.split(" ").slice(0, 2).join(" ") +
                      " " +
                      e.target.value,
                  })
                }
              />
            </div>
            <div className="bg-[#F8F9FC] flex flex-col gap-2 p-4 text-xs text-core border-b">
              <label className="font-semibold">Date of Birth</label>
              <input
                type="date"
                className="focus:outline-none focus:ring-0 text-[#7F7F93]"
                value={traveler.dob}
                onChange={(e) =>
                  setTraveler({ ...traveler, dob: e.target.value })
                }
              />
            </div>

            <div className="bg-[#F8F9FC] flex gap-2 text-xs text-core">
              <div className="w-1/2 flex flex-col gap-2 p-4 text-xs text-core border-r">
                <label className="font-semibold">Gender</label>
                <div className="flex items-center justify-between">
                  <select className="appearance-none bg-transparent w-full focus:outline-none">
                    <option value="" className="text-[#7F7F93]">
                      Prefer not to say
                    </option>
                  </select>
                  <IoIosArrowDown className="size-4" />
                </div>
              </div>

              <div className="w-1/2 flex flex-col gap-2 p-4 text-xs text-core">
                <label className="font-semibold">Wheelchair Assistance</label>
                <div className="flex items-center justify-between">
                  <select className="appearance-none bg-transparent w-full focus:outline-none">
                    <option value="" className="text-[#7F7F93]">
                      No
                    </option>
                  </select>
                  <IoIosArrowDown className="size-4" />
                </div>
              </div>
            </div>
          </div>
        )}

        <div className="flex gap-4">
          {!showForm && (
            <button
              onClick={() => setShowForm(true)}
              className="h-10 w-[103px] bg-[#F5F6FA] text-xs text-core rounded-[30px] flex items-center justify-center cursor-pointer"
            >
              <IoIosAdd className="size-5" />
              <span className="font-semibold">Add Traveler</span>
            </button>
          )}

          {showForm && (
            <>
              <button
                onClick={(e) => {
                  handleAddTraveler(e);
                }}
                className="w-[91px] h-10 text-xs bg-core text-white rounded-[30px] flex items-center justify-center gap-2 cursor-pointer"
              >
                Add
                {createTraveler.isPending ? (
                  <span className="loader-border-loader-border-white w-4 h-4 rounded-full border-2 border-t-2 border-white border-t-transparent animate-spin"></span>
                ) : null}
              </button>

              <button
                onClick={() => setShowForm(false)}
                className="w-[91px] h-10 text-xs text-[#FF3636] rounded-[30px] flex items-center justify-center gap-2 cursor-pointer"
              >
                Cancel
              </button>
            </>
          )}
        </div>
      </div>
    </div>
  );
};
