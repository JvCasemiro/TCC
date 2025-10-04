import serial
import serial.tools.list_ports
import time
import pymysql
import os
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

class ArduinoController:
    def __init__(self):
        self.arduino_port = None
        self.serial_connection = None
        self.db_config = {
            'host': 'localhost',
            'user': 'root',
            'password': '',
            'database': 'tcc',
            'charset': 'utf8mb4',
            'cursorclass': pymysql.cursors.DictCursor
        }
        self.light_status_file = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'light_status.txt')
        
    def find_arduino(self):
        """Find and return the Arduino's serial port"""
        # Lista de portas para tentar se conectar
        possible_ports = ['COM3', 'COM4', 'COM5']
        
        # Verifica primeiro as portas conhecidas
        for port in possible_ports:
            try:
                # Tenta abrir a porta para verificar se é o Arduino
                ser = serial.Serial(port, 9600, timeout=1)
                ser.close()
                print(f"Arduino encontrado na porta {port}")
                return port
            except (serial.SerialException, OSError):
                continue
        
        # Se não encontrar nas portas conhecidas, tenta encontrar automaticamente
        ports = list(serial.tools.list_ports.comports())
        for port in ports:
            try:
                # Tenta abrir a porta para verificar se é o Arduino
                ser = serial.Serial(port.device, 9600, timeout=1)
                ser.close()
                print(f"Arduino encontrado na porta {port.device}")
                return port.device
            except (serial.SerialException, OSError):
                continue
                
        print("Arduino não encontrado nas portas COM3, COM4 ou COM5")
        return None
    
    def connect_to_arduino(self):
        """Connect to the Arduino"""
        if not self.arduino_port:
            self.arduino_port = self.find_arduino()
            
        if not self.arduino_port:
            print("Arduino not found!")
            return False
            
        try:
            self.serial_connection = serial.Serial(self.arduino_port, 9600, timeout=1)
            time.sleep(2)  # Wait for Arduino to reset
            return True
        except Exception as e:
            print(f"Error connecting to Arduino: {e}")
            return False
    
    def get_light_id_by_name(self, light_name):
        """Get light ID by name from the database"""
        try:
            connection = pymysql.connect(**self.db_config)
            with connection.cursor() as cursor:
                sql = "SELECT ID_Lampada as id FROM Lampadas WHERE Nome = %s"
                cursor.execute(sql, (light_name,))
                result = cursor.fetchone()
                return result['id'] if result else None
        except Exception as e:
            print(f"Database error: {e}")
            return None
        finally:
            if connection:
                connection.close()
    
    def read_light_status(self):
        """Read the light status from the status file"""
        try:
            with open(self.light_status_file, 'r') as f:
                return f.read().strip()
        except Exception as e:
            print(f"Error reading light status file: {e}")
            return None
    
    def control_light(self, light_name):
        """Control the light based on its name and status"""
        print(f"\nIniciando controle da lâmpada: {light_name}")
        
        # Get light ID from database
        light_id = self.get_light_id_by_name(light_name)
        if not light_id:
            print(f"[ERRO] Lâmpada com o nome '{light_name}' não encontrada no banco de dados.")
            return False
        print(f"ID da lâmpada encontrado: {light_id}")
            
        # Read light status
        status_str = self.read_light_status()
        if status_str is None:
            print("[ERRO] Não foi possível ler o status da lâmpada do arquivo.")
            return False
        print(f"Status lido do arquivo: {status_str}")
            
        # Get the status for this specific light (1=ON, 0=OFF)
        light_status = status_str[light_id-1] if (light_id-1) < len(status_str) else '0'
        print(f"Status da lâmpada {light_id}: {'LIGADA' if light_status == '1' else 'DESLIGADA'}")
        
        # Connect to Arduino
        print("Conectando ao Arduino...")
        if not self.connect_to_arduino():
            print("[ERRO] Falha ao conectar ao Arduino.")
            return False
            
        try:
            # Send command to Arduino in the format "LEDX:STATE" (e.g., "LED1:ON" or "LED3:OFF")
            command = f"LED{light_id}:{'ON' if light_status == '1' else 'OFF'}\n"
            print(f"Enviando comando para o Arduino: {command.strip()}")
            self.serial_connection.write(command.encode())
            
            # Wait for the Arduino to process the command
            time.sleep(1)
            
            # Wait for response (optional)
            response = self.serial_connection.readline().decode().strip()
            print(f"Resposta do Arduino: {response}")
            
            if not response:
                print("[AVISO] Nenhuma resposta do Arduino. Verifique a conexão.")
            
            print("Comando enviado com sucesso!")
            return True
            
        except Exception as e:
            print(f"Error controlling light: {e}")
            return False
        finally:
            if hasattr(self, 'serial_connection') and self.serial_connection:
                self.serial_connection.close()

if __name__ == "__main__":
    import sys
    import argparse
    import traceback
    
    # Configura o parser de argumentos
    parser = argparse.ArgumentParser(description='Controla lâmpadas via Arduino')
    parser.add_argument('--light-name', required=True, help='Nome da lâmpada a ser controlada')
    parser.add_argument('--status', required=True, choices=['ON', 'OFF'], help='Status da lâmpada (ON ou OFF)')
    parser.add_argument('--port', help='Porta serial do Arduino (ex: COM3)')
    
    # Analisa os argumentos
    args = parser.parse_args()
    
    print(f"Iniciando controle da lâmpada: {args.light_name}, Status: {args.status}")
    
    try:
        controller = ArduinoController()
        
        # Se uma porta foi especificada, tenta usá-la
        if args.port:
            print(f"Tentando conectar à porta especificada: {args.port}")
            controller.arduino_port = args.port
        
        # Primeiro, atualiza o status no arquivo light_status.txt
        light_id = controller.get_light_id_by_name(args.light_name)
        if not light_id:
            print(f"[ERRO] Lâmpada com o nome '{args.light_name}' não encontrada no banco de dados.")
            sys.exit(1)
            
        # Atualiza o status no arquivo
        try:
            with open(controller.light_status_file, 'r+') as f:
                status_list = list(f.read().strip())
                if len(status_list) < light_id:
                    status_list.extend(['0'] * (light_id - len(status_list)))
                status_list[light_id-1] = '1' if args.status == 'ON' else '0'
                f.seek(0)
                f.write(''.join(status_list))
                f.truncate()
                print(f"Status atualizado no arquivo: {''.join(status_list)}")
        except Exception as e:
            print(f"[ERRO] Falha ao atualizar o arquivo de status: {e}")
            sys.exit(1)
        
        # Agora controla o Arduino
        if controller.control_light(args.light_name):
            print(f"Lâmpada {args.light_name} {args.status} com sucesso!")
            sys.exit(0)
        else:
            print(f"[ERRO] Falha ao controlar a lâmpada: {args.light_name}")
            sys.exit(1)
            
    except Exception as e:
        print(f"[ERRO CRÍTICO] {str(e)}")
        traceback.print_exc()
        sys.exit(1)
