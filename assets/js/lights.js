// lights.js - Gerencia a lógica das lâmpadas

// Variáveis globais
let currentLightId = null;
let currentButton = null;

// Função para remover uma lâmpada
function removeLight(lightId, button) {
    currentLightId = lightId;
    currentButton = button;
    
    // Exibe o modal de confirmação
    const modal = document.getElementById('confirmDeleteModal');
    const lightName = button.closest('.light-card').querySelector('h3').textContent;
    document.getElementById('lightToDelete').textContent = lightName;
    modal.style.display = 'flex';
}

// Função para confirmar a exclusão
function confirmDelete() {
    if (!currentLightId || !currentButton) return;
    
    const modal = document.getElementById('confirmDeleteModal');
    
    fetch('processar_lampada.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remover&id=${currentLightId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Lâmpada removida com sucesso!', 'success');
            // Remove o card da lâmpada
            currentButton.closest('.light-card').remove();
            
            // Verifica se ainda existem lâmpadas
            if (document.querySelectorAll('.light-card').length === 0) {
                window.location.reload();
            }
        } else {
            showMessage('Erro ao remover: ' + (data.message || 'Erro desconhecido'), 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showMessage('Erro ao processar a requisição', 'error');
    })
    .finally(() => {
        modal.style.display = 'none';
        currentLightId = null;
        currentButton = null;
    });
}

// Função para cancelar a exclusão
function cancelDelete() {
    const modal = document.getElementById('confirmDeleteModal');
    modal.style.display = 'none';
    currentLightId = null;
    currentButton = null;
}

// Função para exibir mensagens
function showMessage(message, type = 'info') {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    
    // Adiciona o botão de fechar
    const closeButton = document.createElement('button');
    closeButton.className = 'close-message';
    closeButton.innerHTML = '&times;';
    closeButton.onclick = () => messageDiv.remove();
    
    messageDiv.appendChild(closeButton);
    
    // Adiciona a mensagem ao container
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(messageDiv, container.firstChild);
    }
    
    // Remove a mensagem após 5 segundos
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 5000);
}

// Função para alternar o estado da lâmpada
function toggleLight(lightId, element = null) {
    const card = document.querySelector(`[data-light-id="${lightId}"]`);
    if (!card) return;
    
    const statusElement = card.querySelector('.light-status');
    const statusText = statusElement ? statusElement.querySelector('span') : null;
    const brightnessSlider = document.getElementById(`brightness-${lightId}`);
    const isOn = card.classList.contains('on');
    
    // Atualiza o estado visual imediatamente para melhor resposta do usuário
    if (isOn) {
        card.classList.remove('on');
        if (statusText) statusText.textContent = 'Desligada';
        if (brightnessSlider) brightnessSlider.disabled = true;
    } else {
        card.classList.add('on');
        if (statusText) statusText.textContent = 'Ligada';
        if (brightnessSlider) brightnessSlider.disabled = false;
    }
    
    // Se o elemento foi passado (clique no ícone), atualiza o ícone
    if (element) {
        const isOnNow = element.classList.toggle('on');
        const icon = element.querySelector('i');
        if (icon) {
            if (isOnNow) {
                icon.className = 'fas fa-lightbulb';
                icon.style.color = '#ffd700';
            } else {
                icon.className = 'far fa-lightbulb';
                icon.style.color = '#666';
            }
        }
    }
    
    // Envia a requisição para o servidor
    fetch('processar_lampada.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=alternar&id=${lightId}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Reverte as alterações visuais em caso de erro
            if (isOn) {
                card.classList.add('on');
                if (statusText) statusText.textContent = 'Ligada';
                if (brightnessSlider) brightnessSlider.disabled = false;
            } else {
                card.classList.remove('on');
                if (statusText) statusText.textContent = 'Desligada';
                if (brightnessSlider) brightnessSlider.disabled = true;
            }
            showMessage('Erro ao atualizar o estado da lâmpada', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        // Reverte as alterações visuais em caso de erro
        if (isOn) {
            card.classList.add('on');
            if (statusText) statusText.textContent = 'Ligada';
            if (brightnessSlider) brightnessSlider.disabled = false;
        } else {
            card.classList.remove('on');
            if (statusText) statusText.textContent = 'Desligada';
            if (brightnessSlider) brightnessSlider.disabled = true;
        }
        showMessage('Erro ao conectar ao servidor', 'error');
    });
}

