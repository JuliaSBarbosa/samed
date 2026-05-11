(function () {
    const endpoints = {
        create: "api/pulseira/criar_comando.php",
        status: "api/pulseira/status_comando.php",
    };

    const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

    const openModal = (modal) => {
        modal.classList.add("aberto");
        modal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
    };

    const closeModal = (modal) => {
        modal.classList.remove("aberto");
        modal.setAttribute("aria-hidden", "true");

        if (!document.querySelector(".modal-pulseira.aberto")) {
            document.body.style.overflow = "";
        }
    };

    const getStatusElement = (container) =>
        container.querySelector(".js-nfc-status, .js-scanner-status");

    const getDetailsElement = (container) =>
        container.querySelector(".js-scanner-status-detalhes");

    const getStatusClass = (type) => {
        if (type === "sucesso") {
            return "sucesso";
        }

        if (type === "erro") {
            return "erro";
        }

        return "info";
    };

    const escapeHtml = (value) =>
        String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

    const updateStatus = (container, type, message) => {
        const status = getStatusElement(container);
        if (!status) {
            return;
        }

        const statusClass = getStatusClass(type);
        if (status.classList.contains("modal-pulseira-status")) {
            status.className = "modal-pulseira-status js-nfc-status " + statusClass;
        } else {
            status.className = "scanner-status js-scanner-status " + statusClass;
        }

        status.textContent = message;
    };

    const renderResultDetails = (container, result) => {
        const details = getDetailsElement(container);
        if (!details) {
            return;
        }

        if (!result || typeof result !== "object") {
            details.innerHTML = "";
            return;
        }

        const lines = [];

        if (result.uid_tag) {
            lines.push("<strong>UID:</strong> " + escapeHtml(result.uid_tag));
        }

        if (result.payload_ndef) {
            lines.push("<strong>Payload:</strong> " + escapeHtml(result.payload_ndef));
        }

        if (result.perfil_medico_id) {
            lines.push("<strong>ID da ficha:</strong> #" + escapeHtml(result.perfil_medico_id));
        }

        if (Array.isArray(result.uids_desvinculados) && result.uids_desvinculados.length > 0) {
            lines.push("<strong>UIDs liberados:</strong> " + escapeHtml(result.uids_desvinculados.join(", ")));
        }

        details.innerHTML = lines.join("<br>");
    };

    const setButtonsBusy = (container, busy, activeButton, busyLabel) => {
        const buttons = container.querySelectorAll(".js-pulseira-command, .js-pulseira-read-trigger");
        buttons.forEach((button) => {
            if (!button.dataset.defaultLabel) {
                button.dataset.defaultLabel = button.textContent.trim();
            }
            if (!button.dataset.defaultHtml) {
                button.dataset.defaultHtml = button.innerHTML;
            }

            button.disabled = busy;
            if (busy && button === activeButton && busyLabel) {
                button.textContent = busyLabel;
            } else {
                button.innerHTML = button.dataset.defaultHtml;
            }
        });
    };

    const resetContainer = (container) => {
        const status = getStatusElement(container);
        if (status) {
            if (!status.dataset.defaultMessage) {
                status.dataset.defaultMessage = status.textContent.trim();
            }

            updateStatus(container, "info", status.dataset.defaultMessage);
        }

        renderResultDetails(container, null);
        setButtonsBusy(container, false);
        delete container.dataset.busy;
    };

    const requestJson = async (url, options = {}) => {
        const response = await fetch(url, options);
        const contentType = response.headers.get("content-type") || "";
        const payload = contentType.includes("application/json")
            ? await response.json()
            : {};

        if (!response.ok || payload.success === false) {
            throw new Error(payload.message || "Falha ao comunicar com o servidor de pulseiras.");
        }

        return payload;
    };

    const createCommand = (payload) =>
        requestJson(endpoints.create, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(payload),
        });

    const fetchCommandStatus = (commandId) =>
        requestJson(endpoints.status + "?command_id=" + encodeURIComponent(commandId));

    const pollCommandUntilDone = async (commandId, onProgress) => {
        for (let attempt = 0; attempt < 60; attempt += 1) {
            const response = await fetchCommandStatus(commandId);
            const command = response.command || {};

            if (onProgress) {
                onProgress(command);
            }

            if (["sucesso", "erro", "expirado"].includes(command.status)) {
                return command;
            }

            await wait(1500);
        }

        throw new Error("Tempo limite aguardando resposta do Raspberry.");
    };

    const runModalCommand = async (modal, action, button) => {
        if (modal.dataset.busy === "true") {
            return;
        }

        const perfilMedicoId = Number(modal.dataset.perfilMedicoId || 0);
        if (!perfilMedicoId) {
            updateStatus(modal, "erro", "Não foi possível identificar a ficha para esta operação.");
            return;
        }

        modal.dataset.busy = "true";
        setButtonsBusy(
            modal,
            true,
            button,
            action === "gravar" ? "Enviando comando..." : "Processando..."
        );

        try {
            const created = await createCommand({
                acao: action,
                perfil_medico_id: perfilMedicoId,
            });

            updateStatus(modal, "info", created.message || "Comando enviado ao Raspberry.");

            if (created.status === "sucesso") {
                renderResultDetails(modal, created.result || {});
                updateStatus(modal, "sucesso", created.message || "Operação concluída com sucesso.");
                return;
            }

            const finalCommand = await pollCommandUntilDone(created.command_id, (command) => {
                updateStatus(modal, command.status, command.message || "Aguardando o Raspberry processar a pulseira...");
            });

            renderResultDetails(modal, finalCommand.result || {});
            updateStatus(
                modal,
                finalCommand.status,
                finalCommand.message ||
                    (finalCommand.status === "sucesso"
                        ? "Operação na pulseira concluída com sucesso."
                        : "Falha na operação da pulseira.")
            );
        } catch (error) {
            updateStatus(modal, "erro", error.message);
        } finally {
            modal.dataset.busy = "false";
            setButtonsBusy(modal, false);
        }
    };

    const runScannerRead = async (scannerContainer, button) => {
        if (scannerContainer.dataset.busy === "true") {
            return;
        }

        scannerContainer.dataset.busy = "true";
        setButtonsBusy(scannerContainer, true, button, "Aguardando leitura...");
        renderResultDetails(scannerContainer, null);

        try {
            const created = await createCommand({ acao: "ler" });
            updateStatus(scannerContainer, "info", created.message || "Comando de leitura enviado ao Raspberry.");

            const finalCommand =
                created.status === "sucesso"
                    ? { status: "sucesso", message: created.message, result: created.result || {} }
                    : await pollCommandUntilDone(created.command_id, (command) => {
                          updateStatus(
                              scannerContainer,
                              command.status,
                              command.message || "Raspberry aguardando aproximação da pulseira..."
                          );
                      });

            renderResultDetails(scannerContainer, finalCommand.result || {});
            updateStatus(
                scannerContainer,
                finalCommand.status,
                finalCommand.message ||
                    (finalCommand.status === "sucesso"
                        ? "Leitura da pulseira concluída."
                        : "Falha ao consultar a pulseira.")
            );

            if (
                finalCommand.status === "sucesso" &&
                finalCommand.result &&
                finalCommand.result.redirect_url
            ) {
                updateStatus(scannerContainer, "sucesso", "Pulseira localizada. Abrindo a ficha...");
                window.setTimeout(() => {
                    window.location.href = finalCommand.result.redirect_url;
                }, 900);
            }
        } catch (error) {
            updateStatus(scannerContainer, "erro", error.message);
        } finally {
            scannerContainer.dataset.busy = "false";
            setButtonsBusy(scannerContainer, false);
        }
    };

    document.addEventListener("click", (event) => {
        const closeTrigger = event.target.closest("[data-modal-close]");
        if (closeTrigger) {
            const modal = closeTrigger.closest(".modal-pulseira");
            if (modal) {
                closeModal(modal);
            }
            return;
        }

        const openTrigger = event.target.closest("[data-modal-target]");
        if (openTrigger) {
            const modal = document.getElementById(openTrigger.dataset.modalTarget);
            if (modal) {
                openModal(modal);
                resetContainer(modal);
            }
            return;
        }

        const commandTrigger = event.target.closest(".js-pulseira-command");
        if (commandTrigger) {
            const modal = commandTrigger.closest(".modal-pulseira");
            if (modal) {
                void runModalCommand(modal, commandTrigger.dataset.acao || modal.dataset.acao || "", commandTrigger);
            }
            return;
        }

        const scannerTrigger = event.target.closest(".js-pulseira-read-trigger");
        if (scannerTrigger) {
            const scannerContainer = scannerTrigger.closest("[data-pulseira-scanner]");
            if (scannerContainer) {
                void runScannerRead(scannerContainer, scannerTrigger);
            }
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") {
            return;
        }

        document.querySelectorAll(".modal-pulseira.aberto").forEach(closeModal);
    });

    document.querySelectorAll(".modal-pulseira, [data-pulseira-scanner]").forEach(resetContainer);
})();
