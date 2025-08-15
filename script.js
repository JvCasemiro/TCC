// Configuração global de tratamento de erros
window.addEventListener('error', function(event) {
    return false;
});

// Função para exibir mensagens de alerta
function showAlert(type, message) {
    try {
        // Remover alertas anteriores
        const existingAlerts = document.querySelectorAll('.custom-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Criar elemento de alerta
        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert alert-${type}`;
        alertDiv.role = 'alert';
        
        // Estilos inline para garantir que o alerta seja visível
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
        
        // Cores baseadas no tipo de alerta
        const colors = {
            success: '#28a745',
            danger: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8',
            primary: '#007bff'
        };
        
        alertDiv.style.backgroundColor = colors[type] || colors.primary;
        
        // Adicionar mensagem
        const messageSpan = document.createElement('span');
        messageSpan.textContent = message;
        alertDiv.appendChild(messageSpan);
        
        // Adicionar botão de fechar
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
        
        // Adicionar estilos de animação
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
        
        // Adicionar alerta ao body
        document.body.appendChild(alertDiv);
        
        // Fechar automaticamente após 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 5000);
    } catch (error) {
        console.error('Erro ao exibir alerta:', error);
        // Fallback para alerta nativo em caso de erro
        window.alert(message);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const passwordField = registerForm ? registerForm.querySelector('input[name="password"]') : null;
    const confirmPasswordField = registerForm ? registerForm.querySelector('input[name="confirm_password"]') : null;
    
    // Verificar se estamos em um dispositivo móvel
    const isMobile = window.innerWidth <= 900;

    // Função para rolar suavemente para um elemento
    function scrollToElement(element) {
        if (isMobile) {
            window.scrollTo({
                top: element.offsetTop - 20,
                behavior: 'smooth'
            });
        }
    }

    // Switch to Sign Up form
    if (signUpButton) {
        signUpButton.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.add("right-panel-active");
            if (isMobile) {
                const signUpForm = document.querySelector('.sign-up-container');
                scrollToElement(signUpForm);
            }
        });
    }

    // Switch to Sign In form
    if (signInButton) {
        signInButton.addEventListener('click', (e) => {
            e.preventDefault();
            container.classList.remove("right-panel-active");
            if (isMobile) {
                const signInForm = document.querySelector('.sign-in-container');
                scrollToElement(signInForm);
            }
        });
    }

    // Password match validation
    function validatePassword() {
        if (passwordField.value !== confirmPasswordField.value) {
            confirmPasswordField.setCustomValidity("As senhas não coincidem");
        } else {
            confirmPasswordField.setCustomValidity('');
        }
    }

    // Add event listeners for password fields
    passwordField.addEventListener('change', validatePassword);
    confirmPasswordField.addEventListener('keyup', validatePassword);

    // Form submission handling - let the form submit naturally to PHP
    loginForm.addEventListener('submit', function(e) {
        // Don't prevent default - let the form submit to index.php
        // The PHP will handle validation and redirect to menu.php
        console.log('Login form submitted');
    });

    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Mostrar indicador de carregamento
        const submitButton = registerForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cadastrando...';
        
        try {
            // Validate form
            if (!registerForm.checkValidity()) {
                e.stopPropagation();
                registerForm.classList.add('was-validated');
                throw new Error('Por favor, preencha todos os campos corretamente.');
            }

            // Check if passwords match
            if (passwordField.value !== confirmPasswordField.value) {
                throw new Error('As senhas não coincidem!');
            }

            // Get form data
            const formData = new FormData(registerForm);
            
            // Enviar dados para o servidor
            const response = await fetch('register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: new URLSearchParams(formData).toString()
            });
            
            // Verificar se a resposta é JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Resposta inesperada do servidor:', text);
                throw new Error('Resposta inválida do servidor. Por favor, tente novamente.');
            }
            
            const result = await response.json();
            console.log('Resposta do servidor:', result);
            
            if (result.success) {
                // Mostrar mensagem de sucesso
                showAlert('success', 'Cadastro realizado com sucesso! Redirecionando...');
                
                // Redirecionar após um pequeno atraso
                if (result.redirect) {
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                }
            } else {
                throw new Error(result.message || 'Erro desconhecido ao realizar o cadastro.');
            }
        } catch (error) {
            console.error('Erro ao enviar formulário:', error);
            showAlert('danger', error.message || 'Erro ao conectar com o servidor. Tente novamente mais tarde.');
        } finally {
            // Restaurar botão
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    });

    // Add input validation feedback
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (input.checkValidity()) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            } else {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
            }
        });
    });
});