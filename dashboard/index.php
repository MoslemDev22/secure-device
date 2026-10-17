<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Secure Device Management</title>

    <link
        rel="stylesheet"
        href="css/dashboard.css">

</head>


<body>


    <header class="dashboard-header">

        <div>

            <h1>
                Secure Device Management
            </h1>

            <p>
                Admin Dashboard
                <span id="adminWelcome"></span>
            </p>

        </div>


        <div class="header-actions">

            <button
                type="button"
                id="adminManagementButton">
                Admin Management
            </button>


            <button
                type="button"
                id="logoutButton">
                Logout
            </button>

        </div>

    </header>


    <main class="dashboard-container">


        <!-- Statistics -->

        <section class="stats-grid">


            <div class="stat-card">

                <h3>
                    Total Devices
                </h3>

                <strong id="totalDevices">
                    0
                </strong>

            </div>


            <div class="stat-card">

                <h3>
                    Online
                </h3>

                <strong id="onlineDevices">
                    0
                </strong>

            </div>


            <div class="stat-card">

                <h3>
                    Offline
                </h3>

                <strong id="offlineDevices">
                    0
                </strong>

            </div>


            <div class="stat-card">

                <h3>
                    Disabled
                </h3>

                <strong id="disabledDevices">
                    0
                </strong>

            </div>


        </section>



        <!-- Devices -->

        <section class="devices-section">


            <div class="section-header">

                <h2>
                    Devices
                </h2>


                <button
                    type="button"
                    id="refreshButton">
                    Refresh
                </button>

            </div>


            <div id="devicesMessage">
                Loading devices...
            </div>


            <div class="table-container">


                <table>

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Name
                            </th>

                            <th>
                                OS
                            </th>

                            <th>
                                IP Address
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Last Seen
                            </th>

                        </tr>

                    </thead>


                    <tbody
                        id="devicesTableBody"></tbody>


                </table>


            </div>


        </section>


    </main>



    <script>
        /*
         * Get admin token
         */

        const adminToken =
            localStorage.getItem("admin_token");



        /*
         * Redirect unauthenticated users
         */

        if (!adminToken) {

            window.location.href =
                "login.php";

        }



        /*
         * Get admin information
         */

        const adminFullName =
            localStorage.getItem(
                "admin_full_name"
            );


        const adminUsername =
            localStorage.getItem(
                "admin_username"
            );


        const adminWelcome =
            document.getElementById(
                "adminWelcome"
            );



        /*
         * Display admin name
         */

        if (adminFullName) {

            adminWelcome.textContent =
                " • Welcome, " + adminFullName;

        } else if (adminUsername) {

            adminWelcome.textContent =
                " • Welcome, " + adminUsername;

        }



        /*
         * API headers
         */

        const apiHeaders = {

            "Authorization": "Bearer " + adminToken,

            "Content-Type": "application/json"

        };



        /*
         * Load dashboard statistics
         */

        async function loadStats() {

            try {

                const response =
                    await fetch(
                        "../api/dashboard/stats.php", {
                            method: "GET",
                            headers: apiHeaders
                        }
                    );


                const data =
                    await response.json();


                if (
                    !response.ok ||
                    !data.success
                ) {

                    if (response.status === 401) {

                        logout();

                        return;

                    }


                    throw new Error(
                        data.message ||
                        "Failed to load statistics."
                    );

                }


                document.getElementById(
                        "totalDevices"
                    ).textContent =
                    data.stats.total_devices;


                document.getElementById(
                        "onlineDevices"
                    ).textContent =
                    data.stats.online_devices;


                document.getElementById(
                        "offlineDevices"
                    ).textContent =
                    data.stats.offline_devices;


                document.getElementById(
                        "disabledDevices"
                    ).textContent =
                    data.stats.disabled_devices;


            } catch (error) {

                console.error(error);

            }

        }



        /*
         * Load devices
         */

        async function loadDevices() {

            const message =
                document.getElementById(
                    "devicesMessage"
                );


            const tableBody =
                document.getElementById(
                    "devicesTableBody"
                );


            message.textContent =
                "Loading devices...";


            tableBody.innerHTML = "";


            try {

                const response =
                    await fetch(
                        "../api/dashboard/devices.php", {
                            method: "GET",
                            headers: apiHeaders
                        }
                    );


                const data =
                    await response.json();


                if (
                    !response.ok ||
                    !data.success
                ) {

                    if (response.status === 401) {

                        logout();

                        return;

                    }


                    throw new Error(
                        data.message ||
                        "Failed to load devices."
                    );

                }


                if (
                    data.devices.length === 0
                ) {

                    message.textContent =
                        "No devices found.";

                    return;

                }


                message.textContent = "";


                data.devices.forEach(
                    function(device) {

                        const row =
                            document.createElement(
                                "tr"
                            );


                        row.innerHTML = `

                    <td>
                        ${device.device_id}
                    </td>

                    <td>

                        <a
                            href="device.php?device_id=${device.device_id}"
                            class="device-link"
                        >
                            ${device.device_name}
                        </a>

                    </td>

                    <td>

                        ${device.os_name}
                        ${device.os_version || ""}

                    </td>

                    <td>

                        ${device.ip_address || "-"}

                    </td>

                    <td>

                        <span
                            class="status ${device.status}"
                        >
                            ${device.status}
                        </span>

                    </td>

                    <td>

                        ${device.last_seen_at || "-"}

                    </td>

                `;


                        tableBody.appendChild(
                            row
                        );

                    }
                );


            } catch (error) {

                message.textContent =
                    error.message;

            }

        }



        /*
         * Logout
         */

        function logout() {

            localStorage.removeItem(
                "admin_token"
            );


            localStorage.removeItem(
                "admin_username"
            );


            localStorage.removeItem(
                "admin_full_name"
            );


            localStorage.removeItem(
                "admin_token_expires_at"
            );


            window.location.href =
                "login.php";

        }



        /*
         * Load dashboard
         */

        async function loadDashboard() {

            await loadStats();

            await loadDevices();

        }



        /*
         * Logout button
         */

        document
            .getElementById(
                "logoutButton"
            )
            .addEventListener(
                "click",
                logout
            );



        /*
         * Admin Management button
         */

        document
            .getElementById(
                "adminManagementButton"
            )
            .addEventListener(
                "click",
                function() {

                    window.location.href =
                        "admins.php";

                }
            );



        /*
         * Refresh button
         */

        document
            .getElementById(
                "refreshButton"
            )
            .addEventListener(
                "click",
                loadDashboard
            );



        /*
         * Start dashboard
         */

        loadDashboard();
    </script>


</body>

</html>