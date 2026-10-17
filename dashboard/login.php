<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Secure Device Management - Login</title>

    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

    <div class="login-container">

        <div class="login-box">

            <h1>Secure Device Management</h1>

            <p class="login-subtitle">
                Admin Dashboard
            </p>

            <form id="loginForm">

                <div class="form-group">
                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        required>
                </div>

                <div class="form-group">
                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required>
                </div>

                <button type="submit" id="loginButton">
                    Login
                </button>

                <div id="loginMessage"></div>

            </form>

        </div>

    </div>

    <script>
        const loginForm = document.getElementById("loginForm");
        const loginMessage = document.getElementById("loginMessage");
        const loginButton = document.getElementById("loginButton");

        loginForm.addEventListener("submit", async function(event) {

            event.preventDefault();

            const username =
                document.getElementById("username").value.trim();

            const password =
                document.getElementById("password").value;

            loginMessage.textContent = "";
            loginButton.disabled = true;

            try {

                const response = await fetch(
                    "../api/admin/login.php", {
                        method: "POST",

                        headers: {
                            "Content-Type": "application/json"
                        },

                        body: JSON.stringify({
                            username: username,
                            password: password
                        })
                    }
                );

                const data = await response.json();

                if (!response.ok || !data.success) {

                    loginMessage.textContent =
                        data.message || "Login failed.";

                    loginButton.disabled = false;

                    return;
                }

                /*
                 * Store admin authentication data locally.
                 */
                localStorage.setItem(
                    "admin_token",
                    data.admin_token
                );

                localStorage.setItem(
                    "admin_username",
                    data.username
                );

                localStorage.setItem(
                    "admin_full_name",
                    data.full_name
                );

                localStorage.setItem(
                    "admin_token_expires_at",
                    data.expires_at
                );

                window.location.href = "index.php";

            } catch (error) {

                loginMessage.textContent =
                    "Unable to connect to the server.";

                loginButton.disabled = false;
            }

        });
    </script>

</body>

</html>