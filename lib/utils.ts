import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function formatDateRange(start: Date, end: Date): string {
  const sameMonth = start.getMonth() === end.getMonth();
  const sameYear = start.getFullYear() === end.getFullYear();

  const startDay = start.getDate();
  const endDay = end.getDate();

  const startMonth = start.toLocaleString("en", { month: "short" });
  const endMonth = end.toLocaleString("en", { month: "short" });

  if (sameMonth && sameYear) {
    return `${startDay} - ${endDay} ${startMonth}`;
  } else if (sameYear) {
    return `${startDay} ${startMonth} - ${endDay} ${endMonth}`;
  } else {
    return `${startDay} ${startMonth} ${start.getFullYear()} - ${endDay} ${endMonth} ${end.getFullYear()}`;
  }
}
