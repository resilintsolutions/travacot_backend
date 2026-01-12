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
import { Traveler, UpdateTravelerPayload } from "@/app/profile/types";
import { formatDate } from "date-fns";

type Props = {
  traveler: Traveler;
  trigger?: React.ReactNode;
  onSave?: (payload: {
    id: number;
    data: UpdateTravelerPayload;
  }) => Promise<void> | void;
};

export default function EditTravelerDialog({
  traveler,
  trigger,
  onSave,
}: Props) {
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [editedTraveler, setEditedTraveler] = useState<Traveler>({
    ...traveler,
    dob: formatDate(new Date(traveler.dob), "yyyy-MM-dd"),
  });

  const handleSubmit = async (e?: React.FormEvent) => {
    e?.preventDefault();
    setError(null);

    if (!editedTraveler.full_name || !editedTraveler.dob) {
      setError("Name and date of birth are required.");
      return;
    }

    setLoading(true);
    try {
      const payload: UpdateTravelerPayload = {
        full_name: editedTraveler.full_name,
        dob: editedTraveler.dob,
      };

      if (onSave) {
        await onSave({ id: traveler.id, data: payload });
      }

      setOpen(false);
    } catch (err: unknown) {
      if (err instanceof Error) {
        setError(err?.message || "Failed to update traveler.");
      } else {
        setError("Failed to update traveler.");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        {trigger ?? (
          <button className="w-[60px] h-7 bg-[#F5F6FA] rounded-md flex items-center justify-center text-xs">
            <FaPen />
          </button>
        )}
      </DialogTrigger>

      <DialogContent>
        <DialogHeader>
          <DialogTitle>Edit traveler</DialogTitle>
          <DialogDescription>Update traveler details below.</DialogDescription>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="grid gap-4 py-4">
          <div className="grid gap-1">
            <label className="text-sm font-medium">Full name</label>
            <input
              type="text"
              value={editedTraveler.full_name}
              onChange={(e) =>
                setEditedTraveler({
                  ...editedTraveler,
                  full_name: e.target.value,
                })
              }
              className="w-full rounded-md border px-3 py-2"
              required
            />
          </div>

          <div className="grid gap-1">
            <label className="text-sm font-medium">Date of birth</label>
            <input
              type="date"
              value={editedTraveler.dob}
              onChange={(e) =>
                setEditedTraveler({ ...editedTraveler, dob: e.target.value })
              }
              className="w-full rounded-md border px-3 py-2"
              required
            />
          </div>

          <div className="grid gap-1">
            <label className="text-sm font-medium">Wheelchair assistance</label>
            <input
              type="text"
              value={""}
              onChange={() =>
                setEditedTraveler({
                  ...editedTraveler,
                })
              }
              className="w-full rounded-md border px-3 py-2"
            />
          </div>

          <div className="grid gap-1">
            <label className="text-sm font-medium">Gender</label>
            <input
              type="text"
              value={editedTraveler.gender}
              onChange={(e) =>
                setEditedTraveler({
                  ...editedTraveler,
                  gender: e.target.value,
                })
              }
              className="w-full rounded-md border px-3 py-2"
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
