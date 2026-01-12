"use client";
interface Props {
  title?: string;
}
export default function ProfileLoader({ title = "profile" }: Props) {
  return (
    <div className="flex items-center justify-center min-h-screen">
      <div
        role="status"
        aria-live="polite"
        className="flex flex-col items-center gap-3"
      >
        <span className="inline-block w-12 h-12 rounded-full border-4 border-core border-t-transparent animate-spin" />
        <span className="text-sm text-core">Loading {title}...</span>
      </div>
    </div>
  );
}
