import NextAuth, { NextAuthOptions } from "next-auth";
import GoogleProvider from "next-auth/providers/google";
import FacebookProvider from "next-auth/providers/facebook";
import axios from "axios";

declare module "next-auth" {
  interface User {
    accessToken?: string;
    id?: string;
  }
  interface Session {
    user: {
      name?: string | null;
      email?: string | null;
      image?: string | null;
      accessToken?: string;
      id?: string;
    };
  }
}

const BACKEND_URL = process.env.NEXT_PUBLIC_API_URL;

export const authOptions: NextAuthOptions = {
  providers: [
    GoogleProvider({
      clientId: process.env.GOOGLE_CLIENT_ID! as string,
      clientSecret: process.env.GOOGLE_CLIENT_SECRET! as string,

      // This allows us to access the ID Token received from Google
      profile: async (profile, tokens) => {
        // 1. Get the Google ID Token from the tokens object
        const googleIdToken = tokens.id_token;

        console.log("Google ID Token:", googleIdToken);

        if (!googleIdToken) {
          throw new Error("Google ID Token not received.");
        }

        const backendRes = await axios.post(
          `${BACKEND_URL}/auth/google/token`,
          {
            token: googleIdToken,
          }
        );

        const backendUser = backendRes.data;

        return {
          id: backendUser.user.id,
          name: backendUser.user.name,
          email: backendUser.user.email,
          accessToken: backendUser.token,
        };
      },
    }),
    FacebookProvider({
      clientId: process.env.FACEBOOK_CLIENT_ID! as string,
      clientSecret: process.env.FACEBOOK_CLIENT_SECRET as string,
      
      profile: async (profile, tokens) => {
        const facebookAccessToken = tokens.access_token; 

        if (!facebookAccessToken) {
            throw new Error("Facebook Access Token not received.");
        }
        
        const backendRes = await axios.post(`${BACKEND_URL}/auth/facebook/token`, {
            token: facebookAccessToken, // Payload expected by your API
        });
        
        const backendUser = backendRes.data;

        // Return a user object compatible with NextAuth, including your new JWT
        return {
          id: backendUser.user.id,
          name: backendUser.user.name,
          email: backendUser.user.email,
          accessToken: backendUser.token, // Your App's JWT
        };
      },
    }),
  ],

  callbacks: {
    async jwt({ token, user }) {
      if (user) {
        token.accessToken = user.accessToken;
        token.id = user.id;
      }
      return token;
    },

    async session({ session, token }) {
      if (session.user) {
        session.user.accessToken = token.accessToken as string;
      }
      return session;
    },
  },

  pages: {
    signIn: "/login",
  },
  session: {
    strategy: "jwt",
  },
};

const handler = NextAuth(authOptions);
export { handler as GET, handler as POST };
