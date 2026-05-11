(function () {
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

    const updateStatus = (modal, type, message) => {
        const status = modal.querySelector(".js-nfc-status");
        if (!status) {
            return;
        }

        status.className = "modal-pulseira-status js-nfc-status " + type;
        status.textContent = message;
    };

    const resetWriteButton = (modal) => {
        const button = modal.querySelector(".js-nfc-write-button");
        if (!button) {
            return;
        }

        if (!button.dataset.defaultLabel) {
            button.dataset.defaultLabel = button.textContent.trim();
        }

        button.textContent = button.dataset.defaultLabel;
        button.disabled = false;
    };

    const setWriteButtonState = (modal, label, disabled) => {
        const button = modal.querySelector(".js-nfc-write-button");
        if (!button) {
            return;
        }

        button.textContent = label;
        button.disabled = disabled;
    };

    const resetNfcModal = (modal) => {
        const status = modal.querySelector(".js-nfc-status");
        if (status) {
            if (!status.dataset.defaultMessage) {
                status.dataset.defaultMessage = status.textContent.trim();
            }

            updateStatus(modal, "info", status.dataset.defaultMessage);
        }

        resetWriteButton(modal);
        delete modal.dataset.nfcBusy;
    };

    const hasWebNfcSupport = () => window.isSecureContext && "NDEFReader" in window;

    const getSupportErrorMessage = () => {
        if (!window.isSecureContext) {
            return "O navegador precisa abrir este site em HTTPS ou localhost para usar Web NFC.";
        }

        return "Este dispositivo ou navegador nao oferece suporte a gravacao NFC pelo site. Use Android com Chrome ou Edge compativel.";
    };

    const getWriteErrorMessage = (error) => {
        switch (error && error.name) {
            case "NotAllowedError":
                return "A permissao de NFC foi negada. Ative o NFC do aparelho e permita o acesso do navegador.";
            case "NotSupportedError":
                return "Este aparelho nao suporta gravacao NFC pelo navegador.";
            case "NotReadableError":
                return "Nao foi possivel acessar a pulseira. Tente aproximar novamente.";
            case "NetworkError":
                return "A pulseira nao pode ser gravada neste momento. Tente novamente.";
            case "AbortError":
                return "A gravacao foi cancelada antes da conclusao.";
            default:
                return "Nao foi possivel gravar a pulseira agora. Tente novamente com o celular desbloqueado e a pulseira encostada no aparelho.";
        }
    };

    const writeFichaIdToTag = async (modal) => {
        if (modal.dataset.nfcBusy === "true") {
            return;
        }

        const idFicha = Number(modal.dataset.idFicha || 0);

        if (!idFicha) {
            updateStatus(modal, "erro", "Nao foi possivel identificar o ID da ficha para gravar na pulseira.");
            setWriteButtonState(modal, "ID indisponivel", true);
            return;
        }

        if (!hasWebNfcSupport()) {
            updateStatus(modal, "erro", getSupportErrorMessage());
            setWriteButtonState(modal, "NFC indisponivel", true);
            return;
        }

        modal.dataset.nfcBusy = "true";
        updateStatus(
            modal,
            "info",
            "Aproxime a pulseira do celular para gravar o ID da ficha " + idFicha + ". Mantenha o aparelho desbloqueado ate concluir."
        );
        setWriteButtonState(modal, "Aguardando pulseira...", true);

        try {
            const ndef = new NDEFReader();
            await ndef.write(String(idFicha));

            updateStatus(
                modal,
                "sucesso",
                "Pulseira gravada com sucesso com o ID da ficha " + idFicha + "."
            );
            setWriteButtonState(modal, "Gravar novamente", false);
        } catch (error) {
            updateStatus(modal, "erro", getWriteErrorMessage(error));
            setWriteButtonState(modal, "Tentar novamente", false);
        } finally {
            modal.dataset.nfcBusy = "false";
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

        const writeTrigger = event.target.closest(".js-nfc-write-button");
        if (writeTrigger) {
            const modal = writeTrigger.closest(".modal-pulseira");
            if (modal) {
                void writeFichaIdToTag(modal);
            }
            return;
        }

        const openTrigger = event.target.closest("[data-modal-target]");
        if (!openTrigger) {
            return;
        }

        const modal = document.getElementById(openTrigger.dataset.modalTarget);
        if (!modal) {
            return;
        }

        openModal(modal);
        resetNfcModal(modal);

        if (openTrigger.dataset.nfcAction === "write") {
            void writeFichaIdToTag(modal);
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") {
            return;
        }

        document.querySelectorAll(".modal-pulseira.aberto").forEach(closeModal);
    });
})();
