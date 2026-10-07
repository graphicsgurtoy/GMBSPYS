export default function LeadsPage() {
  return (
    <main style={{
      minHeight: "100vh",
      padding: "48px",
      background: "#f6f7fb",
      fontFamily: "Arial, sans-serif"
    }}>
      <a href="/" style={{color:"#111827", textDecoration:"none"}}>
        ← Back to Dashboard
      </a>

      <h1 style={{fontSize:"36px", marginTop:"30px", marginBottom:"8px"}}>
        Leads
      </h1>

      <p style={{color:"#667085", fontSize:"17px"}}>
        Manage and qualify your Google Maps business leads.
      </p>

      <div style={{
        marginTop:"35px",
        background:"#fff",
        border:"1px solid #e5e7eb",
        borderRadius:"16px",
        padding:"60px",
        textAlign:"center"
      }}>
        <div style={{fontSize:"48px"}}>🎯</div>
        <h2>No leads yet</h2>
        <p style={{color:"#667085"}}>
          Leads generated from your GMBSPYS searches will appear here.
        </p>
      </div>
    </main>
  );
}