// Função para ajustar o brilho
function changeBrightness(lightId, value) {
    const card = document.querySelector(`[data-light-id="${lightId}"]`);
    const brightnessValue = document.getElementById(`brightness-value-${lightId}`);
    
    // Atualiza o valor exibido
    if (brightnessValue) {
        brightnessValue.textContent = `${value}%`;
    }
    
    // Envia a requisição para o servidor
    fetch('processar_lampada.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=ajustar_brilho&id=${lightId}&brilho=${value}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            showMessage('Erro ao ajustar o brilho', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showMessage('Erro ao conectar ao servidor', 'error');
    });
}

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa o estado das lâmpadas
    const initialStatus = document.body.getAttribute('data-initial-status');
    if (initialStatus === 'on') {
        const card = document.querySelector('[data-light-id="1"]');
        if (card) {
            card.classList.add('on');
            const statusText = card.querySelector('.light-status span');
            if (statusText) statusText.textContent = 'Ligada';
            const brightnessSlider = document.getElementById('brightness-1');
            if (brightnessSlider) brightnessSlider.disabled = false;
        }
    }
    
    // Inicializa os tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltipElement = document.createElement('div');
            tooltipElement.className = 'tooltip';
            tooltipElement.textContent = tooltipText;
            this.appendChild(tooltipElement);
            
            // Posiciona o tooltip
            const rect = this.getBoundingClientRect();
            tooltipElement.style.top = `${rect.top - tooltipElement.offsetHeight - 10}px`;
            tooltipElement.style.left = `${rect.left + (this.offsetWidth / 2) - (tooltipElement.offsetWidth / 2)}px`;
        });
        
        tooltip.addEventListener('mouseleave', function() {
            const tooltipElement = this.querySelector('.tooltip');
            if (tooltipElement) {
                tooltipElement.remove();
            }
        });
    });
});

// Adiciona um listener para o evento beforeunload
window.addEventListener('beforeunload', function() {
    console.log('Page is being unloaded, but keeping the light controller running');
});

// Função para exibir mensagens
function showMessage(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#27ae60' : type === 'error' ? '#e74c3c' : type === 'warning' ? '#f39c12' : '#4a90e2'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-size: 14px;
        max-width: 300px;
        opacity: 0;
        transform: translateY(-50px);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    `;
    
    let icon = '';
    switch(type) {
        case 'success':
            icon = '<i class="fas fa-check-circle"></i>';
            break;
        case 'error':
            icon = '<i class="fas fa-times-circle"></i>';
            break;
        case 'warning':
            icon = '<i class="fas fa-exclamation-triangle"></i>';
            break;
        default:
            icon = '<i class="fas fa-info-circle"></i>';
    }
    
    toast.innerHTML = `${icon} ${message}`;
    
    document.body.appendChild(toast);
    
    // Anima a entrada
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 100);
    
    // Remove após 5 segundos
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-50px)';
        setTimeout(() => {
            if (toast.parentNode) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

// Função para ligar/desligar a lâmpada
function toggleLight(lightId, element) {
    const isOn = element.classList.toggle('on');
    const icon = element.querySelector('i');
    const brightnessSlider = document.getElementById(`brightness-${lightId}`);
    const brightnessValue = brightnessSlider ? brightnessSlider.value : 100;
    
    // Atualiza o ícone baseado no estado
    if (isOn) {
        icon.className = 'fas fa-lightbulb';
        element.style.color = '#f1c40f';
    } else {
        icon.className = 'far fa-lightbulb';
        element.style.color = '#95a5a6';
    }
    
    // Envia a requisição para atualizar o status
    fetch('processar_lampada.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=toggle&id=${lightId}&status=${isOn ? 'on' : 'off'}&brightness=${brightnessValue}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            showMessage('Erro ao atualizar a lâmpada: ' + (data.message || 'Erro desconhecido'), 'error');
            // Reverte a mudança em caso de erro
            element.classList.toggle('on');
            if (isOn) {
                icon.className = 'far fa-lightbulb';
                element.style.color = '#95a5a6';
            } else {
                icon.className = 'fas fa-lightbulb';
                element.style.color = '#f1c40f';
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showMessage('Erro ao processar a requisição', 'error');
    });
}

// Função para ajustar o brilho
function changeBrightness(lightId, value) {
    const lightElement = document.querySelector(`.light-switch[data-light-id="${lightId}"]`);
    const isOn = lightElement ? lightElement.classList.contains('on') : false;
    
    if (isOn) {
        fetch('processar_lampada.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_brightness&id=${lightId}&brightness=${value}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                showMessage('Erro ao ajustar o brilho: ' + (data.message || 'Erro desconhecido'), 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showMessage('Erro ao processar a requisição', 'error');
        });
    }
}

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa os tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltipElement = document.createElement('div');
            tooltipElement.className = 'tooltip';
            tooltipElement.textContent = tooltipText;
            this.appendChild(tooltipElement);
            
            // Posiciona o tooltip
            const rect = this.getBoundingClientRect();
            tooltipElement.style.top = (rect.top - tooltipElement.offsetHeight - 10) + 'px';
            tooltipElement.style.left = (rect.left + (this.offsetWidth / 2) - (tooltipElement.offsetWidth / 2)) + 'px';
        });
        
        tooltip.addEventListener('mouseleave', function() {
            const tooltipElement = this.querySelector('.tooltip');
            if (tooltipElement) {
                this.removeChild(tooltipElement);
            }
        });
    });

    // Inicializa o status das lâmpadas
    const lightSwitches = document.querySelectorAll('.light-switch');
    lightSwitches.forEach(lightSwitch => {
        const lightId = lightSwitch.getAttribute('data-light-id');
        const isOn = lightSwitch.classList.contains('on');
        const icon = lightSwitch.querySelector('i');
        
        if (isOn) {
            icon.className = 'fas fa-lightbulb';
            lightSwitch.style.color = '#f1c40f';
        } else {
            icon.className = 'far fa-lightbulb';
            lightSwitch.style.color = '#95a5a6';
        }
    });
});
