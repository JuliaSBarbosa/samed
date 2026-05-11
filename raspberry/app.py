import json
import logging
import os
import sys
import time
from pathlib import Path
from typing import Any, Dict

from aws_client import AwsPulseiraClient
from pn532_service import Pn532Service


def load_config() -> Dict[str, Any]:
    config_path = Path(sys.argv[1]) if len(sys.argv) > 1 else Path(__file__).with_name("config.json")
    if not config_path.exists():
        raise FileNotFoundError(
            f"Arquivo de configuração não encontrado em {config_path}. Copie config.example.json para config.json."
        )

    with config_path.open("r", encoding="utf-8") as handle:
        config = json.load(handle)

    config["api_base_url"] = os.getenv("SAMED_API_BASE_URL", config.get("api_base_url", "")).rstrip("/")
    config["device_id"] = os.getenv("SAMED_DEVICE_ID", config.get("device_id", "raspberry-01"))
    config["api_token"] = os.getenv("SAMED_API_TOKEN", config.get("api_token", "samed-pulseira-dev-token"))
    config["nfc_device"] = os.getenv("SAMED_NFC_DEVICE", config.get("nfc_device", "tty:S0:pn532"))
    config["simulate"] = os.getenv("SAMED_SIMULATE", str(config.get("simulate", False))).lower() in {
        "1",
        "true",
        "yes",
    }
    config["poll_interval_seconds"] = int(config.get("poll_interval_seconds", 2))
    config["tag_timeout_seconds"] = int(config.get("tag_timeout_seconds", 25))
    config["request_timeout_seconds"] = int(config.get("request_timeout_seconds", 20))
    return config


def setup_logging() -> None:
    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s [%(levelname)s] %(message)s",
    )


def main() -> None:
    setup_logging()
    config = load_config()

    client = AwsPulseiraClient(
        base_url=config["api_base_url"],
        device_id=config["device_id"],
        api_token=config["api_token"],
        timeout=config["request_timeout_seconds"],
    )
    nfc_service = Pn532Service(
        device=config["nfc_device"],
        tag_timeout_seconds=config["tag_timeout_seconds"],
        simulate=config["simulate"],
    )

    logging.info("Worker de pulseira iniciado para device_id=%s", config["device_id"])

    while True:
        command = None
        try:
            command = client.fetch_next_command()
            if not command:
                time.sleep(config["poll_interval_seconds"])
                continue

            logging.info("Comando recebido: id=%s acao=%s", command.command_id, command.action)

            if command.action == "gravar":
                result = nfc_service.write_tag(command.payload_desejado)
                client.send_result(
                    command_id=command.command_id,
                    status="sucesso",
                    uid_tag=result.get("uid_tag"),
                    payload_ndef=result.get("payload_ndef"),
                    message="Pulseira gravada com sucesso no Raspberry.",
                    tipo_tag=result.get("tipo_tag", "NTAG215"),
                )
                logging.info("Gravação concluída: uid=%s", result.get("uid_tag"))
            elif command.action == "ler":
                result = nfc_service.read_tag()
                client.send_result(
                    command_id=command.command_id,
                    status="sucesso",
                    uid_tag=result.get("uid_tag"),
                    payload_ndef=result.get("payload_ndef"),
                    message="Pulseira lida com sucesso no Raspberry.",
                    tipo_tag=result.get("tipo_tag", "NTAG215"),
                )
                logging.info("Leitura concluída: uid=%s", result.get("uid_tag"))
            else:
                raise RuntimeError(f"Ação não suportada pelo worker: {command.action}")
        except KeyboardInterrupt:
            logging.info("Worker interrompido manualmente.")
            break
        except Exception as exc:  # pragma: no cover - integração/IO
            logging.exception("Falha no loop principal do worker: %s", exc)

            try:
                if command:
                    client.send_result(
                        command_id=command.command_id,
                        status="erro",
                        message=str(exc),
                    )
            except Exception as report_error:
                logging.exception("Falha ao reportar erro do comando para a AWS: %s", report_error)

            time.sleep(config.get("poll_interval_seconds", 2))


if __name__ == "__main__":
    main()
