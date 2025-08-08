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
        // Don't prevent default - let the form submit to login.php
        // The PHP will handle validation and redirect to menu.php
        console.log('Login form submitted');
    });

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!registerForm.checkValidity()) {
            e.stopPropagation();
            registerForm.classList.add('was-validated');
            return;
        }

        // Check if passwords match
        if (passwordField.value !== confirmPasswordField.value) {
            alert('As senhas não coincidem!');
            return;
        }

        // Simulate form submission
        const formData = new FormData(registerForm);
        const formProps = Object.fromEntries(formData);
        
        // Remove confirm_password from the data before sending
        delete formProps.confirm_password;
        
        console.log('Registration data:', formProps);
        
        // Here you would typically make an AJAX call to your PHP backend
        // For now, we'll just show an alert and switch to login
        alert('Cadastro realizado com sucesso! Faça login para continuar.');
        
        // Reset form and switch to login
        registerForm.reset();
        container.classList.remove("right-panel-active");
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