"use client";

import React, { createContext, useContext, useState } from "react";
import { SearchParams } from "../types";

interface SearchContextType {
  searchData: SearchParams;
  setSearchData: (data: SearchParams) => void;
  clearSearchData: () => void;
  reservationId?: number | null;
  setReservationId?: (id: number | null) => void;
}

const SearchContext = createContext<SearchContextType | undefined>(undefined);

const defaultSearchData: SearchParams = {
  destination: "",
  checkIn: new Date().toISOString().split("T")[0],
  checkOut: new Date(Date.now() + 2 * 24 * 60 * 60 * 1000)
    .toISOString()
    .split("T")[0],
  guests: {
    adults: 2,
    children: 0,
  },
};

export const SearchProvider: React.FC<{ children: React.ReactNode }> = ({
  children,
}) => {
  const [searchData, setSearchDataState] =
    useState<SearchParams>(defaultSearchData);
  const [reservationId, setReservationId] = useState<number | null>(null);

  const setSearchData = (data: SearchParams) => {
    setSearchDataState(data);
  };

  const clearSearchData = () => {
    setSearchDataState(defaultSearchData);
    setReservationId(null);
  };

  return (
    <SearchContext.Provider
      value={{
        searchData,
        setSearchData,
        clearSearchData,
        reservationId,
        setReservationId,
      }}
    >
      {children}
    </SearchContext.Provider>
  );
};

export const useSearch = (): SearchContextType => {
  const context = useContext(SearchContext);
  if (!context) {
    throw new Error("useSearch must be used within a SearchProvider");
  }
  return context;
};
