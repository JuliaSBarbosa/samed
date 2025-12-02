/**
 * Validações de formulário para SAMED
 */

/**
 * Valida CPF (apenas verifica se tem 11 dígitos)
 * @param {string} cpf - CPF com ou sem formatação
 * @returns {boolean} - True se válido (11 dígitos), False caso contrário
 */
function validarCPF(cpf) {
    // Remove caracteres não numéricos
    cpf = cpf.replace(/\D/g, '');
    
    // Verifica se tem 11 dígitos
    return cpf.length === 11;
}

/**
 * Aplica máscara de CPF (000.000.000-00)
 * @param {string} valor - Valor sem formatação
 * @returns {string} - Valor formatado
 */
function mascaraCPF(valor) {
    valor = valor.replace(/\D/g, '');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    return valor;
}

/**
 * Valida telefone (apenas números, mínimo 10 dígitos, máximo 11 dígitos)
 * @param {string} telefone - Telefone com ou sem formatação
 * @returns {boolean} - True se válido, False caso contrário
 */
function validarTelefone(telefone) {
    // Remove caracteres não numéricos
    const apenasNumeros = telefone.replace(/\D/g, '');
    
    // Telefone deve ter 10 ou 11 dígitos (fixo ou celular)
    return apenasNumeros.length >= 10 && apenasNumeros.length <= 11;
}

/**
 * Aplica máscara de telefone ((00) 00000-0000 ou (00) 0000-0000)
 * @param {string} valor - Valor sem formatação
 * @returns {string} - Valor formatado
 */
function mascaraTelefone(valor) {
    valor = valor.replace(/\D/g, '');
    
    if (valor.length <= 10) {
        // Telefone fixo: (00) 0000-0000
        valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
        valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
    } else {
        // Telefone celular: (00) 00000-0000
        valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
        valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
    }
    
    return valor;
}

/**
 * Valida email
 * @param {string} email - Email a ser validado
 * @returns {boolean} - True se válido, False caso contrário
 */
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Valida data de nascimento (não pode ser futura e deve ser válida)
 * @param {string} data - Data no formato YYYY-MM-DD
 * @returns {boolean} - True se válida, False caso contrário
 */
function validarDataNascimento(data) {
    if (!data) return false;
    
    const dataObj = new Date(data);
    const hoje = new Date();
    
    // Verifica se a data é válida
    if (isNaN(dataObj.getTime())) {
        return false;
    }
    
    // Verifica se não é futura
    if (dataObj > hoje) {
        return false;
    }
    
    // Verifica se não é muito antiga (mais de 150 anos)
    const idadeMaxima = new Date();
    idadeMaxima.setFullYear(idadeMaxima.getFullYear() - 150);
    if (dataObj < idadeMaxima) {
        return false;
    }
    
    return true;
}

/**
 * Valida tipo sanguíneo
 * @param {string} tipo - Tipo sanguíneo
 * @returns {boolean} - True se válido, False caso contrário
 */
function validarTipoSanguineo(tipo) {
    const tiposValidos = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'RH-NULO'];
    return tiposValidos.includes(tipo);
}

/**
 * Inicializa validações em tempo real para um formulário
 * @param {string} formId - ID do formulário
 */
