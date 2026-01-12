"use client";

import React, { useEffect, useRef, useState } from "react";
import { ChevronDown } from "lucide-react";

interface FilterDropdownProps {
  title: string;
  options: string[];
  selectedOptions: string[];
  onSelectionChange: (selected: string[]) => void;
}

export default function FilterDropdown({
  title,
  options,
  selectedOptions,
  onSelectionChange,
}: FilterDropdownProps) {
  const [isOpen, setIsOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!isOpen) return;

    const node = containerRef.current;
    const handlePointerDown = (event: PointerEvent) => {
      if (node && !node.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setIsOpen(false);
      }
    };

    document.addEventListener("pointerdown", handlePointerDown, {
      capture: true,
    });
    document.addEventListener("keydown", handleKeyDown);
    return () => {
      document.removeEventListener("pointerdown", handlePointerDown, true);
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, [isOpen]);

  const handleOptionChange = (option: string) => {
    // Single selection: if already selected, deselect; otherwise select only this option
    if (selectedOptions.includes(option)) {
      onSelectionChange([]);
    } else {
      onSelectionChange([option]);
    }
    setIsOpen(false); // Close dropdown after selection
  };

  const handleClearAll = () => {
    onSelectionChange([]);
  };

  return (
    <div className="w-full">
      <label className="block text-sm font-bold text-gray-700 mb-2">
        {title}
      </label>
      <div ref={containerRef} className="relative w-full">
        <button
          onClick={() => setIsOpen(!isOpen)}
          className="w-full flex items-center justify-between px-4 py-3 bg-white border border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50 transition-colors"
        >
          <div className="flex items-center gap-2">
            {selectedOptions.length > 0 ? (
              <span className="text-sm text-gray-700 font-medium">
                {selectedOptions[0]}
              </span>
            ) : (
              <span className="text-sm text-gray-500">Select option</span>
            )}
          </div>
          <ChevronDown
            className={`w-5 h-5 text-gray-400 transition-transform ${
              isOpen ? "transform rotate-180" : ""
            }`}
          />
        </button>
        {isOpen && (
          <div className="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
            <div className="p-4">
              {selectedOptions.length > 0 && (
                <button
                  onClick={handleClearAll}
                  className="w-full text-sm text-core font-medium mb-3 pb-3 border-b border-gray-200 hover:text-[#2E2C59]"
                >
                  Clear all
                </button>
              )}

              <ul className="flex flex-col gap-1 max-h-80 overflow-y-auto">
                {options.map((option) => {
                  const isSelected = selectedOptions.includes(option);
                  return (
                    <li key={option}>
                      <button
                        onClick={() => handleOptionChange(option)}
                        className={`w-full flex items-center gap-3 p-2.5 rounded-lg transition-colors text-left ${
                          isSelected
                            ? "bg-[#EEF2FF] hover:bg-[#E0E7FF]"
                            : "hover:bg-gray-50"
                        }`}
                      >
                        <div
                          className={`shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all ${
                            isSelected
                              ? "border-core bg-core"
                              : "border-gray-300"
                          }`}
                        >
                          {isSelected && (
                            <div className="w-2 h-2 bg-white rounded-full" />
                          )}
                        </div>
                        <span
                          className={`text-sm leading-relaxed ${
                            isSelected
                              ? "text-core font-medium"
                              : "text-gray-700"
                          }`}
                        >
                          {option}
                        </span>
                      </button>
                    </li>
                  );
                })}
              </ul>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
