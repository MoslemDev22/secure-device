from datetime import datetime


class ActivityLogger:

    def __init__(self, max_entries=100):
        self.max_entries = max_entries
        self.entries = []

    def add(self, message, level="INFO"):
        timestamp = datetime.now().strftime("%H:%M:%S")

        entry = {
            "time": timestamp,
            "level": level,
            "message": message
        }

        self.entries.append(entry)

        if len(self.entries) > self.max_entries:
            self.entries.pop(0)

    def get_entries(self):
        return self.entries.copy()

    def clear(self):
        self.entries.clear()