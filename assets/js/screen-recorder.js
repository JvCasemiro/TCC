let mediaRecorder;
let recordedChunks = [];
let isRecording = false;

async function startScreenRecording() {
    try {
        // Tenta obter a stream da câmera
        const cameraStream = await navigator.mediaDevices.getUserMedia({ 
            video: {
                width: { ideal: 1280 },
                height: { ideal: 720 },
                facingMode: 'environment'
            },
            audio: true
        });
        
        if (!cameraStream) {
            throw new Error('Não foi possível acessar a câmera');
        }
        
        // Cria um elemento de vídeo para exibir a câmera
        const videoPreview = document.createElement('video');
        videoPreview.srcObject = cameraStream;
        videoPreview.autoplay = true;
        videoPreview.style.position = 'fixed';
        videoPreview.style.top = '0';
        videoPreview.style.left = '0';
        videoPreview.style.width = '1px';
        videoPreview.style.height = '1px';
        videoPreview.style.opacity = '0';
        videoPreview.style.pointerEvents = 'none';
        document.body.appendChild(videoPreview);
        
        // Remove o vídeo quando a gravação for parada
        const originalStop = window.ScreenRecorder.stop;
        window.ScreenRecorder.stop = async function() {
            const result = await originalStop.apply(this, arguments);
            if (videoPreview.parentNode) {
                videoPreview.pause();
                videoPreview.srcObject = null;
                videoPreview.parentNode.removeChild(videoPreview);
            }
            return result;
        };

        // Cria o MediaRecorder com a stream da câmera
        mediaRecorder = new MediaRecorder(cameraStream, {
            mimeType: 'video/webm;codecs=vp8,opus',
            videoBitsPerSecond: 2500000 // 2.5Mbps
        });

        // Armazena os chunks de dados da gravação
        recordedChunks = [];
        mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                recordedChunks.push(event.data);
            }
        };

        // Quando a gravação for parada, salva o arquivo
        mediaRecorder.onstop = () => {
            const blob = new Blob(recordedChunks, {
                type: 'video/webm'
            });
            
            // Cria um link para download
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            a.href = url;
            a.download = `gravacao-${timestamp}.webm`;
            
            // Envia o arquivo para o servidor
            const formData = new FormData();
            formData.append('video', blob, `gravacao-${timestamp}.webm`);
            
            fetch('../salvar_gravacao.php', {
                method: 'POST',
                body: formData
            }).catch(error => {
                console.error('Erro:', error);
                // Se der erro no upload, faz o download local
                a.click();
            }).finally(() => {
                URL.revokeObjectURL(url);
            });

            // Para todas as tracks
            combinedStream.getTracks().forEach(track => track.stop());
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
            }
        };

        // Inicia a gravação
        mediaRecorder.start(1000); // Coleta dados a cada 1 segundo
        isRecording = true;
        
        // Atualiza a interface
        const recordBtn = document.querySelector('.btn-record');
        if (recordBtn) {
            recordBtn.classList.add('recording');
            recordBtn.innerHTML = '<i class="fas fa-stop"></i> Parar';
            const recordIndicator = document.querySelector('.recording-indicator');
            if (recordIndicator) {
                recordIndicator.style.display = 'inline-block';
            }
        }
        
    } catch (err) {
        return;
    }
}

function stopScreenRecording() {
    if (mediaRecorder && isRecording) {
        mediaRecorder.stop();
        isRecording = false;
        
        // Atualiza a interface
        const recordBtn = document.querySelector('.btn-record');
        if (recordBtn) {
            recordBtn.classList.remove('recording');
            recordBtn.innerHTML = '<i class="fas fa-record-vinyl"></i> Gravar';
            const recordIndicator = document.querySelector('.recording-indicator');
            if (recordIndicator) {
                recordIndicator.style.display = 'none';
            }
        }
    }
}

// Exporta as funções para uso global
window.ScreenRecorder = {
    start: startScreenRecording,
    stop: stopScreenRecording
};
