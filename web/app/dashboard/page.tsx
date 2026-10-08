const modules = [
  { title: "Google Maps Scraper", desc: "Search jobs, scrape history and collected business leads.", href: "/searches" },
  { title: "Lead Management", desc: "Review, qualify and manage your lead pipeline.", href: "/leads" },
  { title: "Business CRM", desc: "Business records, customers and workspace management.", href: "/businesses" },
  { title: "Analytics", desc: "Open the current dashboard and review available metrics.", href: "/admin" },
  { title: "Workspace Settings", desc: "Review current application settings.", href: "/settings" },
  { title: "GrowReview CRM", desc: "GrowReview CRM integration: connect authenticated APIs and migrate existing CRM workflows before production use.", href: "/guides/getting-started" },
];

export default function DashboardPage() {
  return (
    <main style={{maxWidth: 1180, margin: "0 auto", padding: "40px 24px"}}>
      <div style={{display:"flex", justifyContent:"space-between", alignItems:"center", gap:16, flexWrap:"wrap"}}>
        <div>
          <p style={{fontSize:12, letterSpacing:2, textTransform:"uppercase", opacity:.7}}>GMBSPYS · WORKSPACE</p>
          <h1 style={{fontSize:36, fontWeight:750, margin:"8px 0"}}>Unified Business Dashboard</h1>
          <p style={{opacity:.75, maxWidth:720}}>Scraper, leads and CRM modules in one workspace. Connect authentication, tenant policies and live APIs before production use.</p>
        </div>
        <a href="/" style={{padding:"10px 16px", border:"1px solid #8886", borderRadius:10, textDecoration:"none"}}>View website ↗</a>
      </div>
      <div style={{display:"grid", gridTemplateColumns:"repeat(auto-fit,minmax(245px,1fr))", gap:16, marginTop:32}}>
        {modules.map((item) => (
          <a key={item.title} href={item.href} style={{display:"block", padding:22, border:"1px solid #8883", borderRadius:16, textDecoration:"none", color:"inherit", minHeight:130}}>
            <h2 style={{fontSize:19, fontWeight:700, margin:"0 0 10px"}}>{item.title} ↗</h2>
            <p style={{fontSize:14, lineHeight:1.6, opacity:.75, margin:0}}>{item.desc}</p>
          </a>
        ))}
      </div>
      <p style={{marginTop:28, fontSize:13, opacity:.7}}>Integration foundation only: existing scraper behavior and PHP CRM endpoints have not yet been migrated by this page.</p>
    </main>
  );
}

