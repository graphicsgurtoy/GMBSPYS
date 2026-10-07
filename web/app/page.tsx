const stats = [
  { title: "Businesses", value: "0", icon: "🏢" },
  { title: "Leads", value: "0", icon: "🎯" },
  { title: "Searches", value: "0", icon: "🔎" },
  { title: "Hot Leads", value: "0", icon: "🔥" },
];

export default function Home() {
  return (
    <main className="app">
      <aside className="sidebar">
        <div className="logo">
          <div className="logoBox">G</div>
          <div>
            <strong>GMBSPYS</strong>
            <span>Lead Finder</span>
          </div>
        </div>

        <nav>
          <a className="active" href="/">
            🏠 <span>Dashboard</span>
          </a>
          <a href="/businesses">🏢 <span>Businesses</span>
          </a>
          <a href="/leads">            🎯 <span>Leads</span>
          </a>
          <a href="/searches">            🔎 <span>Searches</span>
          </a>
          <a href="/settings">            ⚙️ <span>Settings</span>
          </a>
        </nav>

        <div className="sidebarBottom">
          <div className="user">
            <div className="avatar">N</div>
            <div>
              <strong>GMBSPYS User</strong>
              <span>Free Plan</span>
            </div>
          </div>
        </div>
      </aside>

      <section className="content">
        <header className="topbar">
          <div>
            <h1>Dashboard</h1>
            <p>Find businesses. Find opportunities.</p>
          </div>

          <button className="searchButton">+ New Search</button>
        </header>

        <div className="stats">
          {stats.map((stat) => (
            <div className="card" key={stat.title}>
              <div className="cardIcon">{stat.icon}</div>
              <div>
                <span>{stat.title}</span>
                <strong>{stat.value}</strong>
              </div>
            </div>
          ))}
        </div>

        <div className="grid">
          <div className="panel">
            <div className="panelHeader">
              <div>
                <h2>Recent Searches</h2>
                <p>Your latest Google Maps searches</p>
              </div>
              <button>View All</button>
            </div>

            <div className="empty">
              <div className="emptyIcon">🔎</div>
              <h3>No searches yet</h3>
              <p>
                Start a Google Maps search using the GMBSPYS Chrome Extension.
              </p>
              <button className="primary">Start Your First Search</button>
            </div>
          </div>

          <div className="panel">
            <div className="panelHeader">
              <div>
                <h2>Lead Overview</h2>
                <p>Your lead pipeline</p>
              </div>
            </div>

            <div className="leadRows">
              <div>
                <span>🔥 Hot Leads</span>
                <strong>0</strong>
              </div>
              <div>
                <span>🟠 Warm Leads</span>
                <strong>0</strong>
              </div>
              <div>
                <span>🔵 Cold Leads</span>
                <strong>0</strong>
              </div>
            </div>
          </div>
        </div>

        <div className="info">
          <div>
            <strong>🚀 GMBSPYS is ready</strong>
            <p>
              Connect your Chrome Extension to start collecting Google Maps
              business leads.
            </p>
          </div>
          <span>Chrome Extension → Supabase → Dashboard</span>
        </div>
      </section>

      <style>{`
        * {
          box-sizing: border-box;
        }

        body {
          margin: 0;
          background: #f6f7fb;
          color: #111827;
          font-family: Arial, Helvetica, sans-serif;
        }

        .app {
          min-height: 100vh;
          display: flex;
          background: #f6f7fb;
        }

        .sidebar {
          width: 250px;
          min-height: 100vh;
          background: #111827;
          color: white;
          padding: 24px 16px;
          display: flex;
          flex-direction: column;
        }

        .logo {
          display: flex;
          align-items: center;
          gap: 12px;
          padding: 4px 10px 32px;
        }

        .logoBox {
          width: 42px;
          height: 42px;
          border-radius: 12px;
          background: #ffffff;
          color: #111827;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 22px;
          font-weight: 800;
        }

        .logo strong {
          display: block;
          font-size: 18px;
          letter-spacing: .5px;
        }

        .logo span {
          display: block;
          color: #9ca3af;
          font-size: 11px;
          margin-top: 3px;
        }

        nav {
          display: flex;
          flex-direction: column;
          gap: 7px;
        }

        nav a {
          color: #9ca3af;
          text-decoration: none;
          padding: 13px 14px;
          border-radius: 10px;
          display: flex;
          gap: 12px;
          align-items: center;
          font-size: 14px;
          font-weight: 600;
        }

        nav a:hover,
        nav a.active {
          background: #1f2937;
          color: white;
        }

        .sidebarBottom {
          margin-top: auto;
          border-top: 1px solid #1f2937;
          padding: 20px 10px 4px;
        }

        .user {
          display: flex;
          align-items: center;
          gap: 10px;
        }

        .avatar {
          width: 36px;
          height: 36px;
          border-radius: 50%;
          background: #374151;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: 700;
        }

        .user strong {
          display: block;
          font-size: 12px;
        }

        .user span {
          display: block;
          color: #9ca3af;
          font-size: 11px;
          margin-top: 3px;
        }

        .content {
          flex: 1;
          padding: 36px 42px;
          max-width: 1500px;
        }

        .topbar {
          display: flex;
          align-items: center;
          justify-content: space-between;
          margin-bottom: 30px;
        }

        h1 {
          margin: 0;
          font-size: 30px;
          letter-spacing: -1px;
        }

        .topbar p {
          margin: 7px 0 0;
          color: #6b7280;
          font-size: 14px;
        }

        button {
          border: 0;
          cursor: pointer;
          font-family: inherit;
        }

        .searchButton,
        .primary {
          background: #111827;
          color: white;
          padding: 12px 18px;
          border-radius: 9px;
          font-weight: 700;
        }

        .stats {
          display: grid;
          grid-template-columns: repeat(4, 1fr);
          gap: 18px;
          margin-bottom: 22px;
        }

        .card {
          background: white;
          border: 1px solid #e5e7eb;
          border-radius: 14px;
          padding: 20px;
          display: flex;
          align-items: center;
          gap: 15px;
        }

        .cardIcon {
          width: 46px;
          height: 46px;
          border-radius: 11px;
          background: #f3f4f6;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 21px;
        }

        .card span {
          display: block;
          color: #6b7280;
          font-size: 12px;
          margin-bottom: 5px;
        }

        .card strong {
          font-size: 24px;
        }

        .grid {
          display: grid;
          grid-template-columns: 1.7fr 1fr;
          gap: 22px;
        }

        .panel {
          background: white;
          border: 1px solid #e5e7eb;
          border-radius: 14px;
          min-height: 330px;
        }

        .panelHeader {
          padding: 20px 22px;
          border-bottom: 1px solid #eef0f3;
          display: flex;
          align-items: center;
          justify-content: space-between;
        }

        .panelHeader h2 {
          margin: 0;
          font-size: 16px;
        }

        .panelHeader p {
          margin: 5px 0 0;
          color: #9ca3af;
          font-size: 12px;
        }

        .panelHeader button {
          background: transparent;
          color: #4b5563;
          font-size: 12px;
          font-weight: 700;
        }

        .empty {
          text-align: center;
          padding: 50px 25px;
        }

        .emptyIcon {
          width: 55px;
          height: 55px;
          background: #f3f4f6;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          margin: 0 auto 15px;
          font-size: 22px;
        }

        .empty h3 {
          margin: 0 0 8px;
          font-size: 16px;
        }

        .empty p {
          color: #6b7280;
          font-size: 13px;
          max-width: 380px;
          margin: 0 auto 20px;
          line-height: 1.6;
        }

        .leadRows {
          padding: 10px 22px;
        }

        .leadRows div {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 20px 0;
          border-bottom: 1px solid #f0f1f3;
          font-size: 13px;
        }

        .leadRows div:last-child {
          border-bottom: 0;
        }

        .leadRows strong {
          font-size: 18px;
        }

        .info {
          margin-top: 22px;
          padding: 20px 22px;
          background: #111827;
          color: white;
          border-radius: 14px;
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 20px;
        }

        .info strong {
          font-size: 14px;
        }

        .info p {
          margin: 5px 0 0;
          color: #9ca3af;
          font-size: 12px;
        }

        .info > span {
          color: #d1d5db;
          font-size: 12px;
        }

        @media (max-width: 900px) {
          .sidebar {
            width: 80px;
            padding: 20px 10px;
          }

          .logo div:last-child,
          nav span,
          .user div:last-child {
            display: none;
          }

          .logo {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
          }

          nav a {
            justify-content: center;
          }

          .content {
            padding: 25px;
          }

          .stats {
            grid-template-columns: repeat(2, 1fr);
          }

          .grid {
            grid-template-columns: 1fr;
          }
        }

        @media (max-width: 600px) {
          .content {
            padding: 20px 15px;
          }

          .topbar {
            align-items: flex-start;
            gap: 15px;
          }

          .topbar h1 {
            font-size: 24px;
          }

          .searchButton {
            padding: 10px 12px;
            font-size: 12px;
          }

          .stats {
            grid-template-columns: 1fr 1fr;
            gap: 10px;
          }

          .card {
            padding: 14px;
          }

          .info {
            flex-direction: column;
            align-items: flex-start;
          }
        }
      `}</style>
    </main>
  );
}