function inicializarValidacoes(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    // Validação de CPF
    const cpfInput = form.querySelector('input[name="cpf"]');
    if (cpfInput) {
        // Verificar se o CPF é obrigatório (tem atributo required ou é formulário de dependente ou perfil)
        const isRequired = cpfInput.hasAttribute('required') || formId === 'dependenteForm' || formId === 'perfilForm';
        
        // Aplicar máscara enquanto digita
        cpfInput.addEventListener('input', function(e) {
            const valor = e.target.value;
            const valorLimpo = valor.replace(/\D/g, '');
            
            if (valorLimpo.length <= 11) {
                e.target.value = mascaraCPF(valorLimpo);
            } else {
                e.target.value = mascaraCPF(valorLimpo.substring(0, 11));
            }
            
            // Validar em tempo real
            if (valorLimpo.length > 0) {
                if (validarCPF(valorLimpo)) {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido');
                    e.target.classList.add('campo-valido');
                } else {
                    e.target.setCustomValidity('CPF deve ter 11 dígitos.');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                }
            } else {
                // Se for obrigatório e estiver vazio
                if (isRequired) {
                    e.target.setCustomValidity('CPF é obrigatório.');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                } else {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido', 'campo-valido');
                }
            }
        });
        
        // Validar ao perder o foco
        cpfInput.addEventListener('blur', function(e) {
            const valorLimpo = e.target.value.replace(/\D/g, '');
            if (isRequired && valorLimpo.length === 0) {
                e.target.setCustomValidity('CPF é obrigatório.');
                e.target.classList.add('campo-invalido');
            } else if (valorLimpo.length > 0 && valorLimpo.length < 11) {
                e.target.setCustomValidity('CPF deve ter 11 dígitos.');
                e.target.classList.add('campo-invalido');
            } else if (valorLimpo.length > 0 && !validarCPF(valorLimpo)) {
                e.target.setCustomValidity('CPF deve ter 11 dígitos.');
                e.target.classList.add('campo-invalido');
            }
        });
        
        // Validar no submit do formulário
        form.addEventListener('submit', function(e) {
            const valorLimpo = cpfInput.value.replace(/\D/g, '');
            if (isRequired && valorLimpo.length === 0) {
                e.preventDefault();
                cpfInput.setCustomValidity('CPF é obrigatório.');
                cpfInput.classList.add('campo-invalido');
                cpfInput.focus();
                cpfInput.reportValidity();
                return false;
            } else if (valorLimpo.length > 0 && valorLimpo.length < 11) {
                e.preventDefault();
                cpfInput.setCustomValidity('CPF deve ter 11 dígitos.');
                cpfInput.classList.add('campo-invalido');
                cpfInput.focus();
                cpfInput.reportValidity();
                return false;
            } else if (valorLimpo.length > 0 && !validarCPF(valorLimpo)) {
                e.preventDefault();
                cpfInput.setCustomValidity('CPF deve ter 11 dígitos.');
                cpfInput.classList.add('campo-invalido');
                cpfInput.focus();
                cpfInput.reportValidity();
                return false;
            }
        });
    }
    
    // Validação de telefone
    const telefoneInputs = form.querySelectorAll('input[name="telefone"], input[name="contato_telefone"]');
    telefoneInputs.forEach(function(input) {
        const isRequired = input.hasAttribute('required');
        
        // Aplicar máscara enquanto digita
        input.addEventListener('input', function(e) {
            const valor = e.target.value;
            const valorLimpo = valor.replace(/\D/g, '');
            
            if (valorLimpo.length <= 11) {
                e.target.value = mascaraTelefone(valorLimpo);
            } else {
                e.target.value = mascaraTelefone(valorLimpo.substring(0, 11));
            }
            
            // Validar em tempo real
            if (valorLimpo.length > 0) {
                if (validarTelefone(valorLimpo)) {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido');
                    e.target.classList.add('campo-valido');
                } else {
                    e.target.setCustomValidity('Telefone inválido. Use apenas números (10 ou 11 dígitos).');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                }
            } else {
                // Se for obrigatório e estiver vazio
                if (isRequired) {
                    e.target.setCustomValidity('Telefone é obrigatório.');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                } else {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido', 'campo-valido');
                }
            }
        });
        
        // Bloquear entrada de letras
        input.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which);
            if (!/[0-9]/.test(char)) {
                e.preventDefault();
            }
        });
        
        // Validar ao perder o foco
        input.addEventListener('blur', function(e) {
            const valorLimpo = e.target.value.replace(/\D/g, '');
            if (isRequired && valorLimpo.length === 0) {
                e.target.setCustomValidity('Telefone é obrigatório.');
                e.target.classList.add('campo-invalido');
            } else if (valorLimpo.length > 0 && (valorLimpo.length < 10 || valorLimpo.length > 11)) {
                e.target.setCustomValidity('Telefone deve ter 10 ou 11 dígitos.');
                e.target.classList.add('campo-invalido');
            }
        });
    });
    
    // Validação de email
    const emailInput = form.querySelector('input[name="email"]');
    if (emailInput) {
        const isRequired = emailInput.hasAttribute('required');
        
        emailInput.addEventListener('input', function(e) {
            const valor = e.target.value.trim();
            if (valor.length > 0) {
                if (validarEmail(valor)) {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido');
                    e.target.classList.add('campo-valido');
                } else {
                    e.target.setCustomValidity('Email inválido. Use o formato: exemplo@dominio.com');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                }
            } else {
                // Se for obrigatório e estiver vazio
                if (isRequired) {
                    e.target.setCustomValidity('E-mail é obrigatório.');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                } else {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido', 'campo-valido');
                }
            }
        });
        
        emailInput.addEventListener('blur', function(e) {
            const valor = e.target.value.trim();
            if (isRequired && valor.length === 0) {
                e.target.setCustomValidity('E-mail é obrigatório.');
                e.target.classList.add('campo-invalido');
            } else if (valor.length > 0) {
                if (validarEmail(valor)) {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido');
                    e.target.classList.add('campo-valido');
                } else {
                    e.target.setCustomValidity('Email inválido. Use o formato: exemplo@dominio.com');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                }
            }
        });
    }
    
    // Validação de data de nascimento
    const dataInput = form.querySelector('input[name="data_nascimento"]');
    if (dataInput) {
        dataInput.addEventListener('change', function(e) {
            const valor = e.target.value;
            if (valor) {
                if (validarDataNascimento(valor)) {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido');
                    e.target.classList.add('campo-valido');
                } else {
                    e.target.setCustomValidity('Data de nascimento inválida. Não pode ser futura.');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                }
            } else {
                e.target.setCustomValidity('');
                e.target.classList.remove('campo-invalido', 'campo-valido');
            }
        });
    }
    
    // Validação de tipo sanguíneo
    const tipoSanguineoSelect = form.querySelector('select[name="tipo_sanguineo"]');
    if (tipoSanguineoSelect) {
        tipoSanguineoSelect.addEventListener('change', function(e) {
            const valor = e.target.value;
            if (valor && valor !== '') {
                if (validarTipoSanguineo(valor)) {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido');
                    e.target.classList.add('campo-valido');
                } else {
                    e.target.setCustomValidity('Tipo sanguíneo inválido.');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                }
            } else {
                e.target.setCustomValidity('');
                e.target.classList.remove('campo-invalido', 'campo-valido');
            }
        });
    }
    
    // Validação de parentesco (garantir que seja obrigatório)
    const parentescoSelect = form.querySelector('select[name="parentesco"]');
    if (parentescoSelect) {
        parentescoSelect.addEventListener('change', function(e) {
            const valor = e.target.value;
            if (valor && valor.trim() !== '') {
                e.target.setCustomValidity('');
                e.target.classList.remove('campo-invalido');
                e.target.classList.add('campo-valido');
            } else {
                e.target.setCustomValidity('Selecione o parentesco.');
                e.target.classList.remove('campo-valido');
                e.target.classList.add('campo-invalido');
            }
        });
    }
    
    // Validação de autorização de reanimação
    const ressuscitacaoSelect = form.querySelector('select[name="ressuscitacao"]');
    if (ressuscitacaoSelect) {
        ressuscitacaoSelect.addEventListener('change', function(e) {
            const valor = e.target.value;
            if (valor && valor.trim() !== '') {
                e.target.setCustomValidity('');
                e.target.classList.remove('campo-invalido');
                e.target.classList.add('campo-valido');
            } else {
                e.target.setCustomValidity('Selecione uma opção.');
                e.target.classList.remove('campo-valido');
                e.target.classList.add('campo-invalido');
            }
        });
    }
}

