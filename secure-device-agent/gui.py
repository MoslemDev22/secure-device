import threading
import tkinter as tk

from api.heartbeat import send_heartbeat
from core.activity_log import ActivityLogger
from core.device import get_device_info
from core.metrics import get_metrics


AGENT_NAME = "PythonAgent"
AGENT_VERSION = "1.0.0"

HEARTBEAT_CHECK_INTERVAL = 10


class SecureDeviceAgentGUI:

    def __init__(self, root):
        self.root = root

        self.root.title("Secure Device Agent")
        self.root.geometry("1050x760")
        self.root.minsize(950, 680)

        self.root.configure(bg="#050805")

        self.logger = ActivityLogger()

        self.connection_status = "CHECKING"
        self.last_check = "-"

        self.build_gui()

        self.logger.add(
            "GUI Started"
        )

        self.update_activity_log()

        self.refresh_system_data()
        self.start_connection_monitor()

    # ---------------------------------------------------------
    # GUI
    # ---------------------------------------------------------

    def build_gui(self):

        title = tk.Label(
            self.root,
            text="SECURE DEVICE AGENT",
            font=("Consolas", 22, "bold"),
            fg="#00ff66",
            bg="#050805"
        )

        title.pack(
            pady=(20, 5)
        )

        subtitle = tk.Label(
            self.root,
            text="LINUX DEVICE MANAGEMENT CLIENT",
            font=("Consolas", 10),
            fg="#55aa77",
            bg="#050805"
        )

        subtitle.pack(
            pady=(0, 15)
        )

        main_frame = tk.Frame(
            self.root,
            bg="#050805"
        )

        main_frame.pack(
            fill="both",
            expand=True,
            padx=20,
            pady=10
        )

        # -----------------------------------------------------
        # LEFT SIDE
        # -----------------------------------------------------

        left_frame = tk.Frame(
            main_frame,
            bg="#050805"
        )

        left_frame.pack(
            side="left",
            fill="both",
            expand=True,
            padx=(0, 8)
        )

        self.create_section(
            left_frame,
            "LIVE SYSTEM METRICS"
        )

        self.cpu_label = self.create_value_row(
            left_frame,
            "CPU"
        )

        self.ram_label = self.create_value_row(
            left_frame,
            "RAM"
        )

        self.disk_label = self.create_value_row(
            left_frame,
            "DISK"
        )

        self.uptime_label = self.create_value_row(
            left_frame,
            "UPTIME"
        )

        self.create_section(
            left_frame,
            "DEVICE INFORMATION"
        )

        self.device_labels = {}

        device_fields = [
            "DEVICE",
            "OPERATING SYSTEM",
            "OS VERSION",
            "ARCHITECTURE",
            "CPU",
            "CORES",
            "RAM TOTAL",
            "IP ADDRESS",
            "AGENT",
            "AGENT VERSION"
        ]

        for field in device_fields:

            self.device_labels[field] = self.create_value_row(
                left_frame,
                field
            )

        self.create_section(
            left_frame,
            "RESOURCE DETAILS"
        )

        self.ram_used_label = self.create_value_row(
            left_frame,
            "RAM USED"
        )

        self.disk_used_label = self.create_value_row(
            left_frame,
            "DISK USED"
        )

        self.disk_free_label = self.create_value_row(
            left_frame,
            "DISK FREE"
        )

        self.disk_total_label = self.create_value_row(
            left_frame,
            "DISK TOTAL"
        )

        # -----------------------------------------------------
        # RIGHT SIDE
        # -----------------------------------------------------

        right_frame = tk.Frame(
            main_frame,
            bg="#050805"
        )

        right_frame.pack(
            side="right",
            fill="both",
            expand=True,
            padx=(8, 0)
        )

        self.create_section(
            right_frame,
            "AGENT STATUS"
        )

        self.status_dot = tk.Label(
            right_frame,
            text="●",
            font=("Consolas", 18, "bold"),
            fg="#ffaa00",
            bg="#050805"
        )

        self.status_dot.pack(
            anchor="w",
            padx=10,
            pady=(5, 0)
        )

        self.status_label = tk.Label(
            right_frame,
            text="CHECKING",
            font=("Consolas", 13, "bold"),
            fg="#ffaa00",
            bg="#050805"
        )

        self.status_label.pack(
            anchor="w",
            padx=10
        )

        self.server_label = self.create_value_row(
            right_frame,
            "SERVER"
        )

        self.heartbeat_label = self.create_value_row(
            right_frame,
            "HEARTBEAT"
        )

        self.connection_label = self.create_value_row(
            right_frame,
            "CONNECTION"
        )

        self.last_check_label = self.create_value_row(
            right_frame,
            "LAST CHECK"
        )

        # -----------------------------------------------------
        # ACTIVITY LOG
        # -----------------------------------------------------

        self.create_section(
            right_frame,
            "ACTIVITY LOG"
        )

        log_frame = tk.Frame(
            right_frame,
            bg="#050805"
        )

        log_frame.pack(
            fill="both",
            expand=True,
            padx=10,
            pady=5
        )

        scrollbar = tk.Scrollbar(
            log_frame
        )

        scrollbar.pack(
            side="right",
            fill="y"
        )

        self.activity_text = tk.Text(
            log_frame,
            height=18,
            bg="#020402",
            fg="#00ff66",
            insertbackground="#00ff66",
            font=("Consolas", 9),
            relief="solid",
            borderwidth=1,
            yscrollcommand=scrollbar.set
        )

        self.activity_text.pack(
            side="left",
            fill="both",
            expand=True
        )

        scrollbar.config(
            command=self.activity_text.yview
        )

        self.activity_text.config(
            state="disabled"
        )

        # -----------------------------------------------------
        # BUTTONS
        # -----------------------------------------------------

        button_frame = tk.Frame(
            self.root,
            bg="#050805"
        )

        button_frame.pack(
            fill="x",
            padx=20,
            pady=(5, 20)
        )

        refresh_button = tk.Button(
            button_frame,
            text="REFRESH",
            command=self.refresh_system_data,
            font=("Consolas", 10, "bold"),
            bg="#0a1a0a",
            fg="#00ff66",
            activebackground="#123012",
            activeforeground="#00ff66",
            relief="solid",
            borderwidth=1,
            padx=20,
            pady=8
        )

        refresh_button.pack(
            side="left",
            padx=5
        )

        copy_button = tk.Button(
            button_frame,
            text="COPY ALL",
            command=self.copy_all,
            font=("Consolas", 10, "bold"),
            bg="#0a1a0a",
            fg="#00ff66",
            activebackground="#123012",
            activeforeground="#00ff66",
            relief="solid",
            borderwidth=1,
            padx=20,
            pady=8
        )

        copy_button.pack(
            side="left",
            padx=5
        )

        clear_log_button = tk.Button(
            button_frame,
            text="CLEAR LOG",
            command=self.clear_activity_log,
            font=("Consolas", 10, "bold"),
            bg="#1a0a0a",
            fg="#ff6666",
            activebackground="#301212",
            activeforeground="#ff6666",
            relief="solid",
            borderwidth=1,
            padx=20,
            pady=8
        )

        clear_log_button.pack(
            side="left",
            padx=5
        )

    # ---------------------------------------------------------
    # SECTION
    # ---------------------------------------------------------

    def create_section(self, parent, title):

        label = tk.Label(
            parent,
            text=f"[ {title} ]",
            font=("Consolas", 11, "bold"),
            fg="#00ff66",
            bg="#050805"
        )

        label.pack(
            anchor="w",
            padx=10,
            pady=(12, 4)
        )

    # ---------------------------------------------------------
    # VALUE ROW
    # ---------------------------------------------------------

    def create_value_row(self, parent, title):

        frame = tk.Frame(
            parent,
            bg="#050805"
        )

        frame.pack(
            fill="x",
            padx=10,
            pady=2
        )

        title_label = tk.Label(
            frame,
            text=f"{title}:",
            width=18,
            anchor="w",
            font=("Consolas", 9),
            fg="#77aa88",
            bg="#050805"
        )

        title_label.pack(
            side="left"
        )

        value_label = tk.Label(
            frame,
            text="-",
            anchor="w",
            font=("Consolas", 9),
            fg="#dddddd",
            bg="#050805"
        )

        value_label.pack(
            side="left",
            fill="x",
            expand=True
        )

        return value_label

    # ---------------------------------------------------------
    # ACTIVITY LOG
    # ---------------------------------------------------------

    def add_log(self, message, level="INFO"):

        self.logger.add(
            message,
            level
        )

        self.update_activity_log()

    def update_activity_log(self):

        entries = self.logger.get_entries()

        self.activity_text.config(
            state="normal"
        )

        self.activity_text.delete(
            "1.0",
            tk.END
        )

        for entry in entries:

            line = (
                f"[{entry['time']}] "
                f"[{entry['level']}] "
                f"{entry['message']}\n"
            )

            self.activity_text.insert(
                tk.END,
                line
            )

        self.activity_text.see(
            tk.END
        )

        self.activity_text.config(
            state="disabled"
        )

    def clear_activity_log(self):

        self.logger.clear()

        self.logger.add(
            "Activity log cleared"
        )

        self.update_activity_log()

    # ---------------------------------------------------------
    # SYSTEM DATA
    # ---------------------------------------------------------

    def refresh_system_data(self):

        try:

            device_info = get_device_info()
            metrics = get_metrics()

            self.device_labels["DEVICE"].config(
                text=device_info["device_name"]
            )

            self.device_labels["OPERATING SYSTEM"].config(
                text=device_info["operating_system"]
            )

            self.device_labels["OS VERSION"].config(
                text=device_info["os_version"]
            )

            self.device_labels["ARCHITECTURE"].config(
                text=device_info["architecture"]
            )

            self.device_labels["CPU"].config(
                text=device_info["cpu_model"]
            )

            self.device_labels["CORES"].config(
                text=device_info["cpu_cores"]
            )

            self.device_labels["RAM TOTAL"].config(
                text=f"{device_info['ram_total_mb']} MB"
            )

            self.device_labels["IP ADDRESS"].config(
                text=device_info["ip_address"]
            )

            self.device_labels["AGENT"].config(
                text=AGENT_NAME
            )

            self.device_labels["AGENT VERSION"].config(
                text=AGENT_VERSION
            )

            self.cpu_label.config(
                text=f"{metrics['cpu_usage_percent']}%"
            )

            self.ram_label.config(
                text=f"{metrics['ram_usage_percent']}%"
            )

            self.disk_label.config(
                text=f"{metrics['disk_usage_percent']}%"
            )

            self.ram_used_label.config(
                text=f"{metrics['ram_used_mb']} MB"
            )

            self.disk_used_label.config(
                text=f"{metrics['disk_used_gb']} GB"
            )

            self.disk_free_label.config(
                text=f"{metrics['disk_free_gb']} GB"
            )

            self.disk_total_label.config(
                text=f"{metrics['disk_total_gb']} GB"
            )

            self.uptime_label.config(
                text=self.format_uptime(
                    metrics["uptime_seconds"]
                )
            )

            self.server_label.config(
                text="https://moslemdev.ir"
            )

            self.add_log(
                "System metrics updated"
            )

        except Exception as error:

            self.add_log(
                f"System data error: {error}",
                "ERROR"
            )

    # ---------------------------------------------------------
    # CONNECTION MONITOR
    # ---------------------------------------------------------

    def start_connection_monitor(self):

        thread = threading.Thread(
            target=self.connection_check_worker,
            daemon=True
        )

        thread.start()

    def connection_check_worker(self):

        while True:

            try:

                success = send_heartbeat()

                self.root.after(
                    0,
                    self.update_connection_status,
                    success
                )

            except Exception as error:

                self.root.after(
                    0,
                    self.update_connection_status,
                    False
                )

                self.root.after(
                    0,
                    self.add_log,
                    f"Heartbeat error: {error}",
                    "ERROR"
                )

            threading.Event().wait(
                HEARTBEAT_CHECK_INTERVAL
            )

    # ---------------------------------------------------------
    # CONNECTION STATUS
    # ---------------------------------------------------------

    def update_connection_status(self, online):

        previous_status = self.connection_status

        if online:

            self.connection_status = "ONLINE"

            self.status_dot.config(
                fg="#00ff66"
            )

            self.status_label.config(
                text="ONLINE",
                fg="#00ff66"
            )

            self.heartbeat_label.config(
                text="SUCCESS",
                fg="#00ff66"
            )

            self.connection_label.config(
                text="CONNECTED",
                fg="#00ff66"
            )

            if previous_status == "OFFLINE":

                self.add_log(
                    "Connection Restored",
                    "INFO"
                )

            elif previous_status == "CHECKING":

                self.add_log(
                    "Heartbeat Success",
                    "INFO"
                )

        else:

            self.connection_status = "OFFLINE"

            self.status_dot.config(
                fg="#ff3333"
            )

            self.status_label.config(
                text="OFFLINE",
                fg="#ff3333"
            )

            self.heartbeat_label.config(
                text="FAILED",
                fg="#ff3333"
            )

            self.connection_label.config(
                text="DISCONNECTED",
                fg="#ff3333"
            )

            if previous_status != "OFFLINE":

                self.add_log(
                    "Connection Lost",
                    "ERROR"
                )

        self.last_check_label.config(
            text=self.get_current_time()
        )

    # ---------------------------------------------------------
    # COPY ALL
    # ---------------------------------------------------------

    def copy_all(self):

        self.logger.add(
            "Copy All requested"
        )

        self.update_activity_log()

        device_info = get_device_info()
        metrics = get_metrics()

        activity_entries = self.logger.get_entries()

        activity_log = ""

        for entry in activity_entries:

            activity_log += (
                f"[{entry['time']}] "
                f"[{entry['level']}] "
                f"{entry['message']}\n"
            )

        text = f"""
==================================================
SECURE DEVICE AGENT
==================================================

[ DEVICE INFORMATION ]

Device: {device_info["device_name"]}
Operating System: {device_info["operating_system"]}
OS Version: {device_info["os_version"]}
Architecture: {device_info["architecture"]}
CPU: {device_info["cpu_model"]}
CPU Cores: {device_info["cpu_cores"]}
RAM Total: {device_info["ram_total_mb"]} MB
IP Address: {device_info["ip_address"]}

Agent: {AGENT_NAME}
Agent Version: {AGENT_VERSION}


[ LIVE SYSTEM METRICS ]

CPU Usage: {metrics["cpu_usage_percent"]}%
RAM Usage: {metrics["ram_usage_percent"]}%
Disk Usage: {metrics["disk_usage_percent"]}%
Uptime: {self.format_uptime(metrics["uptime_seconds"])}


[ RESOURCE DETAILS ]

RAM Used: {metrics["ram_used_mb"]} MB
RAM Total: {metrics["ram_total_mb"]} MB

Disk Used: {metrics["disk_used_gb"]} GB
Disk Free: {metrics["disk_free_gb"]} GB
Disk Total: {metrics["disk_total_gb"]} GB


[ AGENT STATUS ]

Server: https://moslemdev.ir
Status: {self.connection_status}
Heartbeat: {self.heartbeat_label.cget("text")}
Connection: {self.connection_label.cget("text")}
Last Check: {self.last_check_label.cget("text")}


[ ACTIVITY LOG ]

{activity_log}
==================================================
END OF REPORT
==================================================
"""

        self.root.clipboard_clear()

        self.root.clipboard_append(
            text
        )

        self.root.update()

    # ---------------------------------------------------------
    # HELPERS
    # ---------------------------------------------------------

    @staticmethod
    def format_uptime(seconds):

        seconds = int(seconds)

        days = seconds // 86400
        seconds %= 86400

        hours = seconds // 3600
        seconds %= 3600

        minutes = seconds // 60
        seconds %= 60

        if days > 0:
            return f"{days}d {hours}h {minutes}m"

        if hours > 0:
            return f"{hours}h {minutes}m"

        if minutes > 0:
            return f"{minutes}m {seconds}s"

        return f"{seconds}s"

    @staticmethod
    def get_current_time():

        import datetime

        return datetime.datetime.now().strftime(
            "%H:%M:%S"
        )


def main():

    root = tk.Tk()

    app = SecureDeviceAgentGUI(
        root
    )

    root.mainloop()


if __name__ == "__main__":
    main()