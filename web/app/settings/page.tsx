export default function SettingsPage() {
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
        Settings
      </h1>

      <p style={{color:"#667085", fontSize:"17px"}}>
        Manage your GMBSPYS account and preferences.
      </p>

      <div style={{
        marginTop:"35px",
        background:"#fff",
        border:"1px solid #e5e7eb",
        borderRadius:"16px",
        padding:"30px"
      }}>
        <h2>Account</h2>
        <p style={{color:"#667085"}}>
          Account settings will be available here.
        </p>
      </div>
    </main>
  );
}
