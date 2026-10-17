import platform
import socket

import psutil


def get_cpu_model():
    try:
        with open("/proc/cpuinfo", "r") as file:
            for line in file:
                if line.startswith("model name"):
                    return line.split(":", 1)[1].strip()

    except Exception:
        pass

    return "Unknown CPU"


def get_local_ip():
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)

        sock.connect(("8.8.8.8", 80))

        ip_address = sock.getsockname()[0]

        sock.close()

        return ip_address

    except Exception:
        return None


def get_device_info():
    memory = psutil.virtual_memory()

    return {
        "device_name": socket.gethostname(),
        "operating_system": "Ubuntu",
        "os_version": platform.release(),
        "architecture": platform.machine(),
        "cpu_model": get_cpu_model(),
        "cpu_cores": psutil.cpu_count(logical=True),
        "ram_total_mb": memory.total // (1024 * 1024),
        "ip_address": get_local_ip()
    }
