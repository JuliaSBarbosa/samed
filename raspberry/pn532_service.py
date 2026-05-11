import time
import uuid
from typing import Any, Dict, Optional

try:
    import ndef
    import nfc
except ImportError:  # pragma: no cover - depende do Raspberry
    ndef = None
    nfc = None


class Pn532Service:
    def __init__(self, device: str, tag_timeout_seconds: int = 20, simulate: bool = False) -> None:
        self.device = device
        self.tag_timeout_seconds = tag_timeout_seconds
        self.simulate = simulate

    def _ensure_ready(self) -> None:
        if self.simulate:
            return

        if nfc is None or ndef is None:
            raise RuntimeError(
                "Bibliotecas NFC não encontradas. Instale requirements.txt no Raspberry antes de iniciar o serviço."
            )

    def _extract_payload(self, tag: Any) -> Optional[str]:
        if not getattr(tag, "ndef", None):
            return None

        records = list(tag.ndef.records or [])
        if not records:
            return None

        first = records[0]
        if hasattr(first, "uri"):
            return str(first.uri)

        return str(first)

    def _wait_for_tag(self, on_connect) -> Dict[str, Any]:
        self._ensure_ready()
        started_at = time.monotonic()
        result: Dict[str, Any] = {}

        with nfc.ContactlessFrontend(self.device) as clf:  # pragma: no cover - depende de hardware
            def connected(tag):
                identifier = getattr(tag, "identifier", b"")
                result["uid_tag"] = identifier.hex().upper() if identifier else None
                result["payload_ndef"] = self._extract_payload(tag)
                on_connect(tag, result)
                return False

            connected_ok = clf.connect(
                rdwr={"on-connect": connected},
                terminate=lambda: time.monotonic() - started_at > self.tag_timeout_seconds,
            )

            if connected_ok is False and not result:
                raise TimeoutError("Nenhuma tag foi aproximada do leitor dentro do tempo limite.")

        if not result:
            raise TimeoutError("Nenhuma tag foi aproximada do leitor dentro do tempo limite.")

        return result

    def write_tag(self, payload: Dict[str, Any]) -> Dict[str, Any]:
        url = str(payload.get("url") or "").strip()
        if not url:
            raise ValueError("Payload de gravação inválido: URL vazia.")

        if self.simulate:
            return {
                "uid_tag": f"SIM{uuid.uuid4().hex[:12].upper()}",
                "payload_ndef": url,
                "tipo_tag": payload.get("tag_tipo", "NTAG215"),
            }

        def on_connect(tag, result):  # pragma: no cover - depende de hardware
            if not getattr(tag, "ndef", None):
                raise RuntimeError("A tag aproximada não suporta NDEF ou não está formatada para NDEF.")

            if not tag.ndef.is_writeable:
                raise RuntimeError("A tag aproximada não está gravável.")

            tag.ndef.records = [ndef.UriRecord(url)]
            result["payload_ndef"] = url
            result["tipo_tag"] = payload.get("tag_tipo", "NTAG215")

        return self._wait_for_tag(on_connect)

    def read_tag(self) -> Dict[str, Any]:
        if self.simulate:
            return {
                "uid_tag": f"SIM{uuid.uuid4().hex[:12].upper()}",
                "payload_ndef": "https://example.com/visualizar_paciente.php?id_ficha=1",
                "tipo_tag": "NTAG215",
            }

        def on_connect(tag, result):  # pragma: no cover - depende de hardware
            result["tipo_tag"] = "NTAG215"

        return self._wait_for_tag(on_connect)