// Inicializar validações quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    inicializarValidacoes('perfilForm');
    inicializarValidacoes('dependenteForm');
});


 */

/**
 * Valida CPF (apenas verifica se tem 11 dígitos)
 * @param {string} cpf - CPF com ou sem formatação
 * @returns {boolean} - True se válido (11 dígitos), False caso contrário
 */
function validarCPF(cpf) {
    // Remove caracteres não numéricos
    cpf = cpf.replace(/\D/g, '');
    
    // Verifica se tem 11 dígitos
    return cpf.length === 11;
}

/**
 * Aplica máscara de CPF (000.000.000-00)
 * @param {string} valor - Valor sem formatação
 * @returns {string} - Valor formatado
 */
function mascaraCPF(valor) {
    valor = valor.replace(/\D/g, '');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    return valor;
}

/**
 * Valida telefone (apenas números, mínimo 10 dígitos, máximo 11 dígitos)
 * @param {string} telefone - Telefone com ou sem formatação
 * @returns {boolean} - True se válido, False caso contrário
 */
function validarTelefone(telefone) {
    // Remove caracteres não numéricos
    const apenasNumeros = telefone.replace(/\D/g, '');
    
    // Telefone deve ter 10 ou 11 dígitos (fixo ou celular)
    return apenasNumeros.length >= 10 && apenasNumeros.length <= 11;
}

