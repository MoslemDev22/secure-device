import time

import psutil


def get_cpu_usage():
    return psutil.cpu_percent(interval=1)


def get_memory_usage():
    memory = psutil.virtual_memory()

    return {
        "ram_usage_percent": memory.percent,
        "ram_used_mb": memory.used // (1024 * 1024),
        "ram_total_mb": memory.total // (1024 * 1024)
    }


def get_disk_usage():
    disk = psutil.disk_usage("/")

    return {
        "disk_usage_percent": disk.percent,
        "disk_used_gb": round(
            disk.used / (1024 ** 3),
            2
        ),
        "disk_total_gb": round(
            disk.total / (1024 ** 3),
            2
        ),
        "disk_free_gb": round(
            disk.free / (1024 ** 3),
            2
        )
    }


def get_uptime():
    boot_time = psutil.boot_time()
    current_time = time.time()

    return int(current_time - boot_time)


def get_metrics():
    memory = get_memory_usage()
    disk = get_disk_usage()

    return {
        "cpu_usage_percent": get_cpu_usage(),
        "ram_usage_percent": memory["ram_usage_percent"],
        "ram_used_mb": memory["ram_used_mb"],
        "ram_total_mb": memory["ram_total_mb"],
        "disk_usage_percent": disk["disk_usage_percent"],
        "disk_used_gb": disk["disk_used_gb"],
        "disk_total_gb": disk["disk_total_gb"],
        "disk_free_gb": disk["disk_free_gb"],
        "uptime_seconds": get_uptime()
    }
