document.addEventListener("DOMContentLoaded", async () => {

    const loginSection = document.getElementById("loginSection");
    const userSection = document.getElementById("userSection");

    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");

    const loginBtn = document.getElementById("loginBtn");
    const signupBtn = document.getElementById("signupBtn");
    const logoutBtn = document.getElementById("logoutBtn");

    const message = document.getElementById("message");
    const userMessage = document.getElementById("userMessage");
    const loading = document.getElementById("loading");

    function showMessage(element, text, type) {
        element.textContent = text;
        element.className = `message ${type}`;
        element.style.display = "block";
    }

    function setLoading(value) {
        loading.style.display = value ? "block" : "none";
        loginBtn.disabled = value;
        signupBtn.disabled = value;
    }

    function showLoggedIn(user) {
        loginSection.style.display = "none";
        userSection.style.display = "block";

        const email = user?.email || "";

        document.getElementById("userEmail").textContent = email;

        document.getElementById("avatar").textContent =
            email ? email.charAt(0).toUpperCase() : "G";
    }

    async function checkLogin() {
        try {
            const session = await GMBSPYS_AUTH.getSession();

            if (!session) {
                return;
            }

            let user = await GMBSPYS_AUTH.getUser();

            if (!user && session.refresh_token) {
                await GMBSPYS_AUTH.refreshSession();
                user = await GMBSPYS_AUTH.getUser();
            }

            if (user) {
                showLoggedIn(user);
            }
        } catch (error) {
            console.error("Auth check failed:", error);
        }
    }

    loginBtn.addEventListener("click", async () => {

        const email = emailInput.value.trim();
        const password = passwordInput.value;

        if (!email || !password) {
            showMessage(
                message,
                "Email and password required.",
                "error"
            );
            return;
        }

        setLoading(true);
        message.style.display = "none";

        try {

            const data = await GMBSPYS_AUTH.signIn(
                email,
                password
            );

            if (data.user) {
                showLoggedIn(data.user);
            } else {
                throw new Error("Login successful but user data missing.");
            }

        } catch (error) {

            console.error(error);

            showMessage(
                message,
                error.message || "Login failed.",
                "error"
            );

        } finally {
            setLoading(false);
        }
    });

    signupBtn.addEventListener("click", async () => {

        const email = emailInput.value.trim();
        const password = passwordInput.value;

        if (!email || !password) {
            showMessage(
                message,
                "Email and password required.",
                "error"
            );
            return;
        }

        if (password.length < 6) {
            showMessage(
                message,
                "Password must be at least 6 characters.",
                "error"
            );
            return;
        }

        setLoading(true);
        message.style.display = "none";

        try {

            const data = await GMBSPYS_AUTH.signUp(
                email,
                password
            );

            if (data.access_token && data.user) {

                showMessage(
                    message,
                    "Account created successfully.",
                    "success"
                );

                showLoggedIn(data.user);

            } else {

                showMessage(
                    message,
                    "Account created. Check your email to confirm your account, then login.",
                    "success"
                );
            }

        } catch (error) {

            console.error(error);

            showMessage(
                message,
                error.message || "Signup failed.",
                "error"
            );

        } finally {
            setLoading(false);
        }
    });

    logoutBtn.addEventListener("click", async () => {

        try {

            await GMBSPYS_AUTH.logout();

            userSection.style.display = "none";
            loginSection.style.display = "block";

            emailInput.value = "";
            passwordInput.value = "";

            showMessage(
                message,
                "Logged out successfully.",
                "success"
            );

        } catch (error) {

            showMessage(
                userMessage,
                "Logout failed.",
                "error"
            );

        }
    });

    await checkLogin();
});
