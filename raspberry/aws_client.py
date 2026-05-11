from typing import Any, Dict, Optional

import requests

from models import PulseiraCommand


class AwsPulseiraClient:
    def __init__(self, base_url: str, device_id: str, api_token: str, timeout: int = 20) -> None:
        self.base_url = base_url.rstrip("/")
        self.device_id = device_id
        self.timeout = timeout
        self.session = requests.Session()
        self.session.headers.update(
            {
                "X-Pulseira-Token": api_token,
                "X-Pulseira-Device": device_id,
                "Accept": "application/json",
            }
        )

    def _url(self, path: str) -> str:
        return f"{self.base_url}/{path.lstrip('/')}"

    def fetch_next_command(self) -> Optional[PulseiraCommand]:
        response = self.session.get(
            self._url("api/pulseira/worker_fetch.php"),
            params={"device_id": self.device_id},
            timeout=self.timeout,
        )
        response.raise_for_status()
        payload = response.json()

        if not payload.get("success"):
            raise RuntimeError(payload.get("message", "Falha ao buscar comando na AWS."))

        command = payload.get("command")
        if not command:
            return None

        return PulseiraCommand.from_api(command)

    def send_result(
        self,
        command_id: int,
        status: str,
        uid_tag: Optional[str] = None,
        payload_ndef: Optional[str] = None,
        message: Optional[str] = None,
        tipo_tag: str = "NTAG215",
    ) -> Dict[str, Any]:
        body = {
            "command_id": command_id,
            "status": status,
            "uid_tag": uid_tag,
            "payload_ndef": payload_ndef,
            "message": message,
            "tipo_tag": tipo_tag,
        }

        response = self.session.post(
            self._url("api/pulseira/worker_result.php"),
            params={"device_id": self.device_id},
            json=body,
            timeout=self.timeout,
        )
        response.raise_for_status()
        payload = response.json()

        if not payload.get("success"):
            raise RuntimeError(payload.get("message", "Falha ao enviar resultado para a AWS."))

        return payload
