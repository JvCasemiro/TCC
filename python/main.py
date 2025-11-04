import cv2
import pytesseract
import pyodbc
import numpy as np
import re
import os
from datetime import datetime

# pytesseract.pytesseract.tesseract_cmd = r"C:\Program Files\Tesseract-OCR\tesseract.exe" # --> Não apagar esse comentário

# pytesseract.pytesseract.tesseract_cmd = r"C:\Users\Alunos.DESKTOP-8SLHJJ7\AppData\Local\Programs\Tesseract-OCR\tesseract.exe" # Não apagar esse comentário

pytesseract.pytesseract.tesseract_cmd = r"/home3/lisianth/virtualenv/domx.lisianthus.com.br/python/3.9/lib/python3.9/site-packages/tesseract"
# --> Caminho do servidor

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

def validar_placa(placa):
    placa = placa.upper().strip()
    placa = re.sub(r'\s+', '', placa)

    regex_placa = re.compile(r'''
    (?:(?:[A-Z]{3}\d[A-Z]\d{2})|
    (?:[A-Z]{3}-?\d{4})|
    (?:[A-Z]{2}\d[A-Z]\d{2})|
    (?:[A-Z]{2}-?\d{4})|
    (?:[A-Z]{3}\d{4})|
    (?:[A-Z]{2}\d{3}\d))
''', re.VERBOSE | re.MULTILINE)

    if regex_placa.match(placa):
        return placa
    else:
        return None

def verificar_acesso(numero_placa):
    if not numero_placa:
        return False, "Placa inválida"
    
    placa_limpa = re.sub(r'[^A-Z0-9]', '', numero_placa.upper())
    
    conn = None
    try:
        conn = conectar_banco()
        if not conn:
            return False, "Erro ao conectar ao banco de dados"
            
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("SELECT ID_Placa, Numeracao FROM Placas")
        placas = cursor.fetchall()
        
        for placa in placas:
            placa_banco_limpa = re.sub(r'[^A-Z0-9]', '', placa['Numeracao'].upper())
            if placa_banco_limpa == placa_limpa:
                update_query = "UPDATE Placas SET Ultimo_Acesso = NOW() WHERE ID_Placa = %s"
                cursor.execute(update_query, (placa['ID_Placa'],))
                conn.commit()
                return True, "Acesso autorizado"
                
        return False, "Placa não autorizada"
            
    except Exception as e:
        print(f"Erro ao verificar acesso: {e}")
        return False, f"Erro ao verificar acesso: {str(e)}"
        
    finally:
        if conn and conn.is_connected():
            cursor.close()
            conn.close()

def processar_placa(img_roi, frame):
    resize_img_roi = cv2.resize(img_roi, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)
    img_cinza = cv2.cvtColor(resize_img_roi, cv2.COLOR_BGR2GRAY)

    clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
    img_contraste = clahe.apply(img_cinza)

    _, img_bin = cv2.threshold(img_contraste, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    img_median = cv2.medianBlur(img_bin, 3)

    kernel = np.ones((3, 3), np.uint8)
    img_morph = cv2.morphologyEx(img_median, cv2.MORPH_CLOSE, kernel, iterations=1)

    config = r'-c tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 --psm 6'
    saida = pytesseract.image_to_string(img_morph, lang='eng', config=config).strip()
    saida = re.sub(r'\s+', '', saida)
    placa_detectada = saida.upper()

    if not validar_placa(placa_detectada):
        print(f"Placa detectada em formato inválido: {placa_detectada}")
        return None

    print(f"Placa detectada: {placa_detectada}")
    
    acesso_permitido, mensagem = verificar_acesso(placa_detectada)
    print(f"Verificação de acesso: {placa_detectada} - {mensagem}")
    
    if acesso_permitido:
        output_dir = os.path.join(os.path.dirname(__file__), 'output')
        os.makedirs(output_dir, exist_ok=True)
        
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"placa_{placa_detectada}_{timestamp}.png"
        output_path = os.path.join(output_dir, filename)
        
        cv2.imwrite(output_path, img_roi)
        print(f"Imagem da placa salva em: {output_path}")
        
        return placa_detectada
    return None

def verificar_parada():
    """Verifica se o arquivo de parada foi criado"""
    stop_file = os.path.join(os.path.dirname(__file__), 'stop_detection.flag')
    if os.path.exists(stop_file):
        try:
            os.remove(stop_file)
            return True
        except:
            pass
    return False

def processar_camera():
    stop_file = os.path.join(os.path.dirname(__file__), 'stop_detection.flag')
    if os.path.exists(stop_file):
        try:
            os.remove(stop_file)
        except:
            pass
            
    cap = cv2.VideoCapture(0)

    if not cap.isOpened():
        print("Erro ao acessar a câmera!")
        return

    while True:
        ret, frame = cap.read()
        
        if not ret:
            print("Erro ao capturar imagem!")
            break
        
        img_roi = frame

        placa = processar_placa(img_roi, frame)

        if placa:
            mostrar_mensagem("PORTÃO ABRINDO", (0, 255, 0))
            cv2.waitKey(3000)
            break
        else:
            mostrar_mensagem("Acesso Negado", (0, 0, 255))
        
        cv2.imshow("Captura de Placa", frame)

        if cv2.waitKey(1) & 0xFF == ord('q') or verificar_parada():
            break

    cap.release()
    cv2.destroyAllWindows()

def mostrar_mensagem(mensagem, cor):
    pass

if __name__ == "__main__":
    processar_camera()
