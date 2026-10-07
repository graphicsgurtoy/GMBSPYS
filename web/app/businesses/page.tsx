"use client";

import { useEffect, useState } from "react";

type Business = {
  id: string;
  business_name: string;
  category: string | null;
  phone: string | null;
  email: string | null;
  website: string | null;
  address: string | null;
  city: string | null;
  rating: number | null;
  review_count: number | null;
  google_maps_url: string | null;
  created_at: string;
};

const demoBusinesses: Business[] = [
  {
    id: "demo-1",
    business_name: "Demo Business",
    category: "Business",
    phone: "+91 00000 00000",
    email: "demo@example.com",
    website: "https://example.com",
    address: "Demo Address",
    city: "Ludhiana",
    rating: 4.5,
    review_count: 120,
    google_maps_url: "#",
    created_at: new Date().toISOString(),
  },
];

export default function BusinessesPage() {
  const [businesses, setBusinesses] = useState<Business[]>([]);
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Temporary local data until Supabase connection is enabled.
    setBusinesses([]);
    setLoading(false);
  }, []);

  const filtered = businesses.filter((business) =>
    `${business.business_name} ${business.category ?? ""} ${business.city ?? ""}`
      .toLowerCase()
      .includes(search.toLowerCase())
  );

  return (
    <main className="page">
      <aside className="sidebar">
        <div className="brand">
          <div className="logo">G</div>
          <div>
            <strong>GMBSPYS</strong>
            <span>Lead Finder</span>
          </div>
        </div>

        <nav>
          <a href="/">🏠 Dashboard</a>
          <a className="active" href="/businesses">🏢 Businesses</a>
          <a href="/leads">🎯 Leads</a>
          <a href="/searches">🔎 Searches</a>
          <a href="/settings">⚙️ Settings</a>
        </nav>
      </aside>

      <section className="content">
        <header className="header">
          <div>
            <h1>Businesses</h1>
            <p>All businesses collected from Google Maps.</p>
          </div>

          <button onClick={() => alert("Chrome Extension connection will be enabled next.")}>
            + New Search
          </button>
        </header>

        <div className="toolbar">
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search businesses..."
          />

          <span>{filtered.length} businesses</span>
        </div>

        <div className="tableCard">
          {loading ? (
            <div className="empty">
              <h2>Loading...</h2>
            </div>
          ) : filtered.length === 0 ? (
            <div className="empty">
              <div className="emptyIcon">🏢</div>
              <h2>No businesses yet</h2>
              <p>
                Your Google Maps businesses will appear here after the Chrome
                Extension is connected to GMBSPYS.
              </p>

              <button
                onClick={() =>
                  alert(
                    "Next step: connect the Chrome Extension with Supabase."
                  )
                }
              >
                Connect Chrome Extension
              </button>
            </div>
          ) : (
            <div className="tableWrap">
              <table>
                <thead>
                  <tr>
                    <th>Business</th>
                    <th>Category</th>
                    <th>Phone</th>
                    <th>Website</th>
                    <th>Rating</th>
                    <th>City</th>
                  </tr>
                </thead>

                <tbody>
                  {filtered.map((business) => (
                    <tr key={business.id}>
                      <td>
                        <strong>{business.business_name}</strong>
                      </td>
                      <td>{business.category || "-"}</td>
                      <td>{business.phone || "-"}</td>
                      <td>
                        {business.website ? (
                          <a
                            href={business.website}
                            target="_blank"
                            rel="noreferrer"
                          >
                            Website
                          </a>
                        ) : (
                          "-"
                        )}
                      </td>
                      <td>
                        ⭐ {business.rating ?? "-"} (
                        {business.review_count ?? 0})
                      </td>
                      <td>{business.city || "-"}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </section>

      <style>{`
        * {
          box-sizing: border-box;
        }

        body {
          margin: 0;
          font-family: Arial, sans-serif;
          background: #f6f7fb;
          color: #111827;
        }

        .page {
          min-height: 100vh;
          display: flex;
        }

        .sidebar {
          width: 250px;
          min-height: 100vh;
          background: #111827;
          color: white;
          padding: 24px 16px;
        }

        .brand {
          display: flex;
          align-items: center;
          gap: 12px;
          padding: 0 10px 30px;
        }

        .logo {
          width: 42px;
          height: 42px;
          border-radius: 12px;
          background: white;
          color: #111827;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 22px;
          font-weight: 800;
        }

        .brand strong {
          display: block;
          font-size: 18px;
        }

        .brand span {
          display: block;
          color: #9ca3af;
          font-size: 11px;
          margin-top: 3px;
        }

        nav {
          display: flex;
          flex-direction: column;
          gap: 6px;
        }

        nav a {
          color: #9ca3af;
          text-decoration: none;
          padding: 13px 14px;
          border-radius: 10px;
          font-weight: 600;
        }

        nav a:hover,
        nav a.active {
          color: white;
          background: #1f2937;
        }

        .content {
          flex: 1;
          padding: 38px;
          max-width: 1500px;
        }

        .header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 28px;
        }

        h1 {
          margin: 0;
          font-size: 30px;
        }

        .header p {
          color: #6b7280;
          margin-top: 7px;
        }

        button {
          background: #111827;
          color: white;
          border: 0;
          border-radius: 9px;
          padding: 13px 18px;
          font-weight: 700;
          cursor: pointer;
        }

        .toolbar {
          background: white;
          border: 1px solid #e5e7eb;
          border-radius: 12px;
          padding: 15px;
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 18px;
        }

        input {
          width: 350px;
          border: 1px solid #d1d5db;
          border-radius: 8px;
          padding: 12px;
          outline: none;
        }

        input:focus {
          border-color: #111827;
        }

        .toolbar span {
          color: #6b7280;
          font-size: 13px;
        }

        .tableCard {
          background: white;
          border: 1px solid #e5e7eb;
          border-radius: 14px;
          overflow: hidden;
        }

        .empty {
          text-align: center;
          padding: 100px 30px;
        }

        .emptyIcon {
          width: 60px;
          height: 60px;
          border-radius: 50%;
          background: #f3f4f6;
          display: flex;
          align-items: center;
          justify-content: center;
          margin: 0 auto 18px;
          font-size: 25px;
        }

        .empty h2 {
          margin: 0 0 10px;
        }

        .empty p {
          max-width: 500px;
          margin: 0 auto 22px;
          color: #6b7280;
          line-height: 1.6;
        }

        .tableWrap {
          overflow-x: auto;
        }

        table {
          width: 100%;
          border-collapse: collapse;
        }

        th,
        td {
          padding: 16px;
          text-align: left;
          border-bottom: 1px solid #eef0f3;
          font-size: 13px;
          white-space: nowrap;
        }

        th {
          background: #f9fafb;
          color: #6b7280;
          font-size: 11px;
          text-transform: uppercase;
        }

        td a {
          color: #2563eb;
          text-decoration: none;
        }

        @media (max-width: 800px) {
          .sidebar {
            width: 75px;
          }

          .brand div:last-child,
          nav a {
            font-size: 0;
          }

          .content {
            padding: 20px;
          }

          .header {
            align-items: flex-start;
            gap: 15px;
          }

          input {
            width: 100%;
          }
        }
      `}</style>
    </main>
  );
}
