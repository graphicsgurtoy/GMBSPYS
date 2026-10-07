importScripts("auth/supabase-config.js");
try{importScripts("auth/config.js","auth/feedback/feedback.js","auth/loginbg.js"),importScripts("js/mybg.js"),importScripts("sorry.js")}catch(a){console.error(a)};

// GMBSPYS cloud sync: Google Maps results -> Supabase
chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
  if (message?.action !== "syncBusinesses") return;
  (async () => {
    try {
      const sessionResult = await chrome.storage.local.get("gmbspys_session");
      const session = sessionResult.gmbspys_session;
      if (!session?.access_token) {
        sendResponse({ok:false, error:"Not logged in"});
        return;
      }
      const rows = (Array.isArray(message.data) ? message.data : []).map((b) => ({
        user_id: session.user?.id || null,
        business_name: b.name || "",
        category: b.category || "",
        phone: b.phone || "",
        email: b.email || "",
        website: b.website || "",
        address: b.address || "",
        google_maps_url: b.profileURL || (b.cid ? `https://www.google.com/maps?cid=${b.cid}` : ""),
        rating: b.averageRating ?? null,
        review_count: b.ratingCount ?? null,
        latitude: b.latitude ?? null,
        longitude: b.longitude ?? null,
        instagram: b.instagram || "",
        facebook: b.facebook || "",
        linkedin: b.linkedin || "",
        youtube: b.youtube || "",
        source: "google_maps"
      })).filter(r => r.business_name);
      if (!rows.length) {
        sendResponse({ok:true, inserted:0});
        return;
      }
      const response = await fetch(`${GMBSPYS_SUPABASE_URL}/rest/v1/businesses`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "apikey": GMBSPYS_SUPABASE_KEY,
          "Authorization": `Bearer ${session.access_token}`,
          "Prefer": "return=minimal"
        },
        body: JSON.stringify(rows)
      });
      const body = await response.text();
      if (!response.ok) throw new Error(`Supabase ${response.status}: ${body}`);
      sendResponse({ok:true, inserted:rows.length});
    } catch (error) {
      console.error("GMBSPYS sync error:", error);
      sendResponse({ok:false, error:error.message});
    }
  })();
  return true;
});
