// Iniciar a câmera quando a página carregar
document.addEventListener('DOMContentLoaded', async function() {
    // Verificar se estamos na página de monitoramento
    if (!window.location.pathname.includes('monitorar_seguranca.php')) {
        return;
    }

    // Iniciar a câmera automaticamente
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

        // Tornar o stream global para ser acessado pelo botão Visualizar
        window.cameraStream = stream;
        
    } catch (err) {
        console.error('Erro ao acessar a câmera:', err);
    }
});

// Função para visualizar a câmera em uma nova janela
function viewCamera(cameraId) {
    if (!window.cameraStream) {
        console.error('Nenhum stream de câmera disponível');
        return;
    }

    const cameraCard = document.querySelector(`[data-camera-id="${cameraId}"]`);
    const cameraName = cameraCard ? (cameraCard.querySelector('h4')?.textContent || `Câmera ${cameraId}`) : `Câmera ${cameraId}`;

    const width = window.screen.width * 0.9;
    const height = window.screen.height * 0.9;
    const left = (window.screen.width - width) / 2;
    const top = (window.screen.height - height) / 2;
    
    const features = `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`;
    
    // Abrir nova janela
    const popup = window.open('', `camera_${cameraId}`, features);
    
    // HTML da nova janela
    popup.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${cameraName} - Visualização</title>
            <style>
                body, html {
                    margin: 0;
                    padding: 0;
                    height: 100%;
                    overflow: hidden;
                    background-color: #000;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                video {
                    max-width: 100%;
                    max-height: 100%;
                }
            </style>
        </head>
        <body>
            <video id="camera-feed" autoplay playsinline></video>
            <script>
                // Tentar acessar a câmera diretamente na nova janela
                (async function() {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ 
                            video: { 
                                width: { ideal: 1280 },
                                height: { ideal: 720 },
                                facingMode: 'environment' 
                            } 
                        });
                        
                        const video = document.getElementById('camera-feed');
                        video.srcObject = stream;
                        await video.play();
                    } catch (err) {
                        console.error('Erro ao acessar a câmera na nova janela:', err);
                        document.body.innerHTML = '<div style="color: white; text-align: center; padding: 20px;">Erro ao acessar a câmera. Por favor, verifique as permissões.</div>';
                    }
                })();
            <\/script>
        </body>
        </html>
    `);
    popup.document.close();
}
