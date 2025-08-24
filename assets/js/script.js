window.addEventListener('error', function (event) {
    return false;
});

// Variável global para controlar o processo de detecção
let detectionInProgress = false;

// Função para iniciar a detecção de placas
function iniciarDetecao() {
    const botaoIniciar = document.getElementById('btnIniciar');
    const botaoParar = document.getElementById('btnParar');
    const cameraFeed = document.querySelector('.camera-feed');
    
    // Define que a detecção está em andamento
    detectionInProgress = true;

    // Desabilita o botão de iniciar e habilita o de parar
    botaoIniciar.disabled = true;
    botaoParar.disabled = false;
    
    // Adiciona o evento de clique para o botão de parar
    botaoParar.onclick = function() {
        detectionInProgress = false;
        botaoIniciar.disabled = false;
        botaoParar.disabled = true;
        
        // Envia um sinal para parar o script Python
        fetch('../python/parar_detecao.php')
            .catch(error => console.error('Erro ao parar a detecção:', error));
            
        // Atualiza o status da câmera
        const statusIndicator = document.querySelector('.status-indicator');
        if (statusIndicator) {
            statusIndicator.classList.remove('status-online', 'status-processing');
            statusIndicator.classList.add('status-offline');
            statusIndicator.nextSibling.textContent = 'Detecção Parada';
        }
    };

    // Remove mensagens anteriores
    const existingMessages = document.querySelectorAll('.loading-message, .alert');
    existingMessages.forEach(msg => msg.remove());

    // Mostra mensagem de carregamento
    const loadingMessage = document.createElement('div');
    loadingMessage.className = 'loading-message';
    loadingMessage.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Iniciando detecção de placas...';
    cameraFeed.appendChild(loadingMessage);

    // Atualiza o status da câmera
    const statusIndicator = cameraFeed.querySelector('.status-indicator');
    if (statusIndicator) {
        statusIndicator.classList.add('status-processing');
        statusIndicator.classList.remove('status-online', 'status-offline');
    }

    // Faz a requisição para executar o script Python
    fetch('../python/executar_detecao.php')
        .then(async response => {
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Erro na resposta:', errorText);
                throw new Error(`Erro na requisição: ${response.status} - ${response.statusText}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Resposta não é JSON:', text);
                throw new Error('A resposta não está no formato JSON esperado');
            }

            return response.json().catch(error => {
                console.error('Erro ao fazer parse do JSON:', error);
                throw new Error('Erro ao processar a resposta do servidor');
            });
        })
        .then(data => {
            // Remove a mensagem de carregamento
            const loadingMessage = cameraFeed.querySelector('.loading-message');
            if (loadingMessage) {
                loadingMessage.remove();
            }

            // Atualiza o status da câmera
            const statusIndicator = cameraFeed.querySelector('.status-indicator');
            if (statusIndicator) {
                statusIndicator.classList.remove('status-processing');
                statusIndicator.classList.add('status-online');
            }

            if (data.success) {
                // Mostra mensagem de sucesso
                const successMessage = document.createElement('div');
                successMessage.className = 'alert alert-success mt-3';
                successMessage.textContent = 'Detecção concluída com sucesso!';
                cameraFeed.appendChild(successMessage);

                // Atualiza a lista de detecções recentes após um pequeno atraso
                setTimeout(atualizarDetecoesRecentes, 1000);

                // Mostra a imagem processada se disponível
                const outputImage = 'python/output/placa_binarizada.png' + '?t=' + new Date().getTime();
                const img = document.createElement('img');
                img.src = '../' + outputImage;
                img.alt = 'Placa detectada';
                img.style.maxWidth = '100%';
                cameraFeed.appendChild(img);
            } else {
                // Mostra mensagem de erro
                const errorMessage = document.createElement('div');
                errorMessage.className = 'alert alert-danger mt-3';
                errorMessage.textContent = data.output ? data.output.join('\n') : 'Erro desconhecido ao executar a detecção';
                cameraFeed.appendChild(errorMessage);

                // Atualiza o status da câmera para erro
                const statusIndicator = cameraFeed.querySelector('.status-indicator');
                if (statusIndicator) {
                    statusIndicator.classList.remove('status-processing', 'status-online');
                    statusIndicator.classList.add('status-offline');
                }
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);

            // Remove a mensagem de carregamento se ainda existir
            const loadingMessage = document.querySelector('.loading-message');
            if (loadingMessage) {
                loadingMessage.remove();
            }

            // Mostra mensagem de erro detalhada
            const errorMessage = document.createElement('div');
            errorMessage.className = 'alert alert-danger mt-3';

            // Mensagem mais amigável baseada no tipo de erro
            let errorText = 'Erro ao executar a detecção';
            if (error.message.includes('Failed to fetch')) {
                errorText = 'Não foi possível conectar ao servidor. Verifique sua conexão com a internet.';
            } else if (error.message.includes('404')) {
                errorText = 'O serviço de detecção não está disponível no momento. Tente novamente mais tarde.';
            } else if (error.message.includes('JSON')) {
                errorText = 'Erro ao processar a resposta do servidor. O formato dos dados é inválido.';
            } else {
                errorText = `Erro: ${error.message}`;
            }

            errorMessage.textContent = errorText;
            const cameraFeed = document.querySelector('.camera-feed');
            if (cameraFeed) {
                cameraFeed.appendChild(errorMessage);
            } else {
                console.error('Elemento .camera-feed não encontrado');
            }

            // Atualiza o status da câmera para offline
            const statusIndicator = document.querySelector('.status-indicator');
            if (statusIndicator) {
                statusIndicator.classList.remove('status-processing');
                statusIndicator.classList.add('status-offline');
            }
        })
        .finally(() => {
            // Reabilita o botão de iniciar e desabilita o de parar
            if (botaoIniciar) botaoIniciar.disabled = false;
            if (botaoParar) botaoParar.disabled = true;

            // Remove a classe de processamento do status
            const statusIndicator = document.querySelector('.status-indicator');
            if (statusIndicator && !statusIndicator.classList.contains('status-offline')) {
                statusIndicator.classList.remove('status-processing');
                statusIndicator.classList.add('status-online');
            }
        });
}

// Função para atualizar a lista de detecções recentes
function atualizarDetecoesRecentes() {
    // Aqui você pode adicionar o código para atualizar a lista de detecções recentes
    // Por exemplo, fazendo uma requisição AJAX para buscar as últimas detecções
    console.log('Atualizando lista de detecções recentes...');
    // Recarrega a página para atualizar a lista
    window.location.reload();
}

function showAlert(type, message) {
    try {
        const existingAlerts = document.querySelectorAll('.custom-alert');
        existingAlerts.forEach(alert => alert.remove());

        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert alert-${type}`;
        alertDiv.role = 'alert';

        Object.assign(alertDiv.style, {
            position: 'fixed',
            top: '20px',
            left: '50%',
            transform: 'translateX(-50%)',
            padding: '15px 25px',
            borderRadius: '4px',
            color: '#fff',
            zIndex: '9999',
            maxWidth: '90%',
            width: 'auto',
            textAlign: 'center',
            boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            animation: 'fadeIn 0.3s ease-out'
        });

        const colors = {
            success: '#28a745',
            danger: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8',
            primary: '#007bff'
        };

        alertDiv.style.backgroundColor = colors[type] || colors.primary;

        const messageSpan = document.createElement('span');
        messageSpan.textContent = message;
        alertDiv.appendChild(messageSpan);

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.innerHTML = '&times;';
        closeButton.style.background = 'none';
        closeButton.style.border = 'none';
        closeButton.style.color = '#fff';
        closeButton.style.fontSize = '20px';
        closeButton.style.cursor = 'pointer';
        closeButton.style.marginLeft = '15px';
        closeButton.style.padding = '0 5px';
        closeButton.style.lineHeight = '1';
        closeButton.addEventListener('click', () => {
            alertDiv.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => alertDiv.remove(), 300);
        });

        alertDiv.appendChild(closeButton);

        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translate(-50%, -10px); }
                to { opacity: 1; transform: translate(-50%, 0); }
            }
            @keyframes fadeOut {
                from { opacity: 1; transform: translate(-50%, 0); }
                to { opacity: 0; transform: translate(-50%, -10px); }
            }
        `;
        document.head.appendChild(style);

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 5000);
    } catch (error) {
        showMessage('Erro ao exibir alerta. Tente novamente.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Only proceed with form-related code if we're on a page with forms
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    // Only initialize form-related variables if the forms exist
    let passwordField = null;
    let confirmPasswordField = null;

    if (registerForm) {
        passwordField = registerForm.querySelector('input[name="password"]');
        confirmPasswordField = registerForm.querySelector('input[name="confirm_password"]');

        // Only add event listeners if the fields exist
        if (passwordField && confirmPasswordField) {
            passwordField.addEventListener('change', validatePassword);
            confirmPasswordField.addEventListener('keyup', validatePassword);
        }

        registerForm.addEventListener('submit', handleRegisterSubmit);
    }

    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            showMessage('Login form submitted', 'success');
        });
    }

    const isMobile = window.innerWidth <= 900;

    function scrollToElement(element) {
        if (isMobile) {
            window.scrollTo({
                top: element.offsetTop - 20,
                behavior: 'smooth'
            });
        }
    }

    // Handle sign up button click
    if (signUpButton && container) {
        signUpButton.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.add("right-panel-active");
            if (isMobile) {
                const signUpForm = document.querySelector('.sign-up-container');
                if (signUpForm) scrollToElement(signUpForm);
            }
        });
    }

    // Handle sign in button click
    if (signInButton && container) {
        signInButton.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.remove("right-panel-active");
            if (isMobile) {
                const signInForm = document.querySelector('.sign-in-container');
                if (signInForm) scrollToElement(signInForm);
            }
        });
    }

    function validatePassword() {
        if (!passwordField || !confirmPasswordField) return;

        if (passwordField.value !== confirmPasswordField.value) {
            confirmPasswordField.setCustomValidity("As senhas não coincidem");
        } else {
            confirmPasswordField.setCustomValidity('');
        }
    }

    // Registration form submission handler
    async function handleRegisterSubmit(e) {
        e.preventDefault();

        const submitButton = registerForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cadastrando...';

        try {
            if (!registerForm.checkValidity()) {
                e.stopPropagation();
                registerForm.classList.add('was-validated');
                throw new Error('Por favor, preencha todos os campos corretamente.');
            }

            if (passwordField.value !== confirmPasswordField.value) {
                throw new Error('As senhas não coincidem!');
            }
            const formData = new FormData(registerForm);

            const response = await fetch('register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: new URLSearchParams(formData).toString()
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                showMessage('Resposta inesperada do servidor. Por favor, tente novamente.', 'error');
            }

            const result = await response.json();
            showMessage('Resposta do servidor:', result);

            if (result.success) {
                showMessage('Cadastro realizado com sucesso! Redirecionando...', 'success');

                if (result.redirect) {
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                }
            } else {
                throw new Error(result.message || 'Erro desconhecido ao realizar o cadastro.');
            }
        } catch (error) {
            showMessage('Erro ao enviar formulário:', error);
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    }
});

// Adiciona evento de clique para o botão Iniciar Detecção
const botaoIniciar = document.querySelector('.control-btn .fa-play')?.parentElement;
if (botaoIniciar) {
    botaoIniciar.addEventListener('click', function (e) {
        e.preventDefault();
        iniciarDetecao();
    });
}

// Adiciona evento de clique para o botão Parar
const botaoParar = document.querySelector('.control-btn .fa-stop')?.parentElement;
if (botaoParar) {
    botaoParar.addEventListener('click', function (e) {
        e.preventDefault();
        // Aqui você pode adicionar a lógica para parar a detecção
        console.log('Detecção interrompida pelo usuário');
        // Recarrega a página para parar qualquer processo em andamento
        window.location.reload();
    });
}

const inputs = document.querySelectorAll('input');
inputs.forEach(input => {
    input.addEventListener('input', function () {
        if (input.checkValidity()) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
        }
    });
}); 