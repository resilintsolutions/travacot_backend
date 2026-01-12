"use client";

import { ReactNode, useEffect } from "react";
import { motion, AnimatePresence } from "motion/react";
import { cn } from "@/lib/utils";

interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string | ReactNode;
  children: ReactNode;
  className?: string;
  styleHeader?: string;
}

export default function Modal({
  isOpen,
  onClose,
  title,
  children,
  className,
  styleHeader,
}: ModalProps) {
  const handleOverlayClick = () => onClose();

  useEffect(() => {
    const scrollBarWidth =
      window.innerWidth - document.documentElement.clientWidth;

    if (isOpen) {
      document.body.classList.add("overflow-hidden");
      document.body.style.paddingRight = `${scrollBarWidth}px`;
    } else {
      document.body.classList.remove("overflow-hidden");
      document.body.style.paddingRight = "";
    }

    return () => {
      document.body.classList.remove("overflow-hidden");
      document.body.style.paddingRight = "";
    };
  }, [isOpen]);

  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          className="fixed inset-0 bg-core/50 flex justify-center items-center z-50 p-4"
          onClick={handleOverlayClick}
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.5 }}
        >
          <motion.div
            className={cn(
              "flex flex-col bg-white rounded-lg shadow-lg w-full max-w-3xl mx-auto relative max-h-[90vh]",
              className
            )}
            onClick={(e) => e.stopPropagation()}
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 0.5 }}
          >
            {/* Fixed Header */}
            <div
              className={cn(
                "flex justify-between items-center p-4 border-b border-gray-200 shrink-0",
                styleHeader
              )}
            >
              {title && typeof title === "string" ? (
                <h2 className="text-lg font-bold">{title}</h2>
              ) : (
                title
              )}
              <button
                onClick={onClose}
                className="text-gray-600 hover:text-gray-900 text-lg font-bold"
              >
                ✕
              </button>
            </div>

            {/* Scrollable Content */}
            <div
              className="overflow-y-auto p-4"
              style={{ maxHeight: "calc(90vh - 64px)" }}
            >
              {children}
            </div>
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
