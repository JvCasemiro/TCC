let currentLightId = null;
let currentButton = null;

function elementExists(selector) {
    return document.querySelector(selector) !== null;
}

function getLightCard(lightId) {
    return document.querySelector(`[data-light-id="${lightId}"]`);
}

function showMessage(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function updateLightsStatus() {
    fetch('../includes/get_lights_status.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.lampadas.forEach(lampada => {
                    const card = getLightCard(lampada.ID_Lampada);
                    if (card) {
                        const statusElement = card.querySelector('.light-status');
                        const statusText = statusElement ? statusElement.querySelector('span') : null;
                        const status = lampada.Status === 'on' ? 'on' : 'off';
                        
                        card.setAttribute('data-status', status);
                        if (statusText) {
                            statusText.textContent = status === 'on' ? 'Ligada' : 'Desligada';
                        }
                        
                        const toggleBtn = card.querySelector('.toggle-btn');
                        if (toggleBtn) {
                            toggleBtn.innerHTML = status === 'on' ? 
                                '<i class="fas fa-lightbulb"></i>' : 
                                '<i class="far fa-lightbulb"></i>';
                            toggleBtn.className = `toggle-btn ${status}`;
                        }
                    }
                });
                
                const percentageElement = document.getElementById('lights-percentage');
                if (percentageElement) {
                    percentageElement.textContent = `${data.porcentagem}%`;
                }
            }
        })
        .catch(error => {
            console.error('Erro ao atualizar status das lâmpadas:', error);
        });
}

function toggleLight(lightId, element = null) {
    const card = document.querySelector(`[data-light-id="${lightId}"]`);
    if (!card) return;
    
    const currentStatus = card.getAttribute('data-status') || 'off';
    const newStatus = currentStatus === 'on' ? 'off' : 'on';
    
    card.setAttribute('data-status', newStatus);
    
    if (element) {
        element.innerHTML = newStatus === 'on' ? 
            '<i class="fas fa-lightbulb"></i>' : 
            '<i class="far fa-lightbulb"></i>';
        element.className = `toggle-btn ${newStatus}`;
    }
    
    fetch('../includes/update_light.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            light_id: lightId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            card.setAttribute('data-status', currentStatus);
            if (element) {
                element.innerHTML = currentStatus === 'on' ? 
                    '<i class="fas fa-lightbulb"></i>' : 
                    '<i class="far fa-lightbulb"></i>';
                element.className = `toggle-btn ${currentStatus}`;
            }
            showMessage('Erro ao atualizar a lâmpada: ' + (data.message || 'Erro desconhecido'), 'error');
        } else {
            updateLightsStatus();
            showMessage(`Lâmpada ${lightId} ${newStatus === 'on' ? 'ligada' : 'desligada'} com sucesso!`, 'success');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        card.setAttribute('data-status', currentStatus);
        if (element) {
            element.innerHTML = currentStatus === 'on' ? 
                '<i class="fas fa-lightbulb"></i>' : 
                '<i class="far fa-lightbulb"></i>';
            element.className = `toggle-btn ${currentStatus}`;
        }
    });
}

function showMessage(message, type = 'info') {
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    
    const closeButton = document.createElement('button');
    closeButton.className = 'close-message';
    closeButton.innerHTML = '&times;';
    closeButton.onclick = () => messageDiv.remove();
    
    messageDiv.appendChild(closeButton);
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(messageDiv, container.firstChild);
    }
    
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
    updateLightsStatus();
    setInterval(updateLightsStatus, 5000);
    
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltipElement = document.createElement('div');
            tooltipElement.className = 'tooltip';
            tooltipElement.textContent = tooltipText;
            this.appendChild(tooltipElement);
            
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

document.addEventListener('DOMContentLoaded', function() {
    const initialStatus = document.body.getAttribute('data-initial-status');
    if (initialStatus === 'on') {
        const card = document.querySelector('[data-light-id="1"]');
        if (card) {
            card.classList.add('on');
            const statusText = card.querySelector('.light-status span');
            if (statusText) statusText.textContent = 'Ligada';
        }
    }
    
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltipElement = document.createElement('div');
            tooltipElement.className = 'tooltip';
            tooltipElement.textContent = tooltipText;
            this.appendChild(tooltipElement);
            
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

window.addEventListener('beforeunload', function() {
    return;
});

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
    
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 100);
    
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

function toggleLight(lightId, element) {
    if (!element) {
        element = document.querySelector(`[data-light-id="${lightId}"]`);
        if (!element) {
            console.error(`Elemento da lâmpada ${lightId} não encontrado`);
            return;
        }
    }

    const card = element.closest('.light-card') || element;
    const icon = element.querySelector('i') || card.querySelector('i');
    const brightnessSlider = document.getElementById(`brightness-${lightId}`);
    const statusText = card.querySelector('.light-status span');
    const isCurrentlyOn = card.classList.contains('on');
    const newStatus = !isCurrentlyOn;
    
    if (newStatus) {
        card.classList.add('on');
        if (icon) {
            icon.className = 'fas fa-lightbulb';
            icon.style.color = '#f1c40f';
        }
        if (statusText) statusText.textContent = 'Ligada';
        if (brightnessSlider) brightnessSlider.disabled = false;
    } else {
        card.classList.remove('on');
        if (icon) {
            icon.className = 'far fa-lightbulb';
            icon.style.color = '#95a5a6';
        }
        if (statusText) statusText.textContent = 'Desligada';
        if (brightnessSlider) brightnessSlider.disabled = true;
    }
    
    fetch('../includes/update_light.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            light_id: lightId,
            status: newStatus ? 'ON' : 'OFF'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            showMessage('Erro ao atualizar a lâmpada: ' + (data.message || 'Erro desconhecido'), 'error');
            card.classList.toggle('on');
            if (icon) {
                icon.className = isCurrentlyOn ? 'fas fa-lightbulb' : 'far fa-lightbulb';
                icon.style.color = isCurrentlyOn ? '#f1c40f' : '#95a5a6';
            }
            if (statusText) statusText.textContent = isCurrentlyOn ? 'Ligada' : 'Desligada';
            if (brightnessSlider) brightnessSlider.disabled = !isCurrentlyOn;
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

document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltipElement = document.createElement('div');
            tooltipElement.className = 'tooltip';
            tooltipElement.textContent = tooltipText;
            this.appendChild(tooltipElement);
            
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
