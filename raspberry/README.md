# Worker de Pulseira no Raspberry

Este diretório contém o serviço Python que conecta o `Raspberry Pi B+ v1.2` ao `PN532` para ler e gravar `NTAG215`, recebendo comandos da AWS.

## Estrutura

- `app.py`: loop principal do worker
- `aws_client.py`: comunicação com os endpoints PHP na AWS
- `pn532_service.py`: leitura e gravação NFC
- `models.py`: modelo de comando recebido da AWS
- `config.example.json`: modelo de configuração local
- `pulseira-worker.service`: exemplo de serviço `systemd`

## Fluxo

1. O worker consulta `api/pulseira/worker_fetch.php`.
2. Quando receber um comando:
   - `gravar`: espera a aproximação da `NTAG215` e grava a URL da ficha
   - `ler`: espera a aproximação da tag e devolve `uid_tag` + `payload_ndef`
3. O resultado volta para `api/pulseira/worker_result.php`.

## Setup sugerido no Raspberry

1. Instale o Raspberry Pi OS.
2. Conecte o PN532 e habilite a interface escolhida (`SPI` ou `UART`).
3. Copie esta pasta para o Raspberry.
4. Crie o ambiente virtual:

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

5. Copie `config.example.json` para `config.json` e ajuste:

- `api_base_url`
- `device_id`
- `api_token`
- `nfc_device`
- `simulate`

6. Rode localmente:

```bash
python3 app.py config.json
```

7. Se estiver tudo certo, instale o serviço:

```bash
sudo cp pulseira-worker.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable pulseira-worker
sudo systemctl start pulseira-worker
```

## Observações

- `simulate: true` permite testar a fila AWS sem o hardware conectado.
- Para hardware real, ajuste `nfc_device` conforme o backend do `nfcpy` suportado no seu PN532.
- Se a tag não estiver formatada para NDEF, a gravação pode falhar. Para o MVP, prefira tags `NTAG215` já formatadas ou previamente preparadas.
