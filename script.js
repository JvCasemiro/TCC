document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirmPassword');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const passwordIcon = document.getElementById('passwordIcon');
    const confirmPasswordIcon = document.getElementById('confirmPasswordIcon');

    togglePassword.addEventListener('click', function() {
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;

        if (type === 'password') {
            passwordIcon.classList.remove('fa-eye-slash');
            passwordIcon.classList.add('fa-eye');
        } else {
            passwordIcon.classList.remove('fa-eye');
            passwordIcon.classList.add('fa-eye-slash');
        }
    });

    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordField.type === 'password' ? 'text' : 'password';
        confirmPasswordField.type = type;

        if (type === 'password') {
            confirmPasswordIcon.classList.remove('fa-eye-slash');
            confirmPasswordIcon.classList.add('fa-eye');
        } else {
            confirmPasswordIcon.classList.remove('fa-eye');
            confirmPasswordIcon.classList.add('fa-eye-slash');
        }
    });
});

function validateForm() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (username === '' || password === '' || confirmPassword === '') {
        alert('Por favor, preencha todos os campos.');
        return false;
    }
    
    if (password !== confirmPassword) {
        alert('As senhas não coincidem.');
        return false;
    }
    
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    const senhaCorreta = 'EJTechHouse';

    function verificarSenha(callback) {
        const senha = prompt('Digite a senha para acessar esta página:');
        if (senha === senhaCorreta) {
            callback();
        } else {
            alert('Senha incorreta. Tente novamente.');
        }
    }

    document.getElementById('cadastroBtn').addEventListener('click', function(event) {
        event.preventDefault();
        verificarSenha(() => {
            window.location.href = 'crud.php';
        });
    });

    document.getElementById('gerenciamentoBtn').addEventListener('click', function(event) {
        event.preventDefault();
        verificarSenha(() => {
            window.location.href = 'crud.php'; 
        });
    });
});