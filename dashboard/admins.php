<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Management - Secure Device Management</title>

    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

    <header class="dashboard-header">

        <div>
            <h1>Admin Management</h1>
            <p>Create a new administrator account</p>
        </div>

        <div class="header-actions">

            <button
                type="button"
                onclick="window.location.href='index.php'">
                Dashboard
            </button>

            <button
                type="button"
                id="logoutButton">
                Logout
            </button>

        </div>

    </header>


    <main class="dashboard-container">

        <section class="section-card">

            <div class="section-header">

                <div>
                    <h2>Create Admin</h2>
                    <p>Create a new administrator account.</p>
                </div>

            </div>


            <form id="adminForm" class="admin-form">

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter username"
                        autocomplete="username"
                        required>

                </div>


                <div class="form-group">

                    <label for="fullName">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="fullName"
                        name="full_name"
                        placeholder="Enter full name"
                        autocomplete="name"
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
                        placeholder="Enter password"
                        autocomplete="new-password"
                        required>

                </div>


                <div class="form-group">

                    <label for="confirmPassword">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirm_password"
                        placeholder="Confirm password"
                        autocomplete="new-password"
                        required>

                </div>


                <div
                    id="adminMessage"
                    class="form-message"></div>


                <button
                    type="submit"
                    id="createAdminButton"
                    class="primary-button">
                    Create Admin
                </button>

            </form>

        </section>

    </main>


    <script>
        const adminToken =
            localStorage.getItem("admin_token");


        /*
         * Redirect unauthenticated users
         */
        if (!adminToken) {

            window.location.href = "login.php";

        }


        /*
         * API headers
         */
        const apiHeaders = {

            "Authorization": "Bearer " + adminToken,

            "Content-Type": "application/json"

        };


        /*
         * Logout
         */
        function logout() {

            localStorage.removeItem("admin_token");

            localStorage.removeItem("admin_username");

            localStorage.removeItem("admin_full_name");

            localStorage.removeItem(
                "admin_token_expires_at"
            );

            window.location.href = "login.php";

        }


        /*
         * Create admin
         */
        document
            .getElementById("adminForm")
            .addEventListener(
                "submit",
                async function(event) {

                    event.preventDefault();


                    const username =
                        document
                        .getElementById("username")
                        .value
                        .trim();


                    const fullName =
                        document
                        .getElementById("fullName")
                        .value
                        .trim();


                    const password =
                        document
                        .getElementById("password")
                        .value;


                    const confirmPassword =
                        document
                        .getElementById("confirmPassword")
                        .value;


                    const message =
                        document
                        .getElementById("adminMessage");


                    const button =
                        document
                        .getElementById(
                            "createAdminButton"
                        );


                    message.textContent = "";

                    message.className =
                        "form-message";


                    /*
                     * Validate passwords
                     */
                    if (password !== confirmPassword) {

                        message.textContent =
                            "Passwords do not match.";

                        message.classList.add(
                            "error-message"
                        );

                        return;

                    }


                    if (password.length < 6) {

                        message.textContent =
                            "Password must be at least 6 characters.";

                        message.classList.add(
                            "error-message"
                        );

                        return;

                    }


                    /*
                     * Disable button
                     */
                    button.disabled = true;

                    button.textContent =
                        "Creating...";


                    try {

                        const response =
                            await fetch(
                                "../api/admin/create.php", {
                                    method: "POST",

                                    headers: apiHeaders,

                                    body: JSON.stringify({

                                        username: username,

                                        password: password,

                                        full_name: fullName

                                    })
                                }
                            );


                        const data =
                            await response.json();


                        /*
                         * Handle expired token
                         */
                        if (response.status === 401) {

                            logout();

                            return;

                        }


                        if (
                            !response.ok ||
                            !data.success
                        ) {

                            throw new Error(
                                data.message ||
                                "Failed to create admin."
                            );

                        }


                        /*
                         * Success
                         */
                        message.textContent =
                            data.message ||
                            "Admin created successfully.";

                        message.classList.add(
                            "success-message"
                        );


                        /*
                         * Clear form
                         */
                        document
                            .getElementById("adminForm")
                            .reset();


                    } catch (error) {

                        console.error(error);


                        message.textContent =
                            error.message ||
                            "An error occurred.";

                        message.classList.add(
                            "error-message"
                        );

                    } finally {

                        button.disabled = false;

                        button.textContent =
                            "Create Admin";

                    }

                }
            );


        /*
         * Logout button
         */
        document
            .getElementById("logoutButton")
            .addEventListener(
                "click",
                logout
            );
    </script>

</body>

</html>