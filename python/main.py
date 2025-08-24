import cv2
import pytesseract
import pyodbc
import numpy as np
import re
import os
from datetime import datetime

# Configuração do Tesseract
pytesseract.pytesseract.tesseract_cmd = r"C:\Program Files\Tesseract-OCR\tesseract.exe"

# Função para conectar ao banco de dados
def conectar_banco():
    try:
        import mysql.connector
        conn = mysql.connector.connect(
            host='localhost',
            database='tcc',
            user='root',
            password='',
            charset='utf8mb4'
        )
        return conn
    except mysql.connector.Error as err:
        print(f"Erro ao conectar ao banco de dados: {err}")
        return None

# Função para validar as placas
# Função para validar as placas
def validar_placa(placa):
    placa = placa.upper().strip()  # Garante que a placa esteja em maiúsculas
    placa = re.sub(r'\s+', '', placa)  # Remove qualquer espaço ou quebra de linha

    regex_placa = re.compile(r'''
    (?:(?:[A-Z]{3}\d[A-Z]\d{2})|    # Formato de placa antigo (ABC1A23)
    (?:[A-Z]{3}-?\d{4})|            # Formato de placa antiga (ABC-1234)
    (?:[A-Z]{2}\d[A-Z]\d{2})|       # Formato de placa novo (AB1A23)
    (?:[A-Z]{2}-?\d{4})|            # Formato de placa novo (AB-1234)
    (?:[A-Z]{3}\d{4})|              # Formato de placa antigo (ABC1234)
    (?:[A-Z]{2}\d{3}\d))            # Formato de placa nova (AB12345)
''', re.VERBOSE | re.MULTILINE)

    if regex_placa.match(placa):
        return placa
    else:
        return None

# Função para verificar o acesso
def verificar_acesso(numero_placa):
    numero_placa = validar_placa(numero_placa)
    if not numero_placa:
        return False, "Formato de placa inválido"
        
    conn = None
    try:
        conn = conectar_banco()
        if not conn:
            return False, "Erro ao conectar ao banco de dados"
            
        cursor = conn.cursor(dictionary=True)
        
        # Formata a placa para o padrão do banco (ex: ABC-1234)
        placa_formatada = f"{numero_placa[:3]}-{numero_placa[3:7]}" if len(numero_placa) == 7 else numero_placa
        
        # Verifica se a placa existe no banco
        query = "SELECT * FROM Placas WHERE Numeracao = %s"
        cursor.execute(query, (placa_formatada,))
        placa = cursor.fetchone()
        
        if placa:
            # Atualiza o último acesso
            update_query = "UPDATE Placas SET Ultimo_Acesso = NOW() WHERE ID_Placa = %s"
            cursor.execute(update_query, (placa['ID_Placa'],))
            conn.commit()
            return True, "Acesso autorizado"
        else:
            return False, "Placa não autorizada"
            
    except Exception as e:
        print(f"Erro ao verificar acesso: {e}")
        return False, f"Erro ao verificar acesso: {str(e)}"
        
    finally:
        if conn and conn.is_connected():
            cursor.close()
            conn.close()

# Função para processar a placa da imagem
def processar_placa(img_roi, frame):
    # Redimensiona a imagem para melhorar a precisão do OCR
    resize_img_roi = cv2.resize(img_roi, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)  # Usando 2x de aumento
    img_cinza = cv2.cvtColor(resize_img_roi, cv2.COLOR_BGR2GRAY)

    # Aplicar CLAHE para melhorar o contraste da imagem
    clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))  # Ajuste do CLAHE para um contraste mais suave
    img_contraste = clahe.apply(img_cinza)

    # Binarização com Threshold adaptativo para melhor separação de objetos
    _, img_bin = cv2.threshold(img_contraste, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)

    # Aplicar filtro de mediana para reduzir ruídos
    img_median = cv2.medianBlur(img_bin, 3)

    # Aplique Morphological Transform para limpar a imagem
    kernel = np.ones((3, 3), np.uint8)
    img_morph = cv2.morphologyEx(img_median, cv2.MORPH_CLOSE, kernel, iterations=1)

    # Salva a imagem binarizada
    if not os.path.exists("output"):
        os.makedirs("output")
    cv2.imwrite("output/placa_binarizada.png", img_morph)

    # Realizar OCR na imagem binarizada com filtragem para negrito
    config = r'-c tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 --psm 6'
    saida = pytesseract.image_to_string(img_morph, lang='eng', config=config).strip()

    # Limpar quebras de linha e espaços extras
    saida = re.sub(r'\s+', '', saida)  # Remove espaços e quebras de linha
    placa_detectada = saida.upper()

    # Usar regex para capturar apenas a numeração da placa
    regex_numero_placa = re.compile(r'''
    (?:(?:[A-Z]{3}\d[A-Z]\d{2})| 
    (?:[A-Z]{3}-?\d{4})|
    (?:[A-Z]{2}\d[A-Z]\d{2})|
    (?:[A-Z]{2}-?\d{4})|
    (?:[A-Z]{3}\d{4})|
    (?:[A-Z]{2}\d{3}\d))
''', re.VERBOSE | re.MULTILINE)
    match = re.search(regex_numero_placa, placa_detectada)

    if match:
        placa_detectada = match.group(0)
        print(f"Placa detectada: {placa_detectada}")
    else:
        placa_detectada = None
        print("Não foi possível detectar a numeração da placa.")

    # Verificar se a placa é válida e tem acesso
    if placa_detectada:
        acesso_permitido, mensagem = verificar_acesso(placa_detectada)
        print(f"Placa: {placa_detectada} - {mensagem}")
        if acesso_permitido:
            return placa_detectada
    return None

# Função para processar a imagem da câmera em tempo real
def processar_camera():
    cap = cv2.VideoCapture(0)  # Inicializa a câmera (câmera padrão)

    if not cap.isOpened():
        print("Erro ao acessar a câmera!")
        return

    while True:
        # Captura um frame da câmera
        ret, frame = cap.read()
        
        if not ret:
            print("Erro ao capturar imagem!")
            break
        
        # Aqui você pode ajustar a região de interesse (ROI) se necessário
        img_roi = frame  # Usamos o frame inteiro, mas você pode aplicar uma máscara ou selecionar uma área específica.

        placa = processar_placa(img_roi, frame)

        if placa:
            mostrar_mensagem("PORTÃO ABRINDO", (0, 255, 0))  # Exibe a mensagem de sucesso em verde
            break  # Para o programa após detectar a placa
        else:
            mostrar_mensagem("Acesso Negado", (0, 0, 255))  # Exibe a mensagem de erro em vermelho
        
        # Mostra a imagem binarizada
        cv2.imshow("Imagem Binarizada", cv2.imread("output/placa_binarizada.png"))

        # Mostra a imagem da câmera
        cv2.imshow("Captura de Placa", frame)

        # Sai do loop se pressionar 'q'
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    cap.release()
    cv2.destroyAllWindows()

# Função para exibir a mensagem na tela
def mostrar_mensagem(mensagem, cor):
    fonte = cv2.FONT_HERSHEY_SIMPLEX
    img_mensagem = np.zeros((300, 600, 3), dtype=np.uint8)  # Cria uma imagem para mostrar a mensagem
    cv2.putText(img_mensagem, mensagem, (50, 150), fonte, 1, cor, 2, cv2.LINE_AA)
    cv2.imshow("Mensagem", img_mensagem)

# Função principal
if __name__ == "__main__":
    processar_camera()  # Processa a câmera em tempo real
