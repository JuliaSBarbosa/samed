document.addEventListener('DOMContentLoaded', function () {
    // ===== Modal open/close (se existir) =====
    const abrirBtn = document.getElementById('abrir-form');
    const modal = document.getElementById('modal-dependente');
    const fecharBtn = document.getElementById('fechar-modal');
    const overlay = document.getElementById('modal-overlay');
    const cancelarLink = document.getElementById('cancelar-modal');

    function openModal() {
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        const primeiro = modal.querySelector('input, select, textarea, button');
        if (primeiro) primeiro.focus();
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (abrirBtn) abrirBtn.focus();
    }

    if (abrirBtn && modal) {
        abrirBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openModal();
        });
    }

    if (fecharBtn) fecharBtn.addEventListener('click', function (e) {
        e.preventDefault();
        closeModal();
    });

    if (overlay) overlay.addEventListener('click', function () {
        closeModal();
    });

    if (cancelarLink) cancelarLink.addEventListener('click', function (e) {
        // se o cancelar estiver em modal, prevenir e fechar; se for link de página, ele já aponta para dependentes.php
        e.preventDefault();
        closeModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // ===== Multi-step initializer (reutilizável) =====
    function initMultiStep(container) {
        if (!container) return;
        const form = container.querySelector('form');
        const formSteps = Array.from(container.querySelectorAll('.form-step'));
        const nextSelector = '.btn-next, .btn-proximo';
        const prevSelector = '.btn-prev, .btn-anterior';
        const submitButton = container.querySelector('.submit-btn');
        const progressSteps = Array.from(container.querySelectorAll('.step'));

        if (!form || formSteps.length === 0) return;

        let activeStep = 0;

        function validateCurrentStep() {
            const current = formSteps[activeStep];
            if (!current) return true;
            const requiredFields = Array.from(current.querySelectorAll('[required]'));
            for (const field of requiredFields) {
                if (field.disabled) continue;
                const v = (field.value || '').trim();
                if (!v) {
                    field.focus();
                    alert('Por favor, preencha o campo obrigatório: ' + (field.previousElementSibling ? field.previousElementSibling.textContent : field.name));
                    return false;
                }
            }
            return true;
        }

        function updateStepDisplay() {
            formSteps.forEach((stepEl, idx) => {
                stepEl.classList.toggle('active', idx === activeStep);
            });

            progressSteps.forEach((p, idx) => {
                p.classList.toggle('active', idx === activeStep);
                p.classList.toggle('completed', idx < activeStep);
            });

            // atualizar visibilidade consultando os seletores (mais robusto)
            const prevButtons = Array.from(container.querySelectorAll(prevSelector));
            const nextButtons = Array.from(container.querySelectorAll(nextSelector));

            prevButtons.forEach((btn) => {
                btn.style.display = activeStep === 0 ? 'none' : '';
            });

            nextButtons.forEach((btn) => {
                btn.style.display = activeStep === formSteps.length - 1 ? 'none' : '';
            });

            if (submitButton) submitButton.style.display = activeStep === formSteps.length - 1 ? '' : 'none';
        }

        // Delegação: capturar cliques nos botões mesmo que a NodeList mude
        container.addEventListener('click', function (e) {
            const nextBtn = e.target.closest(nextSelector);
            if (nextBtn) {
                e.preventDefault();
                if (!validateCurrentStep()) return;
                if (activeStep < formSteps.length - 1) {
                    activeStep++;
                    updateStepDisplay();
                }
                return;
            }

            const prevBtn = e.target.closest(prevSelector);
            if (prevBtn) {
                e.preventDefault();
                if (activeStep > 0) {
                    activeStep--;
                    updateStepDisplay();
                }
                return;
            }
        });

        // inicializar visual
        updateStepDisplay();
    }

    // Inicializa para formulário embutido na página
    const pageFormContainer = document.querySelector('.form-container');
    if (pageFormContainer) initMultiStep(pageFormContainer);

    // Inicializa para modal (se existir)
    if (modal) {
        initMultiStep(modal);
    }

});
