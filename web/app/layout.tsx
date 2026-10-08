import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL || "https://gmbspys.vercel.app"),
  title: { default: "GMBSPYS – Google Maps Business Lead Finder", template: "%s | GMBSPYS" },
  description: "Find, organize and manage local business prospects with GMBSPYS. Explore Google Maps lead generation workflows, guides, and business data management tools.",
  applicationName: "GMBSPYS",
  keywords: ["Google Maps lead finder", "local business leads", "Google Maps business leads", "business lead management", "local prospecting tool"],
  openGraph: { title: "GMBSPYS – Google Maps Business Lead Finder", description: "Find and organize local business prospects with a streamlined lead workflow.", type: "website", siteName: "GMBSPYS" },
  robots: { index: true, follow: true },
};
export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
 return <html lang="en"><body>{children}</body></html>;
}