/**
 * Aplica máscara de telefone ((00) 00000-0000 ou (00) 0000-0000)
 * @param {string} valor - Valor sem formatação
 * @returns {string} - Valor formatado
 */
function mascaraTelefone(valor) {
    valor = valor.replace(/\D/g, '');
    
    if (valor.length <= 10) {
        // Telefone fixo: (00) 0000-0000
        valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
        valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
    } else {
        // Telefone celular: (00) 00000-0000
        valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
        valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
    }
    
    return valor;
}

/**
 * Valida email
 * @param {string} email - Email a ser validado
 * @returns {boolean} - True se válido, False caso contrário
 */
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Valida data de nascimento (não pode ser futura e deve ser válida)
 * @param {string} data - Data no formato YYYY-MM-DD
 * @returns {boolean} - True se válida, False caso contrário
 */
function validarDataNascimento(data) {
    if (!data) return false;
    
    const dataObj = new Date(data);
    const hoje = new Date();
    
    // Verifica se a data é válida
    if (isNaN(dataObj.getTime())) {
        return false;
    }
    
    // Verifica se não é futura
    if (dataObj > hoje) {
        return false;
    }
    
    // Verifica se não é muito antiga (mais de 150 anos)
    const idadeMaxima = new Date();
    idadeMaxima.setFullYear(idadeMaxima.getFullYear() - 150);
    if (dataObj < idadeMaxima) {
        return false;
    }
    
    return true;
}

/**
 * Valida tipo sanguíneo
 * @param {string} tipo - Tipo sanguíneo
 * @returns {boolean} - True se válido, False caso contrário
 */
function validarTipoSanguineo(tipo) {
    const tiposValidos = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'RH-NULO'];
    return tiposValidos.includes(tipo);
}

/**
 * Inicializa validações em tempo real para um formulário
 * @param {string} formId - ID do formulário
 */
