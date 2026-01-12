"use client";

import { IoMdBed, IoMdCar } from "react-icons/io";
import { BiSolidPlaneAlt } from "react-icons/bi";
import { motion } from "motion/react";

type AnimatedMarqueeProps = {
  /** Animation duration in seconds (default: 20) */
  duration?: number;
};

const items = [
  { icon: <IoMdBed className="size-6" />, label: "Search for stays" },
  { icon: <IoMdCar className="size-6" />, label: "Search for cars" },
  { icon: <BiSolidPlaneAlt className="size-6" />, label: "Search for flights" },
];

export default function AnimatedMarquee({
  duration = 20,
}: AnimatedMarqueeProps) {
  return (
    <div className="flex overflow-hidden relative">
      {/* Fade overlay on edges */}
      <div className="absolute inset-0 z-20 pointer-events-none bg-linear-to-r from-white via-transparent to-white" />

      <motion.div
        className="flex-none flex gap-4 pr-4"
        animate={{ x: "-50%" }}
        transition={{
          duration,
          repeat: Infinity,
          repeatType: "loop",
          ease: "linear",
        }}
      >
        {/* Repeat items multiple times for continuous scrolling */}
        {[...Array(4)].map((_, i) => (
          <div key={i} className="flex gap-4">
            {items.map((item, index) => (
              <div
                key={`${i}-${index}`}
                className="w-40 flex items-center gap-2 bg-surface px-3 py-1.5 rounded-full"
              >
                {item.icon}
                <span className="font-semibold text-xs">{item.label}</span>
              </div>
            ))}
          </div>
        ))}
      </motion.div>
    </div>
  );
}
