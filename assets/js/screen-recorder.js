let mediaRecorder;
let recordedChunks = [];
let isRecording = false;

async function startScreenRecording() {
    try {
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

        mediaRecorder = new MediaRecorder(cameraStream, {
            mimeType: 'video/webm;codecs=vp8,opus',
            videoBitsPerSecond: 2500000 
        });

        recordedChunks = [];
        mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                recordedChunks.push(event.data);
            }
        };

        mediaRecorder.onstop = () => {
            const blob = new Blob(recordedChunks, {
                type: 'video/webm'
            });
            
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            a.href = url;
            a.download = `gravacao-${timestamp}.webm`;
            
            const formData = new FormData();
            formData.append('video', blob, `gravacao-${timestamp}.webm`);
            
            fetch('../includes/salvar_gravacao.php', {
                method: 'POST',
                body: formData
            }).catch(error => {
                console.error('Erro:', error);
                a.click();
            }).finally(() => {
                URL.revokeObjectURL(url);
            });

            combinedStream.getTracks().forEach(track => track.stop());
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
            }
        };

        mediaRecorder.start(1000); 
        isRecording = true;
        
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

window.ScreenRecorder = {
    start: startScreenRecording,
    stop: stopScreenRecording
};
