window.addEventListener('error', function(event) {
    return false;
});

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

document.addEventListener('DOMContentLoaded', function() {
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const passwordField = registerForm ? registerForm.querySelector('input[name="password"]') : null;
    const confirmPasswordField = registerForm ? registerForm.querySelector('input[name="confirm_password"]') : null;
    
    const isMobile = window.innerWidth <= 900;

    function scrollToElement(element) {
        if (isMobile) {
            window.scrollTo({
                top: element.offsetTop - 20,
                behavior: 'smooth'
            });
        }
    }

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

    function validatePassword() {
        if (passwordField.value !== confirmPasswordField.value) {
            confirmPasswordField.setCustomValidity("As senhas não coincidem");
        } else {
            confirmPasswordField.setCustomValidity('');
        }
    }

    passwordField.addEventListener('change', validatePassword);
    confirmPasswordField.addEventListener('keyup', validatePassword);

    loginForm.addEventListener('submit', function(e) {
        showMessage('Login form submitted', 'success');
    });

    registerForm.addEventListener('submit', async function(e) {
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
    });

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