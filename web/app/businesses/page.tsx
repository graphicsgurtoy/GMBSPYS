"use client";

import { useEffect, useState } from "react";
import { supabase } from "../../lib/supabase";

type Business = {
  id: string;
  business_name: string;
  category: string | null;
  phone: string | null;
  email: string | null;
  website: string | null;
  address: string | null;
  city: string | null;
  state: string | null;
  rating: number | null;
  review_count: number | null;
  instagram: string | null;
  facebook: string | null;
  linkedin: string | null;
  youtube: string | null;
  google_maps_url: string | null;
  created_at: string;
};

export default function BusinessesPage() {
  const [businesses, setBusinesses] = useState<Business[]>([]);
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  async function loadBusinesses() {
    setLoading(true);
    setError("");

    const { data, error } = await supabase
      .from("businesses")
      .select("*")
      .order("created_at", { ascending: false });

    if (error) {
      console.error(error);
      setError(error.message);
      setBusinesses([]);
    } else {
      setBusinesses(data || []);
    }

    setLoading(false);
  }

  useEffect(() => {
    loadBusinesses();
  }, []);

  const filteredBusinesses = businesses.filter((business) => {
    const text = `
      ${business.business_name || ""}
      ${business.category || ""}
      ${business.phone || ""}
      ${business.email || ""}
      ${business.address || ""}
      ${business.city || ""}
    `.toLowerCase();

    return text.includes(search.toLowerCase());
  });

  return (
    <main
      style={{
        minHeight: "100vh",
        background: "#f5f7fb",
        padding: "32px",
        fontFamily: "Arial, sans-serif",
      }}
    >
      <div style={{ maxWidth: 1400, margin: "0 auto" }}>
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            marginBottom: 25,
          }}
        >
          <div>
            <h1 style={{ margin: 0, fontSize: 30 }}>Businesses</h1>
            <p style={{ color: "#6b7280" }}>
              Real businesses collected from Google Maps
            </p>
          </div>

          <a
            href="/"
            style={{
              textDecoration: "none",
              background: "#111827",
              color: "#fff",
              padding: "11px 18px",
              borderRadius: 8,
            }}
          >
            Dashboard
          </a>
        </div>

        <div
          style={{
            background: "#fff",
            padding: 20,
            borderRadius: 14,
            boxShadow: "0 2px 10px rgba(0,0,0,.05)",
            marginBottom: 20,
          }}
        >
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search business, category, phone, city..."
            style={{
              width: "100%",
              padding: "13px 15px",
              border: "1px solid #d1d5db",
              borderRadius: 8,
              fontSize: 15,
              outline: "none",
            }}
          />
        </div>

        {loading && (
          <div
            style={{
              background: "#fff",
              padding: 40,
              borderRadius: 14,
              textAlign: "center",
            }}
          >
            Loading businesses...
          </div>
        )}

        {error && (
          <div
            style={{
              background: "#fff",
              border: "1px solid #fecaca",
              color: "#b91c1c",
              padding: 20,
              borderRadius: 14,
              marginBottom: 20,
            }}
          >
            <strong>Supabase Error</strong>
            <div style={{ marginTop: 8 }}>{error}</div>
          </div>
        )}

        {!loading && !error && (
          <div
            style={{
              background: "#fff",
              borderRadius: 14,
              overflow: "hidden",
              boxShadow: "0 2px 10px rgba(0,0,0,.05)",
            }}
          >
            <div
              style={{
                padding: 18,
                borderBottom: "1px solid #e5e7eb",
                fontWeight: 600,
              }}
            >
              {filteredBusinesses.length} businesses found
            </div>

            {filteredBusinesses.length === 0 ? (
              <div
                style={{
                  padding: 60,
                  textAlign: "center",
                  color: "#6b7280",
                }}
              >
                <h3 style={{ color: "#111827" }}>
                  No businesses yet
                </h3>
                <p>
                  Chrome Extension se Google Maps businesses scrape karne
                  ke baad yahan data automatically dikhega.
                </p>
              </div>
            ) : (
              <div style={{ overflowX: "auto" }}>
                <table
                  style={{
                    width: "100%",
                    borderCollapse: "collapse",
                    minWidth: 1100,
                  }}
                >
                  <thead>
                    <tr style={{ background: "#f9fafb" }}>
                      <th style={th}>Business</th>
                      <th style={th}>Category</th>
                      <th style={th}>Phone</th>
                      <th style={th}>Website</th>
                      <th style={th}>Rating</th>
                      <th style={th}>Reviews</th>
                      <th style={th}>City</th>
                      <th style={th}>Maps</th>
                    </tr>
                  </thead>

                  <tbody>
                    {filteredBusinesses.map((business) => (
                      <tr key={business.id}>
                        <td style={td}>
                          <strong>{business.business_name}</strong>
                          {business.email && (
                            <div style={{ color: "#6b7280", fontSize: 12 }}>
                              {business.email}
                            </div>
                          )}
                        </td>

                        <td style={td}>
                          {business.category || "-"}
                        </td>

                        <td style={td}>
                          {business.phone || "-"}
                        </td>

                        <td style={td}>
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

                        <td style={td}>
                          {business.rating ?? "-"}
                        </td>

                        <td style={td}>
                          {business.review_count ?? "-"}
                        </td>

                        <td style={td}>
                          {business.city || "-"}
                        </td>

                        <td style={td}>
                          {business.google_maps_url ? (
                            <a
                              href={business.google_maps_url}
                              target="_blank"
                              rel="noreferrer"
                            >
                              Open Maps
                            </a>
                          ) : (
                            "-"
                          )}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        )}
      </div>
    </main>
  );
}

const th = {
  padding: "14px 16px",
  textAlign: "left" as const,
  fontSize: 13,
  color: "#6b7280",
  borderBottom: "1px solid #e5e7eb",
};

const td = {
  padding: "15px 16px",
  borderBottom: "1px solid #f0f0f0",
  fontSize: 14,
};
