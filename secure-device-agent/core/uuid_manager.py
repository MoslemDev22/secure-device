from pathlib import Path
import uuid


UUID_FILE = Path.home() / ".secure_device_agent_uuid"


def generate_uuid():
    return str(uuid.uuid4())


def save_uuid(device_uuid):
    UUID_FILE.write_text(
        device_uuid,
        encoding="utf-8"
    )


def load_uuid():
    if not UUID_FILE.exists():
        return None

    return UUID_FILE.read_text(
        encoding="utf-8"
    ).strip()


def get_device_uuid():
    device_uuid = load_uuid()

    if device_uuid:
        return device_uuid

    device_uuid = generate_uuid()

    save_uuid(device_uuid)

    return device_uuid
