const GMBSPYS_AUTH = {

    async signUp(email, password) {

        const response = await fetch(
            `${GMBSPYS_SUPABASE_URL}/auth/v1/signup`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "apikey": GMBSPYS_SUPABASE_KEY
                },
                body: JSON.stringify({
                    email: email.trim(),
                    password: password
                })
            }
        );

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.msg ||
                data.message ||
                data.error_description ||
                data.error ||
                "Signup failed"
            );
        }

        if (data.access_token) {
            await this.saveSession(data);
        }

        return data;
    },


    async signIn(email, password) {

        const cleanEmail = email.trim();

        console.log("GMBSPYS: Starting login");
        console.log("GMBSPYS: Supabase URL:", GMBSPYS_SUPABASE_URL);
        console.log(
            "GMBSPYS: API key:",
            GMBSPYS_SUPABASE_KEY ? "AVAILABLE" : "MISSING"
        );

        const response = await fetch(
            `${GMBSPYS_SUPABASE_URL}/auth/v1/token?grant_type=password`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "apikey": GMBSPYS_SUPABASE_KEY
                },
                body: JSON.stringify({
                    email: cleanEmail,
                    password: password
                })
            }
        );

        const data = await response.json();

        console.log("GMBSPYS: Login HTTP status:", response.status);

        if (!response.ok) {
            console.error("GMBSPYS: Login failed:", data);

            throw new Error(
                data.msg ||
                data.message ||
                data.error_description ||
                data.error ||
                "Login failed"
            );
        }

        console.log("GMBSPYS: Login successful");

        await this.saveSession(data);

        return data;
    },


    async saveSession(data) {

        const session = {
            access_token: data.access_token,
            refresh_token: data.refresh_token,
            expires_in: data.expires_in,
            expires_at: data.expires_at ||
                Date.now() + ((data.expires_in || 3600) * 1000),
            user: data.user || null
        };

        await chrome.storage.local.set({
            gmbspys_session: session
        });

        console.log("GMBSPYS: Session saved");

        return session;
    },


    async getSession() {

        const result = await chrome.storage.local.get(
            "gmbspys_session"
        );

        return result.gmbspys_session || null;
    },


    async getUser() {

        const session = await this.getSession();

        if (!session?.access_token) {
            return null;
        }

        const response = await fetch(
            `${GMBSPYS_SUPABASE_URL}/auth/v1/user`,
            {
                method: "GET",
                headers: {
                    "apikey": GMBSPYS_SUPABASE_KEY,
                    "Authorization": `Bearer ${session.access_token}`
                }
            }
        );

        if (!response.ok) {
            return null;
        }

        return await response.json();
    },


    async refreshSession() {

        const session = await this.getSession();

        if (!session?.refresh_token) {
            return null;
        }

        const response = await fetch(
            `${GMBSPYS_SUPABASE_URL}/auth/v1/token?grant_type=refresh_token`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "apikey": GMBSPYS_SUPABASE_KEY
                },
                body: JSON.stringify({
                    refresh_token: session.refresh_token
                })
            }
        );

        if (!response.ok) {
            await this.logout();
            return null;
        }

        const data = await response.json();

        await this.saveSession(data);

        return data;
    },


    async logout() {

        const session = await this.getSession();

        if (session?.access_token) {

            try {

                await fetch(
                    `${GMBSPYS_SUPABASE_URL}/auth/v1/logout`,
                    {
                        method: "POST",
                        headers: {
                            "apikey": GMBSPYS_SUPABASE_KEY,
                            "Authorization":
                                `Bearer ${session.access_token}`
                        }
                    }
                );

            } catch (error) {
                console.log(
                    "GMBSPYS: Logout request failed:",
                    error
                );
            }
        }

        await chrome.storage.local.remove(
            "gmbspys_session"
        );
    }
};
