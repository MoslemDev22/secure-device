from api.client import ApiClient
from config import DEVICE_METRICS_URL
from core.metrics import get_metrics
from core.token_manager import load_token


def send_metrics():
    token = load_token()

    if not token:
        print("Device token not found.")
        return False

    metrics = get_metrics()

    client = ApiClient(DEVICE_METRICS_URL)

    headers = {
        "Authorization": f"Bearer {token}"
    }

    response = client.post(
        "",
        data=metrics,
        headers=headers
    )

    if response is None:
        print("Metrics request failed.")
        return False

    if client.is_auth_error(response):

        if response.status_code == 401:
            print(
                "Metrics authentication failed."
            )

        elif response.status_code == 403:
            print(
                "Metrics rejected: device is disabled."
            )

        return False

    try:
        result = response.json()

    except ValueError:
        print("Invalid API response.")
        return False

    if response.status_code != 200:

        print(
            f"Metrics failed: {result}"
        )

        return False

    if result.get("result") != "success":

        print(
            f"Metrics failed: {result.get('message')}"
        )

        return False

    print(
        "Metrics sent successfully."
    )

    return True