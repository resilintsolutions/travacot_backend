"use client";
import { useState } from "react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { ChevronDown, Eye, EyeOff, Upload, X } from "lucide-react";
import hardCheck from "@/assets/images/hard-check-icon.svg";
import checkIcon from "@/assets/images/check-icon.svg";
import idIcon from "@/assets/images/id-icon.svg";
import faceIdIcon from "@/assets/images/face-id-icon.svg";
import rightArrow from "@/assets/images/right-arrow-icon-white.svg";
import rightArrowBlue from "@/assets/images/right-arrow-icon.svg";

import { cn } from "@/lib/utils";
import Image from "next/image";

const countries = [
  { name: "United States", code: "US", phoneCode: "+1" },
  { name: "United Kingdom", code: "GB", phoneCode: "+44" },
  { name: "Canada", code: "CA", phoneCode: "+1" },
  { name: "Australia", code: "AU", phoneCode: "+61" },
  { name: "Germany", code: "DE", phoneCode: "+49" },
  { name: "France", code: "FR", phoneCode: "+33" },
  { name: "Spain", code: "ES", phoneCode: "+34" },
  { name: "Italy", code: "IT", phoneCode: "+39" },
  { name: "Japan", code: "JP", phoneCode: "+81" },
  { name: "China", code: "CN", phoneCode: "+86" },
  { name: "India", code: "IN", phoneCode: "+91" },
  { name: "Brazil", code: "BR", phoneCode: "+55" },
  { name: "Mexico", code: "MX", phoneCode: "+52" },
  { name: "South Africa", code: "ZA", phoneCode: "+27" },
];

interface SignUpFormData {
  firstName: string;
  familyName: string;
  email: string;
  password: string;
  confirmPassword: string;
  country: string;
  city: string;
  address: string;
  phoneCode: string;
  phoneNumber: string;
  // Step 2 fields
  verificationFirstName: string;
  verificationFamilyName: string;
  idDocument: File | null;
  selfieDocument: File | null;
}

