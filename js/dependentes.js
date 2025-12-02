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

        // Função para validar um campo individual e atualizar visualmente
        function validateField(field) {
            if (field.disabled) return true;
            
            let isValid = true;
            
            // Handle radio groups
            if (field.type === 'radio') {
                const group = form.querySelectorAll('input[name="' + field.name + '"]');
                let someChecked = false;
                group.forEach(g => { if (g.checked) someChecked = true; });
                if (!someChecked) {
                    isValid = false;
                    group.forEach(g => g.closest('.tipo-opcoes')?.classList.add('campo-invalido'));
                    group.forEach(g => g.closest('.tipo-opcoes')?.classList.remove('campo-valido'));
                } else {
                    group.forEach(g => g.closest('.tipo-opcoes')?.classList.remove('campo-invalido'));
                    group.forEach(g => g.closest('.tipo-opcoes')?.classList.add('campo-valido'));
                }
                return isValid;
            }

            // Handle checkboxes
            if (field.type === 'checkbox') {
                if (!field.checked) {
                    isValid = false;
                    field.classList.add('campo-invalido');
                    field.classList.remove('campo-valido');
                } else {
                    field.classList.remove('campo-invalido');
                    field.classList.add('campo-valido');
                }
                return isValid;
            }

            // Validação de email
            if (field.type === 'email') {
                const valor = field.value.trim();
                if (valor.length === 0) {
                    isValid = false;
                } else {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    isValid = emailRegex.test(valor);
                }
            }
            // Validação de data
            else if (field.type === 'date') {
                const valor = field.value;
                if (!valor) {
                    isValid = false;
                } else {
                    const data = new Date(valor);
                    const hoje = new Date();
                    isValid = data <= hoje;
                }
            }
            // Validação de select
            else if (field.tagName === 'SELECT') {
                isValid = field.value !== '' && field.value !== null;
            }
            // Default text/textarea validation
            else {
                isValid = String(field.value || '').trim().length > 0;
            }
            
            // Atualizar classes visuais
            if (isValid) {
                field.classList.remove('campo-invalido');
                field.classList.add('campo-valido');
                field.setCustomValidity('');
            } else {
                field.classList.remove('campo-valido');
                field.classList.add('campo-invalido');
            }
            
            return isValid;
        }

        function validateCurrentStep() {
            const current = formSteps[activeStep];
            if (!current) return true;
            
            // Remover mensagens de erro anteriores
            current.querySelectorAll('.campo-erro').forEach(el => el.remove());
            current.querySelectorAll('.campo-invalido').forEach(el => el.classList.remove('campo-invalido'));
            
            const required = Array.from(current.querySelectorAll('[required]'));
            let isValid = true;
            const erros = [];

            for (const field of required) {
                if (field.disabled) continue;
                
                const label = field.closest('label') || 
                             (field.previousElementSibling?.tagName === 'LABEL' ? field.previousElementSibling : null) ||
                             current.querySelector(`label[for="${field.id}"]`);
                const fieldName = label ? label.textContent.trim().replace('*', '').trim() : field.name;

                // Handle radio groups
                if (field.type === 'radio') {
                    const group = form.querySelectorAll('input[name="' + field.name + '"]');
                    let someChecked = false;
                    group.forEach(g => { if (g.checked) someChecked = true; });
                    if (!someChecked) {
                        isValid = false;
                        erros.push(fieldName);
                        field.closest('.tipo-opcoes')?.classList.add('campo-invalido');
                        if (erros.length === 1) group[0].focus();
                    }
                    continue;
                }

                // Handle checkboxes (single required checkbox)
                if (field.type === 'checkbox') {
                    if (!field.checked) {
                        isValid = false;
                        erros.push(fieldName);
                        field.classList.add('campo-invalido');
                        if (erros.length === 1) field.focus();
                    }
                    continue;
                }

                // Validação de email
                if (field.type === 'email' && field.value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(field.value)) {
                        isValid = false;
                        erros.push(fieldName + ' (formato inválido)');
                        field.classList.add('campo-invalido');
                        if (erros.length === 1) field.focus();
                        continue;
                    }
                }

                // Validação de data
                if (field.type === 'date' && field.value) {
                    const data = new Date(field.value);
                    const hoje = new Date();
                    if (data > hoje) {
                        isValid = false;
                        erros.push(fieldName + ' (data não pode ser futura)');
                        field.classList.add('campo-invalido');
                        if (erros.length === 1) field.focus();
                        continue;
                    }
                }

                // Default text/select/textarea validation
                if (!String(field.value || '').trim()) {
                    isValid = false;
                    erros.push(fieldName);
                    field.classList.add('campo-invalido');
                    if (erros.length === 1) field.focus();
                }
            }
            
            // Mostrar mensagens de erro
            if (!isValid && erros.length > 0) {
                const erroDiv = document.createElement('div');
                erroDiv.className = 'campo-erro';
                erroDiv.style.cssText = 'background: #fee; color: #c33; padding: 12px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #f44336;';
                erroDiv.innerHTML = '<strong>⚠️ Erros encontrados:</strong><ul style="margin: 8px 0 0 20px;">' + 
                    erros.map(e => '<li>' + e + '</li>').join('') + '</ul>';
                current.insertBefore(erroDiv, current.firstChild);
                
                // Scroll para o erro
                erroDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            
            return isValid;
        }
        
        // Adicionar validação em tempo real para todos os campos obrigatórios
        function addRealTimeValidation() {
            const allRequiredFields = form.querySelectorAll('[required]');
            allRequiredFields.forEach(field => {
                // Evitar duplicar listeners
                if (field.dataset.validationAdded === 'true') return;
                field.dataset.validationAdded = 'true';
                
                // Adicionar listener para input/change
                const eventType = field.tagName === 'SELECT' ? 'change' : 
                                 field.type === 'checkbox' || field.type === 'radio' ? 'change' : 'input';
                
                field.addEventListener(eventType, function() {
                    validateField(field);
                });
            });
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
            
            // Re-inicializar validação em tempo real quando mudar de step
            setTimeout(addRealTimeValidation, 100);
        }
        
        // Inicializar validação em tempo real
        addRealTimeValidation();

        // Função para validar todos os steps
        function validateAllSteps() {
            let firstInvalidStep = -1;
            let firstInvalidField = null;
            
            // Primeiro, remover required de todos os campos em steps ocultos
            formSteps.forEach((step, index) => {
                if (index !== activeStep) {
                    step.querySelectorAll('[required]').forEach(field => {
                        field.removeAttribute('required');
                        field.setAttribute('data-was-required', 'true');
                    });
                }
            });
            
            // Validar todos os steps (agora apenas o step ativo tem campos required)
            for (let i = 0; i < formSteps.length; i++) {
                const step = formSteps[i];
                // Buscar campos required apenas no step ativo, ou validar manualmente nos outros
                const requiredFields = i === activeStep 
                    ? Array.from(step.querySelectorAll('[required]'))
                    : Array.from(step.querySelectorAll('[data-was-required="true"]'));
                
                for (const field of requiredFields) {
                    if (field.disabled) continue;
                    
                    // Verificar se é válido
                    let isValid = true;
                    if (field.type === 'radio') {
                        const group = form.querySelectorAll('input[name="' + field.name + '"]');
                        let someChecked = false;
                        group.forEach(g => { if (g.checked) someChecked = true; });
                        isValid = someChecked;
                    } else if (field.type === 'checkbox') {
                        isValid = field.checked;
                    } else if (field.type === 'email') {
                        const valor = field.value.trim();
                        isValid = valor.length > 0 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);
                    } else if (field.type === 'date') {
                        const valor = field.value;
                        if (valor) {
                            const data = new Date(valor);
                            const hoje = new Date();
                            isValid = data <= hoje;
                        } else {
                            isValid = false;
                        }
                    } else if (field.tagName === 'SELECT') {
                        isValid = field.value !== '' && field.value !== null;
                    } else {
                        isValid = String(field.value || '').trim().length > 0;
                    }
                    
                    if (!isValid) {
                        if (firstInvalidStep === -1) {
                            firstInvalidStep = i;
                            firstInvalidField = field;
                        }
                    }
                }
            }
            
            // Se encontrou erro, restaurar required e mostrar o step
            if (firstInvalidStep !== -1) {
                // Restaurar todos os atributos required
                formSteps.forEach(step => {
                    step.querySelectorAll('[data-was-required="true"]').forEach(field => {
                        field.setAttribute('required', 'required');
                        field.removeAttribute('data-was-required');
                    });
                });
                
                // Ir para o step com erro
                activeStep = firstInvalidStep;
                updateStepDisplay();
                
                // Focar no campo inválido
                if (firstInvalidField) {
                    setTimeout(() => {
                        firstInvalidField.focus();
                        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 100);
                }
                
                return false;
            }
            
            return true;
        }
        
        // Adicionar listener de submit no formulário
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            
            // Validar todos os steps antes de enviar
            if (!validateAllSteps()) {
                return false;
            }
            
            // Se passou na validação, remover required temporariamente de TODOS os campos ocultos e submeter
            // Usar setTimeout para garantir que o DOM foi atualizado antes de submeter
            setTimeout(() => {
                formSteps.forEach((step, index) => {
                    // Remover required de todos os steps exceto o ativo
                    if (index !== activeStep) {
                        step.querySelectorAll('[required]').forEach(field => {
                            field.removeAttribute('required');
                            field.setAttribute('data-was-required', 'true');
                        });
                        // Também remover de campos que já tinham data-was-required
                        step.querySelectorAll('[data-was-required="true"]').forEach(field => {
                            field.removeAttribute('required');
                        });
                    }
                });
                
                // Submeter o formulário
                console.log('[multi-step] Formulário sendo enviado...');
                form.submit();
            }, 50);
            
            return false;
        });
        
        // Garantir que campos ocultos não tenham required antes de submeter
        form.addEventListener('submit', function(e) {
            // Se já foi interceptado pelo listener anterior, não fazer nada
            if (e.defaultPrevented) return;
            
            // Remover required de campos ocultos como fallback
            formSteps.forEach((step, index) => {
                if (index !== activeStep) {
                    step.querySelectorAll('[required]').forEach(field => {
                        field.removeAttribute('required');
                    });
                }
            });
        }, true); // Usar capture phase para executar antes

        container.addEventListener('click', function (e) {
            // Não interceptar cliques no botão submit
            const submitBtn = e.target.closest('.submit-btn');
            if (submitBtn) {
                // O evento submit do form já vai lidar com a validação completa
                // Não precisamos interceptar aqui
                return;
            }
            
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
    // 3. DOENÇAS CRÔNICAS — campos dinâmicos
    // ==========================
    const doencasWrapper = document.getElementById('doencas-wrapper');
    const adicionarDoencaBtn = document.getElementById('adicionar-doenca');

    function mostrarOutroCampoDoenca(selectElement) {
        const doencaItem = selectElement.closest('.doenca-item');
        if (!doencaItem) return;
        
        const campoOutra = doencaItem.querySelector('.campo-outra-doenca');
        const inputOutra = doencaItem.querySelector('.outra-doenca-input');
        
        if (!campoOutra || !inputOutra) return;

        if (selectElement.value === 'outra_nao_listada') {
            campoOutra.style.display = 'block';
            inputOutra.required = true;
        } else {
            campoOutra.style.display = 'none';
            inputOutra.required = false;
            inputOutra.value = '';
        }
    }

    function updateRemoverDoencaButtons() {
        if (!doencasWrapper) return;
        const items = doencasWrapper.querySelectorAll('.doenca-item');
        items.forEach((it) => {
            const btn = it.querySelector('.remover-doenca');
            if (btn) btn.style.display = items.length > 1 ? '' : 'none';
        });
    }

    function updateAdicionarDoencaButton() {
        if (!doencasWrapper || !adicionarDoencaBtn) return;
        const items = doencasWrapper.querySelectorAll('.doenca-item');
        let temSelecao = false;
        
        items.forEach((item) => {
            const select = item.querySelector('.doenca-select');
            if (select && select.value !== '') {
                temSelecao = true;
            }
        });
        
        adicionarDoencaBtn.style.display = temSelecao ? '' : 'none';
    }

    function criarSelectDoencas() {
        return `
            <option value="">Nenhuma</option>

            <optgroup label="Doenças Cardiovasculares">
                <option value="hipertensao">Hipertensão arterial</option>
                <option value="insuficiencia_cardiaca">Insuficiência cardíaca</option>
                <option value="arritmias_cronicas">Arritmias crônicas</option>
                <option value="doenca_arterial_coronariana">Doença arterial coronariana</option>
                <option value="aterosclerose">Aterosclerose</option>
                <option value="doenca_vascular_periferica">Doença vascular periférica</option>
            </optgroup>

            <optgroup label="Doenças Endócrinas e Metabólicas">
                <option value="diabetes_tipo1">Diabetes tipo 1</option>
                <option value="diabetes_tipo2">Diabetes tipo 2</option>
                <option value="hipotireoidismo">Hipotireoidismo</option>
                <option value="hipertireoidismo">Hipertireoidismo</option>
                <option value="obesidade_cronica">Obesidade crônica</option>
                <option value="sindrome_metabolica">Síndrome metabólica</option>
            </optgroup>

            <optgroup label="Doenças Respiratórias Crônicas">
                <option value="asma">Asma</option>
                <option value="dpoc">DPOC (Doença Pulmonar Obstrutiva Crônica)</option>
                <option value="bronquite_cronica">Bronquite crônica</option>
                <option value="enfisema">Enfisema</option>
                <option value="fibrose_pulmonar">Fibrose pulmonar</option>
            </optgroup>

            <optgroup label="Doenças Autoimunes">
                <option value="artrite_reumatoide">Artrite reumatoide</option>
                <option value="lupus">Lúpus (LES)</option>
                <option value="psoriase">Psoríase</option>
                <option value="doenca_celiaca">Doença celíaca</option>
                <option value="tireoidite_hashimoto">Tireoidite de Hashimoto</option>
                <option value="doenca_de_crohn">Doença de Crohn</option>
                <option value="retocolite_ulcerativa">Retocolite ulcerativa</option>
            </optgroup>

            <optgroup label="Doenças Neurológicas">
                <option value="epilepsia">Epilepsia</option>
                <option value="enxaqueca_cronica">Enxaqueca crônica</option>
                <option value="doenca_de_parkinson">Doença de Parkinson</option>
                <option value="esclerose_multipla">Esclerose múltipla</option>
                <option value="neuropatias_perifericas">Neuropatias periféricas</option>
            </optgroup>

            <optgroup label="Doenças Musculoesqueléticas">
                <option value="artrose_osteoartrite">Artrose / Osteoartrite</option>
                <option value="fibromialgia">Fibromialgia</option>
                <option value="lombalgia_cronica">Lombalgia crônica</option>
                <option value="osteoporose">Osteoporose</option>
            </optgroup>

            <optgroup label="Doenças Hepáticas e Renais">
                <option value="hepatite_cronica">Hepatite crônica</option>
                <option value="cirrose">Cirrose</option>
                <option value="esteatose_hepatica_cronica">Esteatose hepática (gordura no fígado) crônica</option>
                <option value="doenca_renal_cronica">Doença renal crônica</option>
                <option value="insuficiencia_renal">Insuficiência renal</option>
            </optgroup>

            <optgroup label="Doenças Gastrointestinais">
                <option value="refluxo_gastroesofagico_cronico">Refluxo gastroesofágico crônico (GERD)</option>
                <option value="sindrome_do_intestino_irritavel">Síndrome do intestino irritável (SII)</option>
                <option value="gastrite_cronica">Gastrite crônica</option>
            </optgroup>

            <optgroup label="Outras Condições Crônicas">
                <option value="cancer">Câncer (em acompanhamento ou histórico)</option>
                <option value="hiv">HIV</option>
                <option value="doencas_hematologicas">Doenças hematológicas</option>
            </optgroup>

            <option value="outra_nao_listada">Outra doença não listada acima</option>
        `;
    }

    if (adicionarDoencaBtn && doencasWrapper) {
        adicionarDoencaBtn.addEventListener('click', () => {
            const item = document.createElement('div');
            item.className = 'doenca-item';
            item.style.marginTop = '8px';
            item.innerHTML = `
                <div class="doenca-select-wrapper">
                    <select name="doencas[]" class="doenca-select">
                        ${criarSelectDoencas()}
                    </select>
                    <button type="button" class="remover-doenca btn-small remove">Remover</button>
                </div>
                <div class="campo-outra-doenca" style="display: none; margin-top: 10px;">
                    <input type="text" name="outraDoenca[]" class="outra-doenca-input" placeholder="Digite o nome da doença">
                </div>
            `;
            doencasWrapper.appendChild(item);
            
            // Adicionar listener ao novo select
            const novoSelect = item.querySelector('.doenca-select');
            if (novoSelect) {
                novoSelect.addEventListener('change', function() {
                    mostrarOutroCampoDoenca(this);
                    updateAdicionarDoencaButton();
                });
            }
            
            updateRemoverDoencaButtons();
            updateAdicionarDoencaButton();
        });

        // Delegar eventos para selects existentes e futuros
        doencasWrapper.addEventListener('change', (e) => {
            if (e.target.classList.contains('doenca-select')) {
                mostrarOutroCampoDoenca(e.target);
                updateAdicionarDoencaButton();
            }
        });

        doencasWrapper.addEventListener('click', (e) => {
            const rm = e.target.closest('.remover-doenca');
            if (rm) {
                rm.closest('.doenca-item').remove();
                updateRemoverDoencaButtons();
                updateAdicionarDoencaButton();
            }
        });

        // Inicializar para o primeiro item
        const primeiroSelect = doencasWrapper.querySelector('.doenca-select');
        if (primeiroSelect) {
            primeiroSelect.addEventListener('change', function() {
                mostrarOutroCampoDoenca(this);
                updateAdicionarDoencaButton();
            });
            mostrarOutroCampoDoenca(primeiroSelect);
        }

        updateRemoverDoencaButtons();
        updateAdicionarDoencaButton();
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
    // 5. ALERGIAS — campos dinâmicos
    // ==========================
    const alergiasWrapper = document.getElementById('alergias-wrapper');
    const adicionarAlergiaBtn = document.getElementById('adicionar-alergia');

    function mostrarCampoAlergia(selectElement) {
        const alergiaItem = selectElement.closest('.alergia-item');
        if (!alergiaItem) return;
        
        const campoDescricao = alergiaItem.querySelector('.campo-descricao-alergia');
        
        if (!campoDescricao) return;

        if (selectElement.value !== '') {
            campoDescricao.style.display = 'block';
        } else {
            campoDescricao.style.display = 'none';
            const inputDescricao = alergiaItem.querySelector('.descricao-alergia-input');
            if (inputDescricao) inputDescricao.value = '';
        }
    }

    function updateRemoverAlergiaButtons() {
        if (!alergiasWrapper) return;
        const items = alergiasWrapper.querySelectorAll('.alergia-item');
        items.forEach((it) => {
            const btn = it.querySelector('.remover-alergia');
            if (btn) btn.style.display = items.length > 1 ? '' : 'none';
        });
    }

    function updateAdicionarAlergiaButton() {
        if (!alergiasWrapper || !adicionarAlergiaBtn) return;
        const items = alergiasWrapper.querySelectorAll('.alergia-item');
        let temSelecao = false;
        
        items.forEach((item) => {
            const select = item.querySelector('.alergia-select');
            if (select && select.value !== '') {
                temSelecao = true;
            }
        });
        
        adicionarAlergiaBtn.style.display = temSelecao ? '' : 'none';
    }

    function criarSelectAlergias() {
        return `
            <option value="">Nenhuma</option>
            <option value="alimentar">Alergia alimentar</option>
            <option value="medicamentos">Alergia medicamentosa</option>
            <option value="respiratoria">Alergia respiratória</option>
            <option value="dermatologica">Alergia dermatológica</option>
            <option value="inseto">Alergia a picada de inseto</option>
            <option value="quimica">Alergia química</option>
            <option value="fisica">Alergia física</option>
            <option value="outra">Outra</option>
        `;
    }

    if (adicionarAlergiaBtn && alergiasWrapper) {
        adicionarAlergiaBtn.addEventListener('click', () => {
            const item = document.createElement('div');
            item.className = 'alergia-item';
            item.style.marginTop = '8px';
            item.innerHTML = `
                <div class="alergia-select-wrapper">
                    <select name="alergias[]" class="alergia-select">
                        ${criarSelectAlergias()}
                    </select>
                    <button type="button" class="remover-alergia btn-small remove">Remover</button>
                </div>
                <div class="campo-descricao-alergia" style="display: none; margin-top: 10px;">
                    <input type="text" name="descricaoAlergia[]" class="descricao-alergia-input" placeholder="Descreva a alergia">
                </div>
            `;
            alergiasWrapper.appendChild(item);
            
            // Adicionar listener ao novo select
            const novoSelect = item.querySelector('.alergia-select');
            if (novoSelect) {
                novoSelect.addEventListener('change', function() {
                    mostrarCampoAlergia(this);
                    updateAdicionarAlergiaButton();
                });
            }
            
            updateRemoverAlergiaButtons();
            updateAdicionarAlergiaButton();
        });

        // Delegar eventos para selects existentes e futuros
        alergiasWrapper.addEventListener('change', (e) => {
            if (e.target.classList.contains('alergia-select')) {
                mostrarCampoAlergia(e.target);
                updateAdicionarAlergiaButton();
            }
        });

        alergiasWrapper.addEventListener('click', (e) => {
            const rm = e.target.closest('.remover-alergia');
            if (rm) {
                rm.closest('.alergia-item').remove();
                updateRemoverAlergiaButtons();
                updateAdicionarAlergiaButton();
            }
        });

        // Inicializar para o primeiro item
        const primeiroSelect = alergiasWrapper.querySelector('.alergia-select');
        if (primeiroSelect) {
            primeiroSelect.addEventListener('change', function() {
                mostrarCampoAlergia(this);
                updateAdicionarAlergiaButton();
            });
            mostrarCampoAlergia(primeiroSelect);
        }

        updateRemoverAlergiaButtons();
        updateAdicionarAlergiaButton();
    }


    // ==========================
    // 6. DOENÇA MENTAL — campos dinâmicos
    // ==========================
    const doencasMentaisWrapper = document.getElementById('doencas-mentais-wrapper');
    const adicionarDoencaMentalBtn = document.getElementById('adicionar-doenca-mental');

    function mostrarOutraDoencaMental(selectElement) {
        const doencaMentalItem = selectElement.closest('.doenca-mental-item');
        if (!doencaMentalItem) return;
        
        const campoOutra = doencaMentalItem.querySelector('.campo-outra-doenca-mental');
        const inputOutra = doencaMentalItem.querySelector('.outra-doenca-mental-input');
        
        if (!campoOutra || !inputOutra) return;

        if (selectElement.value === 'outra') {
            campoOutra.style.display = 'block';
            inputOutra.required = true;
        } else {
            campoOutra.style.display = 'none';
            inputOutra.required = false;
            inputOutra.value = '';
        }
    }

    function updateRemoverDoencaMentalButtons() {
        if (!doencasMentaisWrapper) return;
        const items = doencasMentaisWrapper.querySelectorAll('.doenca-mental-item');
        items.forEach((it) => {
            const btn = it.querySelector('.remover-doenca-mental');
            if (btn) btn.style.display = items.length > 1 ? '' : 'none';
        });
    }

    function updateAdicionarDoencaMentalButton() {
        if (!doencasMentaisWrapper || !adicionarDoencaMentalBtn) return;
        const items = doencasMentaisWrapper.querySelectorAll('.doenca-mental-item');
        let temSelecao = false;
        
        items.forEach((item) => {
            const select = item.querySelector('.doenca-mental-select');
            if (select && select.value !== '') {
                temSelecao = true;
            }
        });
        
        adicionarDoencaMentalBtn.style.display = temSelecao ? '' : 'none';
    }

    function criarSelectDoencasMentais() {
        return `
            <option value="">Nenhuma</option>
            <option value="depressao">Depressão</option>
            <option value="ansiedade">Transtorno de Ansiedade</option>
            <option value="bipolaridade">Transtorno Bipolar</option>
            <option value="esquizofrenia">Esquizofrenia</option>
            <option value="tdah">TDAH (Transtorno do Déficit de Atenção e Hiperatividade)</option>
            <option value="toc">TOC (Transtorno Obsessivo-Compulsivo)</option>
            <option value="transtorno_estresse_pos_traumatico">Transtorno de Estresse Pós-Traumático</option>
            <option value="outra">Outra</option>
        `;
    }

    if (adicionarDoencaMentalBtn && doencasMentaisWrapper) {
        adicionarDoencaMentalBtn.addEventListener('click', () => {
            const item = document.createElement('div');
            item.className = 'doenca-mental-item';
            item.style.marginTop = '8px';
            item.innerHTML = `
                <div class="doenca-mental-select-wrapper">
                    <select name="doenca_mental[]" class="doenca-mental-select">
                        ${criarSelectDoencasMentais()}
                    </select>
                    <button type="button" class="remover-doenca-mental btn-small remove">Remover</button>
                </div>
                <div class="campo-outra-doenca-mental" style="display: none; margin-top: 10px;">
                    <input type="text" name="outraDoencaMental[]" class="outra-doenca-mental-input" placeholder="Digite o nome da doença">
                </div>
            `;
            doencasMentaisWrapper.appendChild(item);
            
            // Adicionar listener ao novo select
            const novoSelect = item.querySelector('.doenca-mental-select');
            if (novoSelect) {
                novoSelect.addEventListener('change', function() {
                    mostrarOutraDoencaMental(this);
                    updateAdicionarDoencaMentalButton();
                });
            }
            
            updateRemoverDoencaMentalButtons();
            updateAdicionarDoencaMentalButton();
        });

        // Delegar eventos para selects existentes e futuros
        doencasMentaisWrapper.addEventListener('change', (e) => {
            if (e.target.classList.contains('doenca-mental-select')) {
                mostrarOutraDoencaMental(e.target);
                updateAdicionarDoencaMentalButton();
            }
        });

        doencasMentaisWrapper.addEventListener('click', (e) => {
            const rm = e.target.closest('.remover-doenca-mental');
            if (rm) {
                rm.closest('.doenca-mental-item').remove();
                updateRemoverDoencaMentalButtons();
                updateAdicionarDoencaMentalButton();
            }
        });

        // Inicializar para o primeiro item
        const primeiroSelect = doencasMentaisWrapper.querySelector('.doenca-mental-select');
        if (primeiroSelect) {
            primeiroSelect.addEventListener('change', function() {
                mostrarOutraDoencaMental(this);
                updateAdicionarDoencaMentalButton();
            });
            mostrarOutraDoencaMental(primeiroSelect);
        }

        updateRemoverDoencaMentalButtons();
        updateAdicionarDoencaMentalButton();
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

    function updateAdicionarMedicacaoButton() {
        if (!medicacoesWrapper || !adicionarMedicacaoBtn) return;
        const items = medicacoesWrapper.querySelectorAll('.medicacao-item');
        let temValor = false;
        
        items.forEach((item) => {
            const input = item.querySelector('.medicacao-input');
            if (input && input.value.trim() !== '') {
                temValor = true;
            }
        });
        
        adicionarMedicacaoBtn.style.display = temValor ? '' : 'none';
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
            
            // Adicionar listener ao novo input
            const novoInput = item.querySelector('.medicacao-input');
            if (novoInput) {
                novoInput.addEventListener('input', function() {
                    updateAdicionarMedicacaoButton();
                });
            }
            
            updateRemoverButtons();
            updateAdicionarMedicacaoButton();
        });

        // Delegar eventos para inputs existentes e futuros
        medicacoesWrapper.addEventListener('input', (e) => {
            if (e.target.classList.contains('medicacao-input')) {
                updateAdicionarMedicacaoButton();
            }
        });

        medicacoesWrapper.addEventListener('click', (e) => {
            const rm = e.target.closest('.remover-medicacao');
            if (rm) {
                rm.closest('.medicacao-item').remove();
                updateRemoverButtons();
                updateAdicionarMedicacaoButton();
            }
        });

        // Inicializar para o primeiro item
        const primeiroInput = medicacoesWrapper.querySelector('.medicacao-input');
        if (primeiroInput) {
            primeiroInput.addEventListener('input', function() {
                updateAdicionarMedicacaoButton();
            });
        }

        updateRemoverButtons();
        updateAdicionarMedicacaoButton();
    }

    // ==========================
    // DISPOSITIVO IMPLANTADO — campos dinâmicos
    // ==========================
    const dispositivosWrapper = document.getElementById('dispositivos-wrapper');
    const adicionarDispositivoBtn = document.getElementById('adicionar-dispositivo');

    function mostrarOutroDispositivo(selectElement) {
        const dispositivoItem = selectElement.closest('.dispositivo-item');
        if (!dispositivoItem) return;
        
        const campoOutro = dispositivoItem.querySelector('.campo-outro-dispositivo');
        const inputOutro = dispositivoItem.querySelector('.outro-dispositivo-input');
        
        if (!campoOutro || !inputOutro) return;

        if (selectElement.value === 'outro') {
            campoOutro.style.display = 'block';
            inputOutro.required = true;
        } else {
            campoOutro.style.display = 'none';
            inputOutro.required = false;
            inputOutro.value = '';
        }
    }

    function updateRemoverDispositivoButtons() {
        if (!dispositivosWrapper) return;
        const items = dispositivosWrapper.querySelectorAll('.dispositivo-item');
        items.forEach((it) => {
            const btn = it.querySelector('.remover-dispositivo');
            if (btn) btn.style.display = items.length > 1 ? '' : 'none';
        });
    }

    function updateAdicionarDispositivoButton() {
        if (!dispositivosWrapper || !adicionarDispositivoBtn) return;
        const items = dispositivosWrapper.querySelectorAll('.dispositivo-item');
        let temSelecao = false;
        
        items.forEach((item) => {
            const select = item.querySelector('.dispositivo-select');
            if (select && select.value !== '') {
                temSelecao = true;
            }
        });
        
        adicionarDispositivoBtn.style.display = temSelecao ? '' : 'none';
    }

    function criarSelectDispositivos() {
        return `
            <option value="">Nenhum</option>
            <option value="marca_passo">Marca-passo</option>
            <option value="stent_cardiaco">Stent cardíaco</option>
            <option value="valvula_cardiaca">Prótese de válvula cardíaca</option>
            <option value="derivacao_cerebral">Derivação ventricular (shunt)</option>
            <option value="implante_cochlear">Implante coclear</option>
            <option value="proteses_ortopedicas">Próteses ortopédicas</option>
            <option value="dispositivo_contraceptivo">Dispositivo contraceptivo</option>
            <option value="outro">Outro</option>
        `;
    }

    if (adicionarDispositivoBtn && dispositivosWrapper) {
        adicionarDispositivoBtn.addEventListener('click', () => {
            const item = document.createElement('div');
            item.className = 'dispositivo-item';
            item.style.marginTop = '8px';
            item.innerHTML = `
                <div class="dispositivo-select-wrapper">
                    <select name="dispositivo[]" class="dispositivo-select">
                        ${criarSelectDispositivos()}
                    </select>
                    <button type="button" class="remover-dispositivo btn-small remove">Remover</button>
                </div>
                <div class="campo-outro-dispositivo" style="display: none; margin-top: 10px;">
                    <input type="text" name="outroDispositivo[]" class="outro-dispositivo-input" placeholder="Digite o nome do dispositivo">
                </div>
            `;
            dispositivosWrapper.appendChild(item);
            
            // Adicionar listener ao novo select
            const novoSelect = item.querySelector('.dispositivo-select');
            if (novoSelect) {
                novoSelect.addEventListener('change', function() {
                    mostrarOutroDispositivo(this);
                    updateAdicionarDispositivoButton();
                });
            }
            
            updateRemoverDispositivoButtons();
            updateAdicionarDispositivoButton();
        });

        // Delegar eventos para selects existentes e futuros
        dispositivosWrapper.addEventListener('change', (e) => {
            if (e.target.classList.contains('dispositivo-select')) {
                mostrarOutroDispositivo(e.target);
                updateAdicionarDispositivoButton();
            }
        });

        dispositivosWrapper.addEventListener('click', (e) => {
            const rm = e.target.closest('.remover-dispositivo');
            if (rm) {
                rm.closest('.dispositivo-item').remove();
                updateRemoverDispositivoButtons();
                updateAdicionarDispositivoButton();
            }
        });

        // Inicializar para o primeiro item
        const primeiroSelect = dispositivosWrapper.querySelector('.dispositivo-select');
        if (primeiroSelect) {
            primeiroSelect.addEventListener('change', function() {
                mostrarOutroDispositivo(this);
                updateAdicionarDispositivoButton();
            });
            mostrarOutroDispositivo(primeiroSelect);
        }

        updateRemoverDispositivoButtons();
        updateAdicionarDispositivoButton();
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
