from api.client import ApiClient
from config import DEVICE_REGISTER_URL
from core.device import get_device_info
from core.uuid_manager import get_device_uuid
from core.token_manager import load_token, save_token


AGENT_NAME = "PythonAgent"
AGENT_VERSION = "1.0.0"


def register_device():
    existing_token = load_token()

    if existing_token:
        print("Device is already registered.")
        print("Registration skipped.")
        return True

    device_uuid = get_device_uuid()
    device_info = get_device_info()

    client = ApiClient(DEVICE_REGISTER_URL)

    data = {
        "device_uuid": device_uuid,
        "device_name": device_info["device_name"],
        "operating_system": device_info["operating_system"],
        "os_version": device_info["os_version"],
        "architecture": device_info["architecture"],
        "cpu_model": device_info["cpu_model"],
        "cpu_cores": device_info["cpu_cores"],
        "ram_total_mb": device_info["ram_total_mb"],
        "ip_address": device_info["ip_address"],
        "agent_name": AGENT_NAME,
        "agent_version": AGENT_VERSION
    }

    response = client.post(
        "",
        data=data
    )

    if response is None:
        print("Registration request failed.")
        return False

    try:
        result = response.json()
    except ValueError:
        print("Invalid API response.")
        return False

    if response.status_code != 200:
        print(f"Registration failed: {result}")
        return False

    if result.get("result") != "success":
        print(f"Registration failed: {result.get('message')}")
        return False

    token = result.get("data", {}).get("device_token")

    if not token:
        print("Device token was not returned.")
        return False

    save_token(token)

    print("Device registered successfully.")
    print("Device token saved successfully.")

    return True