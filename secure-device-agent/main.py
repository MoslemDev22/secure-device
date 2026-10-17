from core.device import get_device_info


def main():
    print("Secure Device Agent")
    print("====================")

    device = get_device_info()

    print(f"Device Name: {device['device_name']}")
    print(f"Operating System: {device['operating_system']}")
    print(f"OS Version: {device['os_version']}")
    print(f"Architecture: {device['architecture']}")
    print(f"CPU: {device['cpu_model']}")
    print(f"CPU Cores: {device['cpu_cores']}")
    print(f"RAM Total: {device['ram_total_mb']} MB")
    print(f"IP Address: {device['ip_address']}")


if __name__ == "__main__":
    main()
