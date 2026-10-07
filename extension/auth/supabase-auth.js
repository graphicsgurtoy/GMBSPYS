const GMBSPYS_AUTH = {
  async signIn(email, password) {
    const response = await fetch(
      `${GMBSPYS_SUPABASE_URL}/auth/v1/token?grant_type=password`,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "apikey": GMBSPYS_SUPABASE_KEY
        },
        body: JSON.stringify({ email: email.trim(), password })
      }
    );
    const data = await response.json();
    if (!response.ok) {
      throw new Error(
        data.msg || data.message || data.error_description || data.error ||
        "Login failed"
      );
    }
    await this.saveSession(data);
    return data;
  },

  async saveSession(data) {
    const session = {
      access_token: data.access_token,
      refresh_token: data.refresh_token,
      expires_in: data.expires_in,
      expires_at: data.expires_at || (Date.now() + ((data.expires_in || 3600) * 1000)),
      user: data.user || null
    };
    await chrome.storage.local.set({ gmbspys_session: session });
    return session;
  },

  async getSession() {
    const result = await chrome.storage.local.get("gmbspys_session");
    return result.gmbspys_session || null;
  },

  async getUser() {
    const session = await this.getSession();
    if (!session?.access_token) return null;
    const response = await fetch(`${GMBSPYS_SUPABASE_URL}/auth/v1/user`, {
      headers: {
        "apikey": GMBSPYS_SUPABASE_KEY,
        "Authorization": `Bearer ${session.access_token}`
      }
    });
    if (!response.ok) return null;
    return await response.json();
  },

  async logout() {
    const session = await this.getSession();
    if (session?.access_token) {
      try {
        await fetch(`${GMBSPYS_SUPABASE_URL}/auth/v1/logout`, {
          method: "POST",
          headers: {
            "apikey": GMBSPYS_SUPABASE_KEY,
            "Authorization": `Bearer ${session.access_token}`
          }
        });
      } catch (_) {}
    }
    await chrome.storage.local.remove("gmbspys_session");
  }
};
