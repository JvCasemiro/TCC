// Função para iniciar a câmera
async function startCamera() {
    try {
        const video = document.getElementById('camera-feed');
        if (!video) {
            console.error('Elemento de vídeo não encontrado');
            return;
        }

        // Obter acesso à câmera
        const stream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                width: { ideal: 1280 },
                height: { ideal: 720 },
                facingMode: 'environment' 
            } 
        });
        
        // Exibir o stream no elemento de vídeo
        video.srcObject = stream;
        await video.play();
        
        // Esconder o placeholder quando o vídeo começar a tocar
        const placeholder = document.querySelector('.feed-placeholder');
        if (placeholder) {
            placeholder.style.display = 'none';
        }
        
        return stream;
    } catch (err) {
        console.error('Erro ao acessar a câmera:', err);
        throw err;
    }
}

// Função para parar a câmera
function stopCamera() {
    const video = document.getElementById('camera-feed');
    if (video && video.srcObject) {
        const tracks = video.srcObject.getTracks();
        tracks.forEach(track => track.stop());
        video.srcObject = null;
    }
}

// Iniciar a câmera quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se estamos na página de monitoramento
    if (window.location.pathname.includes('monitorar_seguranca.php')) {
        // Iniciar a câmera automaticamente
        startCamera().catch(console.error);
    }
});

// Parar a câmera quando a página for descarregada
window.addEventListener('beforeunload', function() {
    stopCamera();
});
