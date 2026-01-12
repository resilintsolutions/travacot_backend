import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  /* config options here */
  images: {
    remotePatterns: [
      {
        protocol: "https",
        hostname: "itsankit.tech",
      },
      {
        protocol: "https",
        hostname: "photos.hotelbeds.com",
      },
    ],
  },
};

export default nextConfig;
