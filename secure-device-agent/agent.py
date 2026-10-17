import time

from api.register import register_device
from api.heartbeat import send_heartbeat
from api.metrics import send_metrics


HEARTBEAT_INTERVAL = 60
METRICS_INTERVAL = 60

RETRY_INTERVAL = 10


def run_agent():

    print("Secure Device Agent")
    print("===================")

    print("\n[1] Device Registration")

    try:

        registration_success = register_device()

    except Exception as error:

        print(
            f"Registration error: {error}"
        )

        registration_success = False

    if not registration_success:

        print(
            "Registration failed."
        )

        print(
            "Agent will retry registration later."
        )

    else:

        print(
            "Registration check completed."
        )

    print(
        "\nDevice agent started successfully."
    )

    last_heartbeat = 0
    last_metrics = 0

    heartbeat_failed = False
    metrics_failed = False

    while True:

        current_time = time.time()

        # -----------------------------------------------------
        # HEARTBEAT
        # -----------------------------------------------------

        heartbeat_interval = (
            RETRY_INTERVAL
            if heartbeat_failed
            else HEARTBEAT_INTERVAL
        )

        if (
            current_time - last_heartbeat
            >= heartbeat_interval
        ):

            print(
                "\n[Heartbeat]"
            )

            try:

                heartbeat_success = send_heartbeat()

                if heartbeat_success:

                    if heartbeat_failed:

                        print(
                            "Heartbeat connection restored."
                        )

                    else:

                        print(
                            "Heartbeat successful."
                        )

                    heartbeat_failed = False

                else:

                    heartbeat_failed = True

                    print(
                        "Heartbeat failed."
                    )

                    print(
                        f"Retrying in {RETRY_INTERVAL} seconds."
                    )

            except Exception as error:

                heartbeat_failed = True

                print(
                    f"Heartbeat error: {error}"
                )

                print(
                    f"Retrying in {RETRY_INTERVAL} seconds."
                )

            last_heartbeat = current_time

        # -----------------------------------------------------
        # METRICS
        # -----------------------------------------------------

        metrics_interval = (
            RETRY_INTERVAL
            if metrics_failed
            else METRICS_INTERVAL
        )

        if (
            current_time - last_metrics
            >= metrics_interval
        ):

            print(
                "\n[Metrics]"
            )

            try:

                metrics_success = send_metrics()

                if metrics_success:

                    if metrics_failed:

                        print(
                            "Metrics connection restored."
                        )

                    else:

                        print(
                            "Metrics upload successful."
                        )

                    metrics_failed = False

                else:

                    metrics_failed = True

                    print(
                        "Metrics upload failed."
                    )

                    print(
                        f"Retrying in {RETRY_INTERVAL} seconds."
                    )

            except Exception as error:

                metrics_failed = True

                print(
                    f"Metrics error: {error}"
                )

                print(
                    f"Retrying in {RETRY_INTERVAL} seconds."
                )

            last_metrics = current_time

        time.sleep(1)


if __name__ == "__main__":

    try:

        run_agent()

    except KeyboardInterrupt:

        print(
            "\nAgent stopped by user."
        )