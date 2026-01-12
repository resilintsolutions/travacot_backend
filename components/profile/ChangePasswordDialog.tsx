"use client";

import React, { useState } from "react";
import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  DialogClose,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { FaPen } from "react-icons/fa";

type ChangePasswordPayload = {
  current_password: string;
  password: string;
  password_confirmation: string;
};

type Props = {
  label?: string;
  onChangePassword?: (payload: ChangePasswordPayload) => Promise<void> | void;
};

export default function ChangePasswordDialog({
  label = "Change password",
  onChangePassword,
}: Props) {
  const [open, setOpen] = useState(false);
  const [currentPassword, setCurrentPassword] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const resetForm = () => {
    setCurrentPassword("");
    setPassword("");
    setPasswordConfirmation("");
    setError(null);
  };

  const handleSubmit = async (e?: React.FormEvent) => {
    e?.preventDefault();
    setError(null);

    if (!currentPassword || !password || !passwordConfirmation) {
      setError("All fields are required.");
      return;
    }

    if (password !== passwordConfirmation) {
      setError("Password confirmation does not match.");
      return;
    }

    setLoading(true);
    try {
      const payload: ChangePasswordPayload = {
        current_password: currentPassword,
        password,
        password_confirmation: passwordConfirmation,
      };

      if (onChangePassword) {
        await onChangePassword(payload);
      }

      resetForm();
      setOpen(false);
    } catch (err: unknown) {
      if (err instanceof Error) {
        setError(err.message || "Failed to change password.");
      } else if (typeof err === "string") {
        setError(err);
      } else {
        setError("Failed to change password.");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <button className="w-[91px] h-[35px] bg-core text-white rounded-[30px] flex items-center justify-center gap-2">
          <FaPen />
          <span className="font-semibold">{label}</span>
        </button>
      </DialogTrigger>

      <DialogContent>
        <DialogHeader>
          <DialogTitle>Change password</DialogTitle>
          <DialogDescription>
            Update your account password. Make sure your new password is strong.
          </DialogDescription>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="grid gap-4 py-4">
          <div className="grid gap-1">
            <label className="text-sm font-medium">Current password</label>
            <input
              type="password"
              value={currentPassword}
              onChange={(e) => setCurrentPassword(e.target.value)}
              className="w-full rounded-md border px-3 py-2"
              required
            />
          </div>

          <div className="grid gap-1">
            <label className="text-sm font-medium">New password</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full rounded-md border px-3 py-2"
              required
            />
          </div>

          <div className="grid gap-1">
            <label className="text-sm font-medium">Confirm new password</label>
            <input
              type="password"
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
              className="w-full rounded-md border px-3 py-2"
              required
            />
          </div>

          {error && <div className="text-sm text-red-500">{error}</div>}

          <DialogFooter>
            <DialogClose asChild>
              <Button variant="secondary" type="button" disabled={loading}>
                Cancel
              </Button>
            </DialogClose>

            <Button type="submit" onClick={handleSubmit} disabled={loading}>
              {loading ? "Saving..." : "Save changes"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
