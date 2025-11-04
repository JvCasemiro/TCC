<?php
// Arquivo temporário para armazenar o código corrigido
?>

<script>
// Código JavaScript corrigido
const deleteZone = async (zoneId, button) => {
    try {
        const response = await fetch('../includes/delete_zone.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: zoneId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const card = button.closest('.temp-zone-card');
            if (card) {
                card.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    card.remove();
                    if (document.querySelectorAll('.temp-zone-card').length === 0) {
                        window.location.reload();
                    }
                }, 300);
            }
        } else {
            throw new Error(result.message || 'Erro ao remover a zona');
        }
    } catch (error) {
        console.error('Erro ao remover zona:', error);
        showToast(error.message || 'Erro ao remover a zona', 'error');
    }
};

// Função para substituir o código existente
function applyFix() {
    const codeToReplace = `const deleteResponse = await fetch(\`../includes/delete_zone.php?id=\${zoneId}\`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });`;
    
    const newCode = `// Código atualizado para enviar o ID no corpo da requisição
                        const deleteResponse = await fetch('../includes/delete_zone.php', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ id: zoneId })
                        });`;
    
    // Atualiza o código na página
    const scripts = document.getElementsByTagName('script');
    for (let script of scripts) {
        if (script.textContent.includes(codeToReplace)) {
            script.textContent = script.textContent.replace(codeToReplace, newCode);
            console.log('Código atualizado com sucesso!');
            return true;
        }
    }
    console.log('Não foi possível encontrar o código para substituir');
    return false;
}

// Aplica a correção quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', applyFix);
</script>
