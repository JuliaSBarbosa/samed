// === SCRIPT UNIFICADO, OTIMIZADO E SEM BUGS ===
// Tudo em um único DOMContentLoaded

document.addEventListener('DOMContentLoaded', function () {

    // ==========================
    // 1. MODAL (abrir/fechar)
    // ==========================
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

    if (abrirBtn && modal) abrirBtn.addEventListener('click', (e) => { e.preventDefault(); openModal(); });
    if (fecharBtn) fecharBtn.addEventListener('click', (e) => { e.preventDefault(); closeModal(); });
    if (overlay) overlay.addEventListener('click', () => closeModal());
    if (cancelarLink) cancelarLink.addEventListener('click', (e) => { e.preventDefault(); closeModal(); });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeModal();
    });


    // ==========================
    // 2. MULTI-STEP
    // ==========================
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
        console.log('[multi-step] init: found', formSteps.length, 'steps');

        function validateCurrentStep() {
            const current = formSteps[activeStep];
            if (!current) return true;
            const required = Array.from(current.querySelectorAll('[required]'));

            for (const field of required) {
                if (field.disabled) continue;

                // Handle radio groups
                if (field.type === 'radio') {
                    const group = form.querySelectorAll('input[name="' + field.name + '"]');
                    let someChecked = false;
                    group.forEach(g => { if (g.checked) someChecked = true; });
                    if (!someChecked) {
                        alert('Selecione: ' + (field.previousElementSibling?.textContent || field.name));
                        group[0].focus();
                        return false;
                    }
                    continue;
                }

                // Handle checkboxes (single required checkbox)
                if (field.type === 'checkbox') {
                    if (!field.checked) {
                        alert('Marque o campo obrigatório: ' + (field.previousElementSibling?.textContent || field.name));
                        field.focus();
                        return false;
                    }
                    continue;
                }

                // Default text/select/textarea validation
                if (!String(field.value || '').trim()) {
                    alert('Preencha o campo obrigatório: ' + (field.previousElementSibling?.textContent || field.name));
                    field.focus();
                    return false;
                }
            }
            return true;
        }

        function updateStepDisplay() {
            console.log('[multi-step] updateStepDisplay activeStep=', activeStep);
            formSteps.forEach((s, i) => s.classList.toggle('active', i === activeStep));
            progressSteps.forEach((s, i) => {
                s.classList.toggle('active', i === activeStep);
                s.classList.toggle('completed', i < activeStep);
            });

            const prevButtons = container.querySelectorAll(prevSelector);
            const nextButtons = container.querySelectorAll(nextSelector);

            prevButtons.forEach(btn => btn.style.display = activeStep === 0 ? 'none' : '');
            nextButtons.forEach(btn => btn.style.display = activeStep === formSteps.length - 1 ? 'none' : '');
            if (submitButton) submitButton.style.display = activeStep === formSteps.length - 1 ? '' : 'none';
        }

        container.addEventListener('click', function (e) {
            const nextBtn = e.target.closest(nextSelector);
            if (nextBtn) {
                e.preventDefault();
                console.log('[multi-step] click next (before validation) activeStep=', activeStep);
                if (!validateCurrentStep()) return;
                if (activeStep < formSteps.length - 1) {
                    activeStep++;
                    console.log('[multi-step] advanced to', activeStep);
                }
                updateStepDisplay();
                return;
            }

            const prevBtn = e.target.closest(prevSelector);
            if (prevBtn) {
                e.preventDefault();
                console.log('[multi-step] click prev (before) activeStep=', activeStep);
                if (activeStep > 0) activeStep--;
                updateStepDisplay();
            }
        });

        updateStepDisplay();
    }

    // Inicializa multi-step
    initMultiStep(document.querySelector('.form-container'));
    if (modal) initMultiStep(modal);


    // ==========================
    // 3. DOENÇAS CRÔNICAS → campo "Outra"
    // ==========================
    function mostrarOutroCampo() {
        const select = document.getElementById('doencas');
        const div = document.getElementById('campoOutraDoenca');
        const input = document.getElementById('outraDoenca');
        if (!select || !div || !input) return;

        if (select.value === 'outra_nao_listada') {
            div.style.display = 'block';
            input.required = true;
        } else {
            div.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }

    const selectDoencas = document.getElementById('doencas');
    if (selectDoencas) {
        selectDoencas.addEventListener('change', mostrarOutroCampo);
        mostrarOutroCampo();
    }


    // ==========================
    // 4. PARENTESCO → campo "Outro"
    // ==========================
    function mostrarOutroParentesco() {
        const select = document.getElementById('parentesco');
        const div = document.getElementById('campoOutroParentesco');
        const input = document.getElementById('outroParentesco');
        if (!select || !div || !input) return;

        if (select.value === 'Outro') {
            div.style.display = 'block';
            input.required = true;
        } else {
            div.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }

    const selectParentesco = document.getElementById('parentesco');
    if (selectParentesco) {
        selectParentesco.addEventListener('change', mostrarOutroParentesco);
        mostrarOutroParentesco();
    }


    // ==========================
    // 5. ALERGIAS → mostrar descrição
    // ==========================
    function mostrarCampoAlergia() {
        const select = document.getElementById('alergias');
        const campo = document.getElementById('campoDescricao');
        if (!select || !campo) return;

        campo.style.display = select.value !== '' ? 'block' : 'none';
    }

    const selectAlergia = document.getElementById('alergias');
    if (selectAlergia) {
        selectAlergia.addEventListener('change', mostrarCampoAlergia);
        mostrarCampoAlergia();
    }


    // ==========================
    // 6. DOENÇA MENTAL → campo "Outra"
    // ==========================
    function mostrarOutraDoencaMental() {
        const select = document.getElementById('doenca_mental');
        const div = document.getElementById('campoOutraDoencaMental');
        const input = document.getElementById('outraDoencaMental');
        if (!select || !div || !input) return;

        if (select.value === 'outra') {
            div.style.display = 'block';
            input.required = true;
        } else {
            div.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }

    const selectDM = document.getElementById('doenca_mental');
    if (selectDM) {
        selectDM.addEventListener('change', mostrarOutraDoencaMental);
        mostrarOutraDoencaMental();
    }


    // ==========================
    // 7. MEDICAÇÃO — campos dinâmicos
    // ==========================
    const medicacoesWrapper = document.getElementById('medicacoes-wrapper');
    const adicionarMedicacaoBtn = document.getElementById('adicionar-medicacao');

    function updateRemoverButtons() {
        if (!medicacoesWrapper) return;
        const items = medicacoesWrapper.querySelectorAll('.medicacao-item');
        items.forEach((it) => {
            const btn = it.querySelector('.remover-medicacao');
            btn.style.display = items.length > 1 ? '' : 'none';
        });
    }

    if (adicionarMedicacaoBtn && medicacoesWrapper) {
        adicionarMedicacaoBtn.addEventListener('click', () => {
            const item = document.createElement('div');
            item.className = 'medicacao-item';
            item.style.marginTop = '8px';
            item.innerHTML = `
                <input type="text" name="medicacao[]" class="medicacao-input" placeholder="Nome do medicamento">
                <button type="button" class="remover-medicacao btn-small remove">Remover</button>
            `;
            medicacoesWrapper.appendChild(item);
            updateRemoverButtons();
        });

        medicacoesWrapper.addEventListener('click', (e) => {
            const rm = e.target.closest('.remover-medicacao');
            if (rm) {
                rm.closest('.medicacao-item').remove();
                updateRemoverButtons();
            }
        });

        updateRemoverButtons();
    }

    // ==========================
    // DISPOSITIVO IMPLANTADO → campo "Outra"
    // ==========================
    const selectDispositivo = document.getElementById("dispositivo");
    if (selectDispositivo) {
        selectDispositivo.addEventListener("change", function () {
            const campoOutro = document.getElementById("campoOutroDispositivo");
            if (this.value === "outro") {
                campoOutro.style.display = "block";
            } else {
                campoOutro.style.display = "none";
            }
        });
    }

    // ==========================
    // PLANO DE SAÚDE → campo "Qual plano?"
    // ==========================
    const selectPlano = document.getElementById("plano_saude");
    if (selectPlano) {
        selectPlano.addEventListener("change", function () {
            const campo = document.getElementById("campoPlano");
            if (this.value === "sim") {
                campo.style.display = "block";
            } else {
                campo.style.display = "none";
            }
        });
    }

});