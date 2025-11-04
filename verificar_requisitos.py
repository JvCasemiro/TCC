import sys
import os
from datetime import datetime

def verificar_instalacao(pacote):
    try:
        __import__(pacote)
        return "✅ Instalado"
    except ImportError:
        return "❌ Não instalado"

def executar_comando(comando):
    try:
        # Tenta importar subprocess de forma segura
        try:
            import subprocess
            resultado = subprocess.run(
                comando,
                shell=True,
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                universal_newlines=True
            )
            return resultado.stdout.strip() or "Sem saída"
        except:
            # Se subprocess falhar, tenta métodos alternativos
            if 'which' in comando:
                return "Comando 'which' não disponível"
            if 'tesseract' in comando:
                return "Tesseract não encontrado"
            return "Não foi possível executar o comando"
    except Exception as e:
        return f"Erro: {str(e)}"

def verificar_cv2():
    try:
        import cv2
        return f"✅ Instalado (v{cv2.__version__})"
    except ImportError:
        return "❌ Não instalado"
    except Exception as e:
        return f"❌ Erro: {str(e)}"

def verificar_camera():
    try:
        import cv2
        return "✅ OpenCV importado com sucesso (não é possível verificar câmera neste ambiente)"
    except ImportError:
        return "❌ OpenCV não instalado"
    except Exception as e:
        return f"❌ Erro ao verificar câmera: {str(e)}"

def main():
    print("\n=== Verificação de Requisitos do Sistema ===")
    print(f"Data/Hora: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"Sistema Operacional: {sys.platform}")
    print(f"Python: {sys.version.split()[0]}")

    print("\n=== Dependências do Python ===")
    print(f"OpenCV: {verificar_cv2()}")
    print(f"Pytesseract: {verificar_instalacao('pytesseract')}")

    print("\n=== Verificando Tesseract OCR ===")
    print("Caminho do Tesseract:", executar_comando("which tesseract || echo 'Não encontrado'"))
    print("Versão do Tesseract:", executar_comando("tesseract --version 2>&1 || echo 'Tesseract não encontrado'"))

    print("\n=== Verificando Câmera ===")
    print(verificar_camera())

    print("\n=== Verificando Permissões ===")
    diretorio_atual = os.path.dirname(os.path.abspath(__file__))
    print(f"Diretório atual: {diretorio_atual}")
    try:
        print(f"Permissões: {oct(os.stat(diretorio_atual).st_mode & 0o777)}")
    except:
        print("Não foi possível verificar permissões do diretório")
    
    print("\n=== Verificação Concluída ===\n")

if __name__ == "__main__":
    main()