function inicializarValidacoes(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    // Validação de CPF
    const cpfInput = form.querySelector('input[name="cpf"]');
    if (cpfInput) {
        // Verificar se o CPF é obrigatório (tem atributo required ou é formulário de dependente ou perfil)
        const isRequired = cpfInput.hasAttribute('required') || formId === 'dependenteForm' || formId === 'perfilForm';
        
        // Aplicar máscara enquanto digita
        cpfInput.addEventListener('input', function(e) {
            const valor = e.target.value;
            const valorLimpo = valor.replace(/\D/g, '');
            
            if (valorLimpo.length <= 11) {
                e.target.value = mascaraCPF(valorLimpo);
            } else {
                e.target.value = mascaraCPF(valorLimpo.substring(0, 11));
            }
            
            // Validar em tempo real
            if (valorLimpo.length > 0) {
                if (validarCPF(valorLimpo)) {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido');
                    e.target.classList.add('campo-valido');
                } else {
                    e.target.setCustomValidity('CPF deve ter 11 dígitos.');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                }
            } else {
                // Se for obrigatório e estiver vazio
                if (isRequired) {
                    e.target.setCustomValidity('CPF é obrigatório.');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                } else {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido', 'campo-valido');
                }
            }
        });
        
        // Validar ao perder o foco
        cpfInput.addEventListener('blur', function(e) {
            const valorLimpo = e.target.value.replace(/\D/g, '');
            if (isRequired && valorLimpo.length === 0) {
                e.target.setCustomValidity('CPF é obrigatório.');
                e.target.classList.add('campo-invalido');
            } else if (valorLimpo.length > 0 && valorLimpo.length < 11) {
                e.target.setCustomValidity('CPF deve ter 11 dígitos.');
                e.target.classList.add('campo-invalido');
            } else if (valorLimpo.length > 0 && !validarCPF(valorLimpo)) {
                e.target.setCustomValidity('CPF deve ter 11 dígitos.');
                e.target.classList.add('campo-invalido');
            }
        });
        
        // Validar no submit do formulário
        form.addEventListener('submit', function(e) {
            const valorLimpo = cpfInput.value.replace(/\D/g, '');
            if (isRequired && valorLimpo.length === 0) {
                e.preventDefault();
                cpfInput.setCustomValidity('CPF é obrigatório.');
                cpfInput.classList.add('campo-invalido');
                cpfInput.focus();
                cpfInput.reportValidity();
                return false;
            } else if (valorLimpo.length > 0 && valorLimpo.length < 11) {
                e.preventDefault();
                cpfInput.setCustomValidity('CPF deve ter 11 dígitos.');
                cpfInput.classList.add('campo-invalido');
                cpfInput.focus();
                cpfInput.reportValidity();
                return false;
            } else if (valorLimpo.length > 0 && !validarCPF(valorLimpo)) {
                e.preventDefault();
                cpfInput.setCustomValidity('CPF deve ter 11 dígitos.');
                cpfInput.classList.add('campo-invalido');
                cpfInput.focus();
                cpfInput.reportValidity();
                return false;
            }
        });
    }
    
    // Validação de telefone
    const telefoneInputs = form.querySelectorAll('input[name="telefone"], input[name="contato_telefone"]');
    telefoneInputs.forEach(function(input) {
        const isRequired = input.hasAttribute('required');
        
        // Aplicar máscara enquanto digita
        input.addEventListener('input', function(e) {
            const valor = e.target.value;
            const valorLimpo = valor.replace(/\D/g, '');
            
            if (valorLimpo.length <= 11) {
                e.target.value = mascaraTelefone(valorLimpo);
            } else {
                e.target.value = mascaraTelefone(valorLimpo.substring(0, 11));
            }
            
            // Validar em tempo real
            if (valorLimpo.length > 0) {
                if (validarTelefone(valorLimpo)) {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido');
                    e.target.classList.add('campo-valido');
                } else {
                    e.target.setCustomValidity('Telefone inválido. Use apenas números (10 ou 11 dígitos).');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                }
            } else {
                // Se for obrigatório e estiver vazio
                if (isRequired) {
                    e.target.setCustomValidity('Telefone é obrigatório.');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                } else {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido', 'campo-valido');
                }
            }
        });
        
        // Bloquear entrada de letras
        input.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which);
            if (!/[0-9]/.test(char)) {
                e.preventDefault();
            }
        });
        
        // Validar ao perder o foco
        input.addEventListener('blur', function(e) {
            const valorLimpo = e.target.value.replace(/\D/g, '');
            if (isRequired && valorLimpo.length === 0) {
                e.target.setCustomValidity('Telefone é obrigatório.');
                e.target.classList.add('campo-invalido');
            } else if (valorLimpo.length > 0 && (valorLimpo.length < 10 || valorLimpo.length > 11)) {
                e.target.setCustomValidity('Telefone deve ter 10 ou 11 dígitos.');
                e.target.classList.add('campo-invalido');
            }
        });
    });
    
    // Validação de email
    const emailInput = form.querySelector('input[name="email"]');
    if (emailInput) {
        const isRequired = emailInput.hasAttribute('required');
        
        emailInput.addEventListener('input', function(e) {
            const valor = e.target.value.trim();
            if (valor.length > 0) {
                if (validarEmail(valor)) {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido');
                    e.target.classList.add('campo-valido');
                } else {
                    e.target.setCustomValidity('Email inválido. Use o formato: exemplo@dominio.com');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                }
            } else {
                // Se for obrigatório e estiver vazio
                if (isRequired) {
                    e.target.setCustomValidity('E-mail é obrigatório.');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                } else {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido', 'campo-valido');
                }
            }
        });
        
        emailInput.addEventListener('blur', function(e) {
            const valor = e.target.value.trim();
            if (isRequired && valor.length === 0) {
                e.target.setCustomValidity('E-mail é obrigatório.');
                e.target.classList.add('campo-invalido');
            } else if (valor.length > 0) {
                if (validarEmail(valor)) {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido');
                    e.target.classList.add('campo-valido');
                } else {
                    e.target.setCustomValidity('Email inválido. Use o formato: exemplo@dominio.com');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                }
            }
        });
    }
    
    // Validação de data de nascimento
    const dataInput = form.querySelector('input[name="data_nascimento"]');
    if (dataInput) {
        dataInput.addEventListener('change', function(e) {
            const valor = e.target.value;
            if (valor) {
                if (validarDataNascimento(valor)) {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido');
                    e.target.classList.add('campo-valido');
                } else {
                    e.target.setCustomValidity('Data de nascimento inválida. Não pode ser futura.');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                }
            } else {
                e.target.setCustomValidity('');
                e.target.classList.remove('campo-invalido', 'campo-valido');
            }
        });
    }
    
    // Validação de tipo sanguíneo
    const tipoSanguineoSelect = form.querySelector('select[name="tipo_sanguineo"]');
    if (tipoSanguineoSelect) {
        tipoSanguineoSelect.addEventListener('change', function(e) {
            const valor = e.target.value;
            if (valor && valor !== '') {
                if (validarTipoSanguineo(valor)) {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('campo-invalido');
                    e.target.classList.add('campo-valido');
                } else {
                    e.target.setCustomValidity('Tipo sanguíneo inválido.');
                    e.target.classList.remove('campo-valido');
                    e.target.classList.add('campo-invalido');
                }
            } else {
                e.target.setCustomValidity('');
                e.target.classList.remove('campo-invalido', 'campo-valido');
            }
        });
    }
    
    // Validação de parentesco (garantir que seja obrigatório)
    const parentescoSelect = form.querySelector('select[name="parentesco"]');
    if (parentescoSelect) {
        parentescoSelect.addEventListener('change', function(e) {
            const valor = e.target.value;
            if (valor && valor.trim() !== '') {
                e.target.setCustomValidity('');
                e.target.classList.remove('campo-invalido');
                e.target.classList.add('campo-valido');
            } else {
                e.target.setCustomValidity('Selecione o parentesco.');
                e.target.classList.remove('campo-valido');
                e.target.classList.add('campo-invalido');
            }
        });
    }
    
    // Validação de autorização de reanimação
    const ressuscitacaoSelect = form.querySelector('select[name="ressuscitacao"]');
    if (ressuscitacaoSelect) {
        ressuscitacaoSelect.addEventListener('change', function(e) {
            const valor = e.target.value;
            if (valor && valor.trim() !== '') {
                e.target.setCustomValidity('');
                e.target.classList.remove('campo-invalido');
                e.target.classList.add('campo-valido');
            } else {
                e.target.setCustomValidity('Selecione uma opção.');
                e.target.classList.remove('campo-valido');
                e.target.classList.add('campo-invalido');
            }
        });
    }
}

// Inicializar validações quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    inicializarValidacoes('perfilForm');
    inicializarValidacoes('dependenteForm');
});

