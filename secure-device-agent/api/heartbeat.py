from api.client import ApiClient
from config import DEVICE_HEARTBEAT_URL
from core.device import get_device_info
from core.token_manager import load_token


def send_heartbeat():
    token = load_token()

    if not token:
        print("Device token not found.")
        return False

    device_info = get_device_info()

    client = ApiClient(DEVICE_HEARTBEAT_URL)

    headers = {
        "Authorization": f"Bearer {token}"
    }

    data = {
        "ip_address": device_info["ip_address"]
    }

    response = client.post(
        "",
        data=data,
        headers=headers
    )

    if response is None:
        print("Heartbeat request failed.")
        return False

    if client.is_auth_error(response):

        if response.status_code == 401:
            print(
                "Heartbeat authentication failed."
            )

        elif response.status_code == 403:
            print(
                "Heartbeat rejected: device is disabled."
            )

        return False

    try:
        result = response.json()

    except ValueError:
        print("Invalid API response.")
        return False

    if response.status_code != 200:

        print(
            f"Heartbeat failed: {result}"
        )

        return False

    if result.get("result") != "success":

        print(
            f"Heartbeat failed: {result.get('message')}"
        )

        return False

    print(
        "Heartbeat sent successfully."
    )

    return True