const SignUpPage = () => {
  const [currentStep, setCurrentStep] = useState(1);
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isVerified, setIsVerified] = useState(false);

  const [formData, setFormData] = useState<SignUpFormData>({
    firstName: "",
    familyName: "",
    email: "",
    password: "",
    confirmPassword: "",
    country: "",
    city: "",
    address: "",
    phoneCode: "+1",
    phoneNumber: "",
    verificationFirstName: "",
    verificationFamilyName: "",
    idDocument: null,
    selfieDocument: null,
  });

  const handleInputChange = (field: keyof SignUpFormData, value: string) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
  };

  const handleFileUpload = (
    field: "idDocument" | "selfieDocument",
    file: File
  ) => {
    setFormData((prev) => ({ ...prev, [field]: file }));
  };

  const handleNext = () => {
    // Add validation here before proceeding to step 2
    setCurrentStep(2);
  };

  const handleSkip = () => {
    // Handle skip logic - show completion without verification
    setIsVerified(false);
    setCurrentStep(3);
  };

  const handleVerify = () => {
    // Handle verification logic - show completion with verification
    setIsVerified(true);
    setCurrentStep(3);
  };

  return (
    <div className="flex items-center justify-center py-6 sm:py-8 lg:py-12 px-4">
      <div className="flex flex-col items-center space-y-6">
        {currentStep < 3 && (
          <div className="space-y-4 w-full max-w-[470px]">
            <h1 className="text-center text-2xl sm:text-3xl font-bold text-core">
              Sign Up
            </h1>

            <div className="space-y-3">
              <div className="flex items-center gap-3">
                <div
                  className={cn(
                    "flex items-center justify-center rounded-full shrink-0 text-white font-bold w-12 h-12 text-xs",
                    currentStep === 1 ? "bg-[#5F5506]" : "bg-[#065F46]"
                  )}
                >
                  1
                </div>
                <div
                  className={cn(
                    "flex-1 rounded-full text-white font-semibold px-4 py-3 text-xs",
                    currentStep === 1
                      ? "bg-[#5F5506]"
                      : "bg-[#065F46] text-white"
                  )}
                >
                  Signing up for rewards and benefits
                </div>
              </div>

              <div className="flex items-center gap-3">
                <div
                  className={cn(
                    "flex items-center justify-center rounded-full shrink-0 text-core font-bold w-12 h-12 text-xs",
                    currentStep === 2
                      ? "bg-[#5F5506] text-white"
                      : "bg-[#F5F6FA]"
                  )}
                >
                  2
                </div>
                <div
                  className={cn(
                    "flex-1 rounded-full px-4 py-3 text-xs",
                    currentStep === 2
                      ? "bg-[#5F5506] text-white"
                      : "bg-[#F5F6FA] text-core"
                  )}
                >
                  (Optional) Marketplace Accessibility
                </div>
              </div>
            </div>
          </div>
        )}

        {currentStep === 1 && (
          <div className="w-full max-w-[500px] overflow-y-auto p-4 sm:p-6">
            <div className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="firstName" className="sr-only">
                    First Name
                  </Label>
                  <Input
                    id="firstName"
                    type="text"
                    placeholder="First Name"
                    className="bg-[#F5F6FA] rounded-none h-12 px-4 py-3 placeholder:text-[#595870] border-none"
                    value={formData.firstName}
                    onChange={(e) =>
                      handleInputChange("firstName", e.target.value)
                    }
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="familyName" className="sr-only">
                    Family Name
                  </Label>
                  <Input
                    id="familyName"
                    type="text"
                    placeholder="Family Name"
                    className="bg-[#F5F6FA] rounded-none h-12 px-4 py-3 placeholder:text-[#595870] border-none"
                    value={formData.familyName}
                    onChange={(e) =>
                      handleInputChange("familyName", e.target.value)
                    }
                  />
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="email" className="sr-only">
                  Email
                </Label>
                <Input
                  id="email"
                  type="email"
                  placeholder="Email"
                  className="bg-[#F5F6FA] rounded-none h-12 px-4 py-3 placeholder:text-[#595870] border-none"
                  value={formData.email}
                  onChange={(e) => handleInputChange("email", e.target.value)}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="password" className="sr-only">
                  Password
                </Label>
                <div className="relative">
                  <Input
                    id="password"
                    type={showPassword ? "text" : "password"}
                    placeholder="Password"
                    className="bg-[#F5F6FA] rounded-none h-12 px-4 py-3 placeholder:text-[#595870] border-none"
                    value={formData.password}
                    onChange={(e) =>
                      handleInputChange("password", e.target.value)
                    }
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                  >
                    {showPassword ? (
                      <EyeOff className="h-4 w-4" />
                    ) : (
                      <Eye className="h-4 w-4" />
                    )}
                  </button>
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="confirmPassword" className="sr-only">
                  Confirm Password
                </Label>
                <div className="relative">
                  <Input
                    id="confirmPassword"
                    type={showConfirmPassword ? "text" : "password"}
                    placeholder="Confirm Password"
                    className="bg-[#F5F6FA] rounded-none h-12 px-4 py-3 placeholder:text-[#595870] border-none"
                    value={formData.confirmPassword}
                    onChange={(e) =>
                      handleInputChange("confirmPassword", e.target.value)
                    }
                  />
                  <button
                    type="button"
                    onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                  >
                    {showConfirmPassword ? (
                      <EyeOff className="h-4 w-4" />
                    ) : (
                      <Eye className="h-4 w-4" />
                    )}
                  </button>
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="country" className="sr-only">
                    Country
                  </Label>
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <button
                        id="country"
                        className={cn(
                          "flex h-12 w-full items-center justify-between placeholder:text-[#595870] rounded-none bg-[#F5F6FA] px-4 text-sm border-none",
                          !formData.country && "text-muted-foreground"
                        )}
                      >
                        <span>{formData.country || "Country"}</span>
                        <ChevronDown className="h-4 w-4 opacity-50" />
                      </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent className="w-(--radix-dropdown-menu-trigger-width) max-h-[300px] overflow-y-auto">
                      {countries.map((country) => (
                        <DropdownMenuItem
                          key={country.code}
                          onClick={() =>
                            handleInputChange("country", country.name)
                          }
                        >
                          {country.name}
                        </DropdownMenuItem>
                      ))}
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="city" className="sr-only">
                    City
                  </Label>
                  <Input
                    id="city"
                    type="text"
                    placeholder="City"
                    className="bg-[#F5F6FA] rounded-none h-12 px-4 py-3 placeholder:text-[#595870] border-none"
                    value={formData.city}
                    onChange={(e) => handleInputChange("city", e.target.value)}
                  />
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="address" className="sr-only">
                  Address
                </Label>
                <Input
                  id="address"
                  type="text"
                  placeholder="Address"
                  className="bg-[#F5F6FA] rounded-none h-12 px-4 py-3 placeholder:text-[#595870] border-none"
                  value={formData.address}
                  onChange={(e) => handleInputChange("address", e.target.value)}
                />
              </div>

              <div className="grid grid-cols-3 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="phoneCode" className="sr-only">
                    Phone Code
                  </Label>
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <button
                        id="phoneCode"
                        className={cn(
                          "flex h-12 w-full items-center justify-between rounded-none bg-[#F5F6FA] px-4 text-sm border-none",
                          "focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                        )}
                      >
                        <span>{formData.phoneCode}</span>
                        <ChevronDown className="h-4 w-4 opacity-50" />
                      </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent className="w-(--radix-dropdown-menu-trigger-width) max-h-[300px] overflow-y-auto">
                      {countries.map((country) => (
                        <DropdownMenuItem
                          key={country.code}
                          onClick={() =>
                            handleInputChange("phoneCode", country.phoneCode)
                          }
                        >
                          {country.phoneCode} ({country.code})
                        </DropdownMenuItem>
                      ))}
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
                <div className="space-y-2 col-span-2">
                  <Label htmlFor="phoneNumber" className="sr-only">
                    Phone Number
                  </Label>
                  <Input
                    id="phoneNumber"
                    type="tel"
                    placeholder="Phone number"
                    className="bg-[#F5F6FA] rounded-none h-12 px-4 py-3 placeholder:text-[#595870] border-none"
                    value={formData.phoneNumber}
                    onChange={(e) =>
                      handleInputChange("phoneNumber", e.target.value)
                    }
                  />
                </div>
              </div>

              <div className="pt-4">
                <Button
                  onClick={handleNext}
                  className="w-full text-xs bg-core text-white hover:bg-core rounded-[10px] py-3 font-bold flex items-center justify-center gap-2"
                  size="lg"
                >
                  <span>Next</span>
                  <Image
                    src={rightArrow}
                    alt="right arrow"
                    className="text-[#FFFFFF]"
                    width={8}
                    height={10.341273307800293}
                  />
                </Button>
              </div>
            </div>
          </div>
        )}

        {currentStep === 2 && (
          <div className="w-full max-w-[500px] overflow-y-auto p-4 sm:p-6">
            <div className="space-y-6">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="bg-[#F5F6FA] h-[220px] rounded-lg p-6 flex flex-col items-center justify-center space-y-3">
                  <div className="rounded-lg flex items-center justify-center">
                    <Image
                      src={idIcon}
                      alt="ID Document"
                      width={99}
                      height={65}
                    />
                  </div>
                  <label
                    htmlFor="id-upload"
                    className="flex items-center p-1 mt-5 w-[135px] justify-start gap-2 cursor-pointer rounded-full bg-[#FFFFFF]"
                  >
                    <div className="w-6 h-6 bg-core rounded-full flex items-center justify-center">
                      <Upload className="w-4 h-4 text-white" />
                    </div>
                    <span className="font-semibold text-sm leading-none flex justify-center items-center flex-1">
                      Upload ID
                    </span>
                  </label>
                  <input
                    id="id-upload"
                    type="file"
                    accept="image/*,.pdf"
                    className="hidden"
                    onChange={(e) => {
                      const file = e.target.files?.[0];
                      if (file) handleFileUpload("idDocument", file);
                    }}
                  />
                  {formData.idDocument && (
                    <span className="text-xs text-gray-600 truncate max-w-full px-2">
                      {formData.idDocument.name}
                    </span>
                  )}
                </div>

                <div className="bg-[#F5F6FA] h-[220px] rounded-lg p-6 flex flex-col items-center justify-center space-y-3">
                  <div className="rounded-lg flex items-center justify-center">
                    <Image src={faceIdIcon} alt="face id" width={85} />
                  </div>
                  <label
                    htmlFor="selfie-upload"
                    className="flex items-center w-[135px] justify-start gap-2 cursor-pointer bg-[#FFFFFF] rounded-full p-1"
                  >
                    <div className="w-6 h-6 bg-core rounded-full flex items-center justify-center">
                      <Upload className="w-4 h-4 text-white" />
                    </div>
                    <span className="font-semibold text-sm leading-none whitespace-nowrap">
                      Upload Selfie
                    </span>
                  </label>
                  <input
                    id="selfie-upload"
                    type="file"
                    accept="image/*"
                    className="hidden"
                    onChange={(e) => {
                      const file = e.target.files?.[0];
                      if (file) handleFileUpload("selfieDocument", file);
                    }}
                  />
                  {formData.selfieDocument && (
                    <span className="text-xs text-gray-600 truncate max-w-full px-2">
                      {formData.selfieDocument.name}
                    </span>
                  )}
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Input
                    id="verificationFirstName"
                    type="text"
                    placeholder="First Name"
                    readOnly
                    value={formData.verificationFirstName}
                    onChange={(e) =>
                      handleInputChange("verificationFirstName", e.target.value)
                    }
                    className="bg-[#F5F6FA] border-none rounded-none placeholder:text-[#807FAC]"
                  />
                </div>
                <div className="space-y-2">
                  <Input
                    id="verificationFamilyName"
                    type="text"
                    placeholder="Family Name"
                    readOnly
                    value={formData.verificationFamilyName}
                    onChange={(e) =>
                      handleInputChange(
                        "verificationFamilyName",
                        e.target.value
                      )
                    }
                    className="bg-[#F5F6FA] border-none rounded-none placeholder:text-[#807FAC]"
                  />
                </div>
              </div>

              <div className="space-y-4">
                <p className="text-sm text-core">
                  If the documents are verified, we will replace your name with
                  what we detected.
                </p>

                <div className="space-y-2">
                  <p className="text-sm text-core">
                    <span className="font-bold italic">Why?</span> To protect
                    against misuse, we need to verify your identity to confirm
                    that the booking belongs to you. The information you provide
                    will not be used for any purpose other than verification and
                    will not be sold to third parties.
                  </p>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="grid grid-cols-2 gap-4 pt-4">
                <Button
                  onClick={handleVerify}
                  className="w-full bg-core hover:bg-core"
                  size="lg"
                >
                  Verify
                </Button>
                <Button
                  onClick={handleSkip}
                  variant="outline"
                  className="w-full border border-core text-core"
                  size="lg"
                >
                  Skip
                  <Image
                    src={rightArrowBlue}
                    alt="right arrow"
                    className="text-[#FFFFFF]"
                    width={8}
                    height={10.341273307800293}
                  />
                </Button>
              </div>
            </div>
          </div>
        )}

        {currentStep === 3 && (
          <div className="w-[434px] p-8">
            <div className="space-y-6">
              <div className="flex items-center justify-center gap-2">
                <Image src={hardCheck} alt="verified" width={39} height={39} />
                <h2 className="text-[20px] font-bold text-core">
                  Sign up complete!
                </h2>
              </div>

              <div className="space-y-4">
                <h3 className="text-[17px] font-bold text-core">
                  Your account&apos;s eligibility
                </h3>

                <div className="space-y-3">
                  <div className="flex items-center gap-3">
                    <div className="w-6 h-6 bg-[#065F46] rounded-full flex items-center justify-center shrink-0">
                      <Image
                        src={checkIcon}
                        alt="verified"
                        className="w-5 h-5 text-white"
                      />
                    </div>
                    <div className="flex-1 bg-[#065F46] text-white px-4 py-2.5 rounded-full text-xs font-bold">
                      Signing up for rewards and benefits
                    </div>
                  </div>

                  <div className="flex items-center gap-3">
                    <div className="w-6 h-6 bg-[#065F46] rounded-full flex items-center justify-center shrink-0">
                      <Image
                        src={checkIcon}
                        alt="verified"
                        className="w-5 h-5 text-white"
                      />
                    </div>
                    <div className="flex-1 bg-[#065F46] text-white px-4 py-2.5 rounded-full text-xs font-bold">
                      Buying from the Marketplace
                    </div>
                  </div>

                  <div className="flex items-center gap-3">
                    <div
                      className={cn(
                        "w-6 h-6 rounded-full flex items-center justify-center shrink-0",
                        isVerified ? "bg-[#065F46]" : "bg-[#5F5506]"
                      )}
                    >
                      {isVerified ? (
                        <Image
                          src={checkIcon}
                          alt="verified"
                          className="w-5 h-5 text-white"
                        />
                      ) : (
                        <X className="w-5 h-5 text-white" />
                      )}
                    </div>
                    <div
                      className={cn(
                        "flex-1 px-4 py-2.5 rounded-full text-xs font-bold",
                        isVerified
                          ? "bg-[#065F46] text-white"
                          : "bg-[#5F5506] text-white"
                      )}
                    >
                      Selling from the Marketplace (Verification required)
                    </div>
                  </div>
                </div>
              </div>

              <div className="space-y-2">
                <p className="text-sm text-core">
                  <span className="font-bold italic">Why?</span> To protect
                  against misuse, we need to verify your identity to confirm
                  that the booking belongs to you. The information you provide
                  will not be used for any purpose other than verification and
                  will not be sold to third parties.
                </p>
              </div>

              <div
                className={cn(
                  "pt-4",
                  isVerified ? "" : "grid grid-cols-2 gap-4"
                )}
              >
                {isVerified ? (
                  <Button
                    onClick={() => {
                      window.location.href = "/login";
                    }}
                    className="w-full bg-core"
                    size="lg"
                  >
                    Log in
                  </Button>
                ) : (
                  <>
                    <Button
                      onClick={() => {
                        window.location.href = "/login";
                      }}
                      className="w-full bg-core"
                      size="lg"
                    >
                      Log in
                    </Button>
                    <Button
                      onClick={() => setCurrentStep(2)}
                      variant="outline"
                      className="w-full font-bold text-xs border border-core text-core"
                      size="lg"
                    >
                      Verify
                    </Button>
                  </>
                )}
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default SignUpPage;
