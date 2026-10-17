<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Device Details - Secure Device Management</title>

    <link
        rel="stylesheet"
        href="css/dashboard.css">

</head>

<body>

    <header class="dashboard-header">

        <div>

            <h1>Device Details</h1>

            <p>Secure Device Management</p>

        </div>

        <div class="header-actions">

            <button id="backButton">
                Back
            </button>

            <button id="logoutButton">
                Logout
            </button>

        </div>

    </header>


    <main class="dashboard-container">

        <div id="pageMessage">
            Loading device...
        </div>


        <!-- Device Information -->

        <section class="details-section">

            <div class="section-header">

                <h2>Device Information</h2>

            </div>


            <div class="details-grid">

                <div class="detail-item">

                    <span>Device ID</span>

                    <strong id="deviceId">-</strong>

                </div>


                <div class="detail-item">

                    <span>Device Name</span>

                    <strong id="deviceName">-</strong>

                </div>


                <div class="detail-item">

                    <span>Hostname</span>

                    <strong id="hostname">-</strong>

                </div>


                <div class="detail-item">

                    <span>Operating System</span>

                    <strong id="os">-</strong>

                </div>


                <div class="detail-item">

                    <span>Architecture</span>

                    <strong id="architecture">-</strong>

                </div>


                <div class="detail-item">

                    <span>CPU</span>

                    <strong id="cpu">-</strong>

                </div>


                <div class="detail-item">

                    <span>CPU Cores</span>

                    <strong id="cpuCores">-</strong>

                </div>


                <div class="detail-item">

                    <span>RAM</span>

                    <strong id="ram">-</strong>

                </div>


                <div class="detail-item">

                    <span>IP Address</span>

                    <strong id="ipAddress">-</strong>

                </div>


                <div class="detail-item">

                    <span>Agent</span>

                    <strong id="agent">-</strong>

                </div>


                <div class="detail-item">

                    <span>Status</span>

                    <strong id="status">-</strong>

                </div>


                <div class="detail-item">

                    <span>Last Seen</span>

                    <strong id="lastSeen">-</strong>

                </div>

            </div>

        </section>


        <!-- Latest Metrics -->

        <section class="details-section">

            <div class="section-header">

                <h2>Latest Metrics</h2>

            </div>


            <div class="details-grid">

                <div class="detail-item">

                    <span>CPU Usage</span>

                    <strong id="latestCpu">-</strong>

                </div>


                <div class="detail-item">

                    <span>RAM Usage</span>

                    <strong id="latestRam">-</strong>

                </div>


                <div class="detail-item">

                    <span>Disk Usage</span>

                    <strong id="latestDisk">-</strong>

                </div>


                <div class="detail-item">

                    <span>RAM Used</span>

                    <strong id="latestRamUsed">-</strong>

                </div>


                <div class="detail-item">

                    <span>Disk Used</span>

                    <strong id="latestDiskUsed">-</strong>

                </div>


                <div class="detail-item">

                    <span>Disk Free</span>

                    <strong id="latestDiskFree">-</strong>

                </div>


                <div class="detail-item">

                    <span>Uptime</span>

                    <strong id="latestUptime">-</strong>

                </div>

            </div>


            <div id="metricsRecordedAt">
                Last metrics update: -
            </div>

        </section>


        <!-- Metrics Charts -->

        <section class="details-section">

            <div class="section-header">

                <h2>Metrics Charts</h2>

            </div>


            <div class="charts-grid">

                <div class="chart-card">

                    <h3>CPU Usage</h3>

                    <canvas id="cpuChart"></canvas>

                </div>


                <div class="chart-card">

                    <h3>RAM Usage</h3>

                    <canvas id="ramChart"></canvas>

                </div>


                <div class="chart-card">

                    <h3>Disk Usage</h3>

                    <canvas id="diskChart"></canvas>

                </div>

            </div>

        </section>


        <!-- Metrics History -->

        <section class="details-section">

            <div class="section-header">

                <h2>Metrics History</h2>

            </div>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>CPU %</th>

                            <th>RAM %</th>

                            <th>Disk %</th>

                            <th>Uptime</th>

                            <th>Recorded At</th>

                        </tr>

                    </thead>


                    <tbody id="metricsTableBody">

                    </tbody>

                </table>

            </div>

        </section>


        <!-- Device Logs -->

        <section class="details-section">

            <div class="section-header">

                <h2>Device Logs</h2>

            </div>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>Event</th>

                            <th>Message</th>

                            <th>IP Address</th>

                            <th>Created At</th>

                        </tr>

                    </thead>


                    <tbody id="logsTableBody">

                    </tbody>

                </table>

            </div>

        </section>


        <!-- Latest Heartbeat -->

        <section class="details-section">

            <div class="section-header">

                <h2>Latest Heartbeat</h2>

            </div>


            <div class="details-grid">

                <div class="detail-item">

                    <span>IP Address</span>

                    <strong id="heartbeatIp">-</strong>

                </div>


                <div class="detail-item">

                    <span>Received At</span>

                    <strong id="heartbeatTime">-</strong>

                </div>

            </div>

        </section>

    </main>


    <script>
        /*
         * Get admin token.
         */
        const adminToken =
            localStorage.getItem("admin_token");


        /*
         * Get device ID from URL.
         */
        const urlParams =
            new URLSearchParams(
                window.location.search
            );


        const deviceId =
            urlParams.get("device_id");


        /*
         * Redirect if admin token is missing.
         */
        if (!adminToken) {

            window.location.href =
                "login.php";

        }


        /*
         * Validate device ID.
         */
        if (!deviceId) {

            document.getElementById(
                    "pageMessage"
                ).textContent =
                "Device ID is missing.";

            throw new Error(
                "Device ID is missing."
            );

        }


        /*
         * Common API headers.
         */
        const apiHeaders = {

            "Authorization": "Bearer " + adminToken,

            "Content-Type": "application/json"

        };


        /*
         * Store metrics for charts.
         */
        let metricsHistory = [];


        /*
         * Logout.
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
         * Format uptime.
         */
        function formatUptime(seconds) {

            if (
                seconds === null ||
                seconds === undefined
            ) {

                return "-";

            }


            seconds =
                Number(seconds);


            const days =
                Math.floor(
                    seconds / 86400
                );


            const hours =
                Math.floor(
                    (seconds % 86400) / 3600
                );


            const minutes =
                Math.floor(
                    (seconds % 3600) / 60
                );


            return (
                days + "d " +
                hours + "h " +
                minutes + "m"
            );

        }


        /*
         * Load device details.
         */
        async function loadDevice() {

            try {

                const response =
                    await fetch(
                        "../api/dashboard/device.php?device_id=" +
                        encodeURIComponent(deviceId), {
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

                    if (
                        response.status === 401
                    ) {

                        logout();

                        return;

                    }


                    throw new Error(
                        data.message ||
                        "Failed to load device."
                    );

                }


                const device =
                    data.device;


                document.getElementById(
                        "deviceId"
                    ).textContent =
                    device.device_id;


                document.getElementById(
                        "deviceName"
                    ).textContent =
                    device.device_name;


                document.getElementById(
                        "hostname"
                    ).textContent =
                    device.hostname || "-";


                document.getElementById(
                        "os"
                    ).textContent =
                    (
                        device.os_name || ""
                    ) +
                    " " +
                    (
                        device.os_version || ""
                    );


                document.getElementById(
                        "architecture"
                    ).textContent =
                    device.architecture || "-";


                document.getElementById(
                        "cpu"
                    ).textContent =
                    device.cpu_name || "-";


                document.getElementById(
                        "cpuCores"
                    ).textContent =
                    device.cpu_cores || "-";


                document.getElementById(
                        "ram"
                    ).textContent =
                    device.ram_total_mb ?
                    device.ram_total_mb +
                    " MB" :
                    "-";


                document.getElementById(
                        "ipAddress"
                    ).textContent =
                    device.ip_address || "-";


                document.getElementById(
                        "agent"
                    ).textContent =
                    (
                        device.agent_name || "-"
                    ) +
                    " " +
                    (
                        device.agent_version || ""
                    );


                document.getElementById(
                        "status"
                    ).textContent =
                    device.status;


                document.getElementById(
                        "lastSeen"
                    ).textContent =
                    device.last_seen_at || "-";


                /*
                 * Latest metrics.
                 */
                if (data.latest_metrics) {

                    const metrics =
                        data.latest_metrics;


                    document.getElementById(
                            "latestCpu"
                        ).textContent =
                        metrics.cpu_usage +
                        "%";


                    document.getElementById(
                            "latestRam"
                        ).textContent =
                        metrics.ram_usage_percent +
                        "%";


                    document.getElementById(
                            "latestDisk"
                        ).textContent =
                        metrics.disk_usage_percent +
                        "%";


                    document.getElementById(
                            "latestRamUsed"
                        ).textContent =
                        metrics.ram_used_mb +
                        " / " +
                        metrics.ram_total_mb +
                        " MB";


                    document.getElementById(
                            "latestDiskUsed"
                        ).textContent =
                        metrics.disk_used_gb +
                        " GB";


                    document.getElementById(
                            "latestDiskFree"
                        ).textContent =
                        metrics.disk_free_gb +
                        " GB";


                    document.getElementById(
                            "latestUptime"
                        ).textContent =
                        formatUptime(
                            metrics.uptime_seconds
                        );


                    document.getElementById(
                            "metricsRecordedAt"
                        ).textContent =
                        "Last metrics update: " +
                        metrics.recorded_at;

                }


                /*
                 * Latest heartbeat.
                 */
                if (data.latest_heartbeat) {

                    document.getElementById(
                            "heartbeatIp"
                        ).textContent =
                        data.latest_heartbeat.ip_address ||
                        "-";


                    document.getElementById(
                            "heartbeatTime"
                        ).textContent =
                        data.latest_heartbeat.received_at ||
                        "-";

                }


                document.getElementById(
                    "pageMessage"
                ).textContent = "";


            } catch (error) {

                document.getElementById(
                        "pageMessage"
                    ).textContent =
                    error.message;

            }

        }


        /*
         * Load metrics history.
         */
        async function loadMetrics() {

            const tableBody =
                document.getElementById(
                    "metricsTableBody"
                );


            tableBody.innerHTML = "";


            try {

                const response =
                    await fetch(
                        "../api/dashboard/metrics.php?device_id=" +
                        encodeURIComponent(deviceId) +
                        "&limit=20", {
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

                    if (
                        response.status === 401
                    ) {

                        logout();

                        return;

                    }


                    throw new Error(
                        data.message ||
                        "Failed to load metrics."
                    );

                }


                metricsHistory =
                    data.metrics || [];


                if (
                    metricsHistory.length === 0
                ) {

                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="5">
                                No metrics found.
                            </td>
                        </tr>
                    `;

                    drawCharts();

                    return;

                }


                metricsHistory.forEach(
                    function(metric) {

                        const row =
                            document.createElement(
                                "tr"
                            );


                        row.innerHTML = `

                            <td>
                                ${metric.cpu_usage}%
                            </td>

                            <td>
                                ${metric.ram_usage_percent}%
                            </td>

                            <td>
                                ${metric.disk_usage_percent}%
                            </td>

                            <td>
                                ${formatUptime(
                                    metric.uptime_seconds
                                )}
                            </td>

                            <td>
                                ${metric.recorded_at}
                            </td>

                        `;


                        tableBody.appendChild(row);

                    }
                );


                drawCharts();


            } catch (error) {

                tableBody.innerHTML = `

                    <tr>

                        <td colspan="5">
                            ${error.message}
                        </td>

                    </tr>

                `;

            }

        }


        /*
         * Draw a simple line chart.
         */
        function drawLineChart(
            canvasId,
            values,
            labels,
            title
        ) {

            const canvas =
                document.getElementById(
                    canvasId
                );


            if (!canvas) {

                return;

            }


            const context =
                canvas.getContext("2d");


            const width =
                canvas.width =
                canvas.clientWidth * 2;


            const height =
                canvas.height =
                260;


            context.clearRect(
                0,
                0,
                width,
                height
            );


            if (!values.length) {

                context.font =
                    "28px Arial";

                context.fillText(
                    "No data",
                    30,
                    60
                );

                return;

            }


            const padding = 45;


            const chartWidth =
                width -
                padding * 2;


            const chartHeight =
                height -
                padding * 2;


            /*
             * Background.
             */
            context.strokeStyle =
                "#dddddd";


            context.lineWidth = 1;


            for (
                let i = 0; i <= 4; i++
            ) {

                const y =
                    padding +
                    (
                        chartHeight *
                        i /
                        4
                    );


                context.beginPath();

                context.moveTo(
                    padding,
                    y
                );

                context.lineTo(
                    width - padding,
                    y
                );

                context.stroke();

            }


            /*
             * Y-axis labels.
             */
            context.fillStyle =
                "#666666";

            context.font =
                "20px Arial";


            for (
                let i = 0; i <= 4; i++
            ) {

                const value =
                    100 -
                    (
                        i * 25
                    );


                const y =
                    padding +
                    (
                        chartHeight *
                        i /
                        4
                    ) +
                    7;


                context.fillText(
                    value + "%",
                    5,
                    y
                );

            }


            /*
             * Draw line.
             */
            context.beginPath();


            values.forEach(
                function(value, index) {

                    const x =
                        padding +
                        (
                            values.length === 1 ?
                            chartWidth / 2 :
                            chartWidth *
                            index /
                            (
                                values.length - 1
                            )
                        );


                    const y =
                        padding +
                        chartHeight *
                        (
                            1 -
                            value / 100
                        );


                    if (index === 0) {

                        context.moveTo(
                            x,
                            y
                        );

                    } else {

                        context.lineTo(
                            x,
                            y
                        );

                    }

                }
            );


            context.strokeStyle =
                "#222222";

            context.lineWidth = 4;

            context.stroke();


            /*
             * Draw points.
             */
            values.forEach(
                function(value, index) {

                    const x =
                        padding +
                        (
                            values.length === 1 ?
                            chartWidth / 2 :
                            chartWidth *
                            index /
                            (
                                values.length - 1
                            )
                        );


                    const y =
                        padding +
                        chartHeight *
                        (
                            1 -
                            value / 100
                        );


                    context.beginPath();

                    context.arc(
                        x,
                        y,
                        6,
                        0,
                        Math.PI * 2
                    );

                    context.fillStyle =
                        "#222222";

                    context.fill();

                }
            );


            /*
             * X-axis labels.
             */
            context.fillStyle =
                "#666666";

            context.font =
                "16px Arial";


            if (labels.length) {

                context.fillText(
                    labels[0],
                    padding,
                    height - 10
                );


                if (labels.length > 1) {

                    const lastLabel =
                        labels[
                            labels.length - 1
                        ];


                    const textWidth =
                        context.measureText(
                            lastLabel
                        ).width;


                    context.fillText(
                        lastLabel,
                        width -
                        padding -
                        textWidth,
                        height - 10
                    );

                }

            }


            /*
             * Chart title.
             */
            context.font =
                "bold 18px Arial";

            context.fillStyle =
                "#222222";

            context.fillText(
                title,
                padding,
                25
            );

        }


        /*
         * Draw all charts.
         */
        function drawCharts() {

            const orderedMetrics = [...metricsHistory]
                .reverse();


            const labels =
                orderedMetrics.map(
                    function(metric) {

                        return metric.recorded_at;

                    }
                );


            const cpuValues =
                orderedMetrics.map(
                    function(metric) {

                        return Number(
                            metric.cpu_usage || 0
                        );

                    }
                );


            const ramValues =
                orderedMetrics.map(
                    function(metric) {

                        return Number(
                            metric.ram_usage_percent || 0
                        );

                    }
                );


            const diskValues =
                orderedMetrics.map(
                    function(metric) {

                        return Number(
                            metric.disk_usage_percent || 0
                        );

                    }
                );


            drawLineChart(
                "cpuChart",
                cpuValues,
                labels,
                "CPU"
            );


            drawLineChart(
                "ramChart",
                ramValues,
                labels,
                "RAM"
            );


            drawLineChart(
                "diskChart",
                diskValues,
                labels,
                "Disk"
            );

        }


        /*
         * Load logs.
         */
        async function loadLogs() {

            const tableBody =
                document.getElementById(
                    "logsTableBody"
                );


            tableBody.innerHTML = "";


            try {

                const response =
                    await fetch(
                        "../api/dashboard/logs.php?device_id=" +
                        encodeURIComponent(deviceId) +
                        "&limit=20", {
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

                    if (
                        response.status === 401
                    ) {

                        logout();

                        return;

                    }


                    throw new Error(
                        data.message ||
                        "Failed to load logs."
                    );

                }


                if (!data.logs.length) {

                    tableBody.innerHTML = `

                        <tr>

                            <td colspan="4">
                                No logs found.
                            </td>

                        </tr>

                    `;

                    return;

                }


                data.logs.forEach(
                    function(log) {

                        const row =
                            document.createElement(
                                "tr"
                            );


                        row.innerHTML = `

                            <td>
                                ${log.event_type}
                            </td>

                            <td>
                                ${log.message || "-"}
                            </td>

                            <td>
                                ${log.ip_address || "-"}
                            </td>

                            <td>
                                ${log.created_at}
                            </td>

                        `;


                        tableBody.appendChild(row);

                    }
                );


            } catch (error) {

                tableBody.innerHTML = `

                    <tr>

                        <td colspan="4">
                            ${error.message}
                        </td>

                    </tr>

                `;

            }

        }


        /*
         * Back button.
         */
        document.getElementById(
            "backButton"
        ).addEventListener(
            "click",
            function() {

                window.location.href =
                    "index.php";

            }
        );


        /*
         * Logout button.
         */
        document.getElementById(
            "logoutButton"
        ).addEventListener(
            "click",
            logout
        );


        /*
         * Load all device data.
         */
        async function loadPage() {

            await loadDevice();

            await loadMetrics();

            await loadLogs();

        }


        /*
         * Initial load.
         */
        loadPage();
    </script>

</body>

</html>