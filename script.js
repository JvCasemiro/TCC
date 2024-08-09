function validateForm() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    if (username === '' || password === '') {
        alert('Por favor, preencha todos os campos.');
        return false;
    }
    
    return true;
}
