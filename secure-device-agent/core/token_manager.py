from pathlib import Path


TOKEN_FILE = Path.home() / ".secure_device_agent_token"


def save_token(token):
    TOKEN_FILE.write_text(
        token,
        encoding="utf-8"
    )

    TOKEN_FILE.chmod(0o600)


def load_token():
    if not TOKEN_FILE.exists():
        return None

    return TOKEN_FILE.read_text(
        encoding="utf-8"
    ).strip()


def delete_token():
    if TOKEN_FILE.exists():
        TOKEN_FILE.unlink()
