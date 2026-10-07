document.addEventListener("DOMContentLoaded", async () => {
  const login = document.getElementById("gmbLogin");
  const loggedIn = document.getElementById("gmbLoggedIn");
  const email = document.getElementById("email");
  const password = document.getElementById("password");
  const loginBtn = document.getElementById("loginBtn");
  const logoutBtn = document.getElementById("logoutBtn");
  const loading = document.getElementById("loading");
  const loginMessage = document.getElementById("loginMessage");
  const loggedEmail = document.getElementById("loggedEmail");
  const userBadge = document.getElementById("userBadge");

  function setMessage(el, msg, ok=false) {
    el.textContent = msg || "";
    el.style.color = ok ? "#067647" : "#b42318";
  }

  function showUser(user) {
    login.style.display = "none";
    loggedIn.style.display = "block";
    const e = user?.email || "";
    loggedEmail.textContent = e;
    userBadge.textContent = e;
  }

  async function check() {
    const user = await GMBSPYS_AUTH.getUser();
    if (user) showUser(user);
  }

  loginBtn.addEventListener("click", async () => {
    if (!email.value.trim() || !password.value) {
      setMessage(loginMessage, "Email and password required.");
      return;
    }
    loading.style.display = "block";
    loginBtn.disabled = true;
    setMessage(loginMessage, "");
    try {
      const data = await GMBSPYS_AUTH.signIn(email.value, password.value);
      showUser(data.user);
    } catch (e) {
      setMessage(loginMessage, e.message || "Login failed.");
    } finally {
      loading.style.display = "none";
      loginBtn.disabled = false;
    }
  });

  logoutBtn.addEventListener("click", async () => {
    await GMBSPYS_AUTH.logout();
    loggedIn.style.display = "none";
    login.style.display = "block";
    userBadge.textContent = "";
  });

  document.getElementById("openDashboardBtn")?.addEventListener("click", () => {
    chrome.tabs.create({url:"https://gmbspys.vercel.app/"});
  });
  document.getElementById("openDashboardBtn1")?.addEventListener("click", () => {
    chrome.tabs.create({url:"https://gmbspys.vercel.app/"});
  });
  document.getElementById("openExportBtn")?.addEventListener("click", () => {
    chrome.tabs.create({url:chrome.runtime.getURL("dashboard.html")});
  });

  await check();
});
