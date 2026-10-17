import requests


class ApiClient:

    def __init__(self, base_url):
        self.base_url = base_url.rstrip("/")

    def post(
        self,
        endpoint,
        data=None,
        headers=None,
        timeout=5
    ):
        url = f"{self.base_url}/{endpoint.lstrip('/')}"

        try:

            response = requests.post(
                url,
                json=data,
                headers=headers,
                timeout=timeout
            )

            return response

        except requests.RequestException as error:

            print(
                f"API Request Error: {error}"
            )

            return None

    @staticmethod
    def is_auth_error(response):
        if response is None:
            return False

        return response.status_code in (401, 403)