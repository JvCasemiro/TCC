import serial
import serial.tools.list_ports
import time
import pymysql
import os
import atexit
from functools import lru_cache
from dotenv import load_dotenv

load_dotenv()

class ArduinoController:
    _instance = None
    _db_connection = None
    _light_cache = {}
    _last_light_status = None
    _last_light_status_time = 0
    CACHE_TTL = 5

    def __new__(cls):
        if cls._instance is None:
            cls._instance = super(ArduinoController, cls).__new__(cls)
            cls._instance._init_controller()
        return cls._instance
        
    def _init_controller(self):
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
        atexit.register(self.cleanup)
        
    def get_db_connection(self):
        if self._db_connection is None or not self._db_connection.open:
            self._db_connection = pymysql.connect(**self.db_config)
        return self._db_connection
        
    def cleanup(self):
        if self._db_connection and self._db_connection.open:
            self._db_connection.close()
        if self.serial_connection and self.serial_connection.is_open:
            self.serial_connection.close()
        
    def find_arduino(self):
        try:
            print("Procurando portas seriais disponíveis...")
            ports = serial.tools.list_ports.comports()
            
            for port in ports:
                print(f"Verificando porta: {port.device} - {port.description}")
                try:
                    ser = serial.Serial(
                        port=port.device,
                        baudrate=9600,
                        timeout=1,
                        write_timeout=1
                    )
                    time.sleep(2)
                    ser.reset_input_buffer()
                    ser.reset_output_buffer()
                    
                    test_command = "LED1:ON\n"
                    ser.write(test_command.encode())
                    time.sleep(0.5)
                    
                    if ser.in_waiting > 0:
                        response = ser.readline().decode().strip()
                        print(f"Resposta do Arduino: {response}")
                        if 'LIGADO' in response or 'DESLIGADO' in response:
                            ser.close()
                            return port.device
                    
                    ser.write(b"LED1:OFF\n")
                    ser.close()
                    
                except (serial.SerialException, OSError) as e:
                    print(f"Erro na porta {port.device}: {str(e)}")
                    continue
                    
        except Exception as e:
            print(f"Erro ao buscar portas seriais: {str(e)}")
            
        print("Nenhum Arduino compatível encontrado")
        return None
    
    def connect_to_arduino(self):
        if self.serial_connection and self.serial_connection.is_open:
            return True
            
        if not self.arduino_port:
            print("Procurando porta do Arduino...")
            self.arduino_port = self.find_arduino()
            if not self.arduino_port:
                print("Erro: Arduino não encontrado")
                return False
                
        try:
            print(f"Conectando ao Arduino na porta {self.arduino_port}...")
            self.serial_connection = serial.Serial(
                port=self.arduino_port,
                baudrate=9600,
                bytesize=serial.EIGHTBITS,
                parity=serial.PARITY_NONE,
                stopbits=serial.STOPBITS_ONE,
                timeout=1,
                write_timeout=1
            )
            time.sleep(2)
            self.serial_connection.reset_input_buffer()
            self.serial_connection.reset_output_buffer()
            print("Conexão com o Arduino estabelecida com sucesso")
            return True
        except (serial.SerialException, OSError) as e:
            print(f"Erro ao conectar ao Arduino: {str(e)}")
            self.serial_connection = None
            return False
    
    @lru_cache(maxsize=32)
    def get_light_id_by_name(self, light_name):
        try:
            with self.get_db_connection().cursor() as cursor:
                cursor.execute(
                    "SELECT ID_Lampada as id FROM Lampadas WHERE Nome = %s",
                    (light_name,)
                )
                result = cursor.fetchone()
                return result['id'] if result else None
        except Exception:
            return None
    
    def read_light_status(self):
        current_time = time.time()
        if (self._last_light_status is not None and 
            current_time - self._last_light_status_time < self.CACHE_TTL):
            return self._last_light_status
            
        try:
            with open(self.light_status_file, 'r') as f:
                self._last_light_status = f.read().strip()
                self._last_light_status_time = current_time
                return self._last_light_status
        except (IOError, OSError):
            return None
    
    def control_light(self, light_name):
        try:
            light_id = self.get_light_id_by_name(light_name)
            if not light_id:
                print(f"Erro: Lâmpada '{light_name}' não encontrada")
                return False
                
            status_str = self.read_light_status()
            if status_str is None:
                print("Erro: Não foi possível ler o status das lâmpadas")
                return False
                
            if not self.connect_to_arduino():
                print("Erro: Não foi possível conectar ao Arduino")
                return False
            
            self.serial_connection.reset_input_buffer()
            
            light_status = status_str[light_id-1] if (light_id-1) < len(status_str) else '0'
            command = f"LED{light_id}:{'ON' if light_status == '1' else 'OFF'}\n"
            print(f"Enviando comando: {command.strip()}")
            self.serial_connection.write(command.encode())
            self.serial_connection.flush()
            
            time.sleep(0.5)
            
            if self.serial_connection.in_waiting > 0:
                response = self.serial_connection.readline().decode().strip()
                print(f"Resposta do Arduino: {response}")
            
            for i in range(len(status_str)):
                led_num = i + 1
                if led_num != light_id:
                    led_status = status_str[i] if i < len(status_str) else '0'
                    command = f"LED{led_num}:{'ON' if led_status == '1' else 'OFF'}\n"
                    print(f"Atualizando LED {led_num}: {'LIGADO' if led_status == '1' else 'DESLIGADO'}")
                    self.serial_connection.write(command.encode())
                    self.serial_connection.flush()
                    time.sleep(0.1)  
            
            return True
            
        except (serial.SerialException, OSError, IOError):
            self.serial_connection = None
            return False

def update_light_status(controller, light_name, status):
    try:
        light_id = controller.get_light_id_by_name(light_name)
        if not light_id:
            return False
            
        with open(controller.light_status_file, 'r+') as f:
            status_list = list(f.read().strip())
            if len(status_list) < light_id:
                status_list.extend(['0'] * (light_id - len(status_list)))
            status_list[light_id-1] = '1' if status == 'ON' else '0'
            f.seek(0)
            f.write(''.join(status_list))
            f.truncate()
            
        controller._last_light_status = None
        return True
        
    except (IOError, OSError):
        return False

if __name__ == "__main__":
    import sys
    import argparse
    import traceback
    
    parser = argparse.ArgumentParser(description='Controla lâmpadas via Arduino')
    parser.add_argument('--light-name', required=True, help='Nome da lâmpada a ser controlada')
    parser.add_argument('--status', required=True, choices=['ON', 'OFF'], 
                       help='Status da lâmpada (ON ou OFF)')
    parser.add_argument('--port', help='Porta serial do Arduino (ex: COM3)')
    args = parser.parse_args()
    
    try:
        controller = ArduinoController()
        
        if args.port:
            controller.arduino_port = args.port
        
        if not update_light_status(controller, args.light_name, args.status):
            sys.exit(1)
        
        if controller.control_light(args.light_name):
            sys.exit(0)
        sys.exit(1)
            
    except Exception:
        traceback.print_exc()
        sys.exit(1)
