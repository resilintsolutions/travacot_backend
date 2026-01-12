"use client";
import { removeCookie } from "@/lib/cookies";
import { FaSave } from "react-icons/fa";
import { FiLogOut } from "react-icons/fi";
import { IoIosArrowDown } from "react-icons/io";
import { UserProfile } from "../types";
import { useState } from "react";
import { useUpdateProfile, useChangePassword } from "../hooks/useProfile";
import ChangePasswordDialog from "@/components/profile/ChangePasswordDialog";
import { signOut } from "next-auth/react";

type Props = {
  profile: UserProfile;
};

export const AccountProfileForm = ({ profile }: Props) => {
  const [userProfile, setUserProfile] = useState<UserProfile>(profile);
  const updateMutation = useUpdateProfile();
  const changePassword = useChangePassword();

  const handleProfileUpdate = (e: React.FormEvent) => {
    e.preventDefault();
    updateMutation.mutate(userProfile);
  };
  const handleLoggedOut = () => {
    removeCookie("access_token");
    signOut({ callbackUrl: "/login" });
  };

  return (
    <div className="w-full lg:max-w-xl flex flex-col gap-4">
      <div className="p-4 rounded-[30px] bg-[#FAFAFC] text-core text-xs flex items-center justify-between">
        <div>
          <h3 className="font-semibold mb-2">Credits</h3>
          <p className="text-[#8C8CA0] max-w-xs">
            Credits are points given to you after you complete the cards above.
          </p>
        </div>
        <span className="shrink-0">0 Credits</span>
      </div>

      <div className="flex flex-col rounded-[30px] overflow-hidden">
        <div className="bg-[#F8F9FC] flex flex-col gap-2 p-4 text-xs text-core border-b">
          <label className="font-semibold">First name</label>
          <input
            type="text"
            placeholder="Your name"
            value={userProfile.name.split(" ")[0] || ""}
            onChange={(e) =>
              setUserProfile({
                ...userProfile,
                name:
                  e.target.value +
                  " " +
                  userProfile.name.split(" ").slice(1).join(" "),
              })
            }
            className="focus:outline-none focus:ring-0 placeholder:text-[#7F7F93]"
          />
        </div>
        <div className="bg-[#F8F9FC] flex flex-col gap-2 p-4 text-xs text-core border-b">
          <label className="font-semibold">Last name</label>
          <input
            type="text"
            value={userProfile.name.split(" ").slice(1).join(" ") || ""}
            onChange={(e) =>
              setUserProfile({
                ...userProfile,
                name: userProfile.name.split(" ")[0] + " " + e.target.value,
              })
            }
            placeholder="Your last name"
            className="focus:outline-none focus:ring-0 placeholder:text-[#7F7F93]"
          />
        </div>
        <div className="bg-[#F8F9FC] flex flex-col gap-2 p-4 text-xs text-core border-b">
          <label className="font-semibold">Gender</label>
          <div className="flex items-center justify-between">
            <select
              value={userProfile.gender || ""}
              onChange={(e) =>
                setUserProfile({
                  ...userProfile,
                  gender: e.target.value,
                })
              }
              className="appearance-none bg-transparent w-full focus:outline-none"
            >
              <option value="" className="text-[#7F7F93]">
                Prefer not to say
              </option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
            <IoIosArrowDown className="size-4" />
          </div>
        </div>
        <div className="bg-[#F8F9FC] flex gap-2 text-xs text-core">
          <select className="p-4 text-xs border-r border-[#D9D9D9]">
            <option value="">USA +1</option>
          </select>

          <input
            type="number"
            value={userProfile.phone_number || ""}
            onChange={(e) =>
              setUserProfile({
                ...userProfile,
                phone_number: e.target.value,
              })
            }
            placeholder="Phone number"
            className="w-full focus:ring-0 focus:outline-none text-xs p-4 placeholder:text-[#7F7F93]"
          />
        </div>

        <div className="bg-[#F8F9FC] flex gap-2 p-4 text-xs text-core justify-end">
          <button
            onClick={handleProfileUpdate}
            className="bg-core cursor-pointer p-2 w-22 text-white rounded-[30px] flex items-center justify-center gap-2"
          >
            <FaSave />
            <span className="font-semibold text-xs">Save</span>
            {updateMutation.isPending ? (
              <span className="loader-border-loader-border-white w-4 h-4 rounded-full border-2 border-t-2 border-white border-t-transparent animate-spin"></span>
            ) : null}
          </button>
        </div>
      </div>

      <div className="bg-[#F8F9FC] rounded-[30px] flex items-center justify-between gap-2 p-4 text-xs text-core">
        <div className="flex flex-col gap-2">
          <label className="font-semibold">Password</label>
          <input
            type="password"
            placeholder="**********"
            className="focus:outline-none focus:ring-0 placeholder:text-[#7F7F93]"
          />
        </div>
        <ChangePasswordDialog
          label="Change"
          onChangePassword={async (payload) => {
            await changePassword.mutateAsync(payload);
          }}
        />
      </div>

      <button
        onClick={handleLoggedOut}
        className="bg-[#FFEDED] cursor-pointer rounded-[20px] h-12 flex items-center gap-2 px-6 text-[#A82828]"
      >
        <span className="font-semibold text-xs">Logout</span>
        <FiLogOut />
      </button>
    </div>
  );
};
