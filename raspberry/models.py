from dataclasses import dataclass, field
from typing import Any, Dict, Optional


@dataclass
class PulseiraCommand:
    command_id: int
    action: str
    device_id: str
    perfil_medico_id: Optional[int] = None
    payload_desejado: Dict[str, Any] = field(default_factory=dict)

    @classmethod
    def from_api(cls, payload: Dict[str, Any]) -> "PulseiraCommand":
        return cls(
            command_id=int(payload["id"]),
            action=str(payload["acao"]),
            device_id=str(payload["device_id"]),
            perfil_medico_id=payload.get("perfil_medico_id"),
            payload_desejado=payload.get("payload_desejado") or {},
        )
