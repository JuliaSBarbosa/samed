/**
 * Sistema de Toast Notifications para o SAMED
 */

function mostrarToast(mensagem, tipo = 'info', duracao = 5000) {
    // Tipos: success, error, warning, info
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${tipo}`;
    
    // Ícones por tipo
    const icones = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-icon">${icones[tipo] || icones.info}</span>
            <span class="toast-message">${mensagem}</span>
            <button class="toast-close" onclick="fecharToast(this)">×</button>
        </div>
    `;
    
    // Adicionar ao container de toasts
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
    
    container.appendChild(toast);
    
    // Animação de entrada
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Remover automaticamente após duração
    setTimeout(() => {
        fecharToast(toast.querySelector('.toast-close'));
    }, duracao);
}

function fecharToast(btn) {
    const toast = btn.closest('.toast-notification');
    if (toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
}

// Auto-exibir mensagens da sessão PHP
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se há mensagens de sucesso
    const mensagensSucesso = document.querySelectorAll('.mensagem-sucesso');
    mensagensSucesso.forEach(function(msg) {
        const texto = msg.textContent.trim();
        if (texto) {
            mostrarToast(texto, 'success');
            msg.style.display = 'none'; // Ocultar mensagem original
        }
    });
    
    // Verificar se há mensagens de erro
    const mensagensErro = document.querySelectorAll('.mensagem-erro');
    mensagensErro.forEach(function(msg) {
        const texto = msg.textContent.trim();
        if (texto) {
            mostrarToast(texto, 'error');
            msg.style.display = 'none'; // Ocultar mensagem original
        }
    });
});

