import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import { ReactQueryClientProvider } from "@/components/ReactQueryClientProvider";
import LayoutWrapper from "@/components/LayoutWrapper";
import { Toaster } from "@/components/ui/sonner";
import { SessionProvider } from "@/components/SessionProvider";
import MainHeader from "@/components/shared/MainHeader";
import { SearchProvider } from "@/app/search/context/SearchContext";

const inter = Inter({
  subsets: ["latin"],
  preload: false,
});

export const metadata: Metadata = {
  title: "Travacot - Find Your Perfect Stay",
  description:
    "Travacot helps you find and book the best hotels, resorts, and stays worldwide with exclusive deals and easy reservations.",
  keywords: [
    "travel",
    "hotel booking",
    "vacation deals",
    "stay reservations",
    "hotels",
    "travel deals",
    "online booking",
  ],
  authors: [{ name: "Travacot Team" }],
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body className={`${inter.className} antialiased`}>
        <ReactQueryClientProvider>
          <SessionProvider>
            <SearchProvider>
              <LayoutWrapper>
                <MainHeader />
                {children}
              </LayoutWrapper>
              <Toaster />
            </SearchProvider>
          </SessionProvider>
        </ReactQueryClientProvider>
      </body>
    </html>
  );
}
