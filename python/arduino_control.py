import serial
import serial.tools.list_ports
import time
import pymysql
import os
from dotenv import load_dotenv

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
        possible_ports = serial.tools.list_ports.comports()
        
        for port in possible_ports:
            try:
                ser = serial.Serial(port.device, 9600, timeout=1)
                ser.close()
                return port.device
            except (serial.SerialException, OSError):
                continue
        return None
    
    def connect_to_arduino(self):
        if not self.arduino_port:
            self.arduino_port = self.find_arduino()
            
        if not self.arduino_port:
            return False
            
        try:
            self.serial_connection = serial.Serial(self.arduino_port, 9600, timeout=1)
            time.sleep(2) 
            return True
        except Exception as e:
            return False
    
    def get_light_id_by_name(self, light_name):
        try:
            connection = pymysql.connect(**self.db_config)
            with connection.cursor() as cursor:
                sql = "SELECT ID_Lampada as id FROM Lampadas WHERE Nome = %s"
                cursor.execute(sql, (light_name,))
                result = cursor.fetchone()
                return result['id'] if result else None
        except Exception as e:
            return None
        finally:
            if connection:
                connection.close()
    
    def read_light_status(self):
        try:
            with open(self.light_status_file, 'r') as f:
                return f.read().strip()
        except Exception as e:
            return None
    
    def control_light(self, light_name):        
        light_id = self.get_light_id_by_name(light_name)
        if not light_id:
            return False
            
        status_str = self.read_light_status()
        if status_str is None:
            return False
            
        light_status = status_str[light_id-1] if (light_id-1) < len(status_str) else '0'
        
        if not self.connect_to_arduino():
            return False
            
        try:
            command = f"LED{light_id}:{'ON' if light_status == '1' else 'OFF'}\n"
            self.serial_connection.write(command.encode())
            
            time.sleep(1)
            
            response = self.serial_connection.readline().decode().strip()
            
            if not response:
                return False
            
            return True
            
        except Exception as e:
            return False
        finally:
            if hasattr(self, 'serial_connection') and self.serial_connection:
                self.serial_connection.close()

if __name__ == "__main__":
    import sys
    import argparse
    import traceback
    
    parser = argparse.ArgumentParser(description='Controla lâmpadas via Arduino')
    parser.add_argument('--light-name', required=True, help='Nome da lâmpada a ser controlada')
    parser.add_argument('--status', required=True, choices=['ON', 'OFF'], help='Status da lâmpada (ON ou OFF)')
    parser.add_argument('--port', help='Porta serial do Arduino (ex: COM3)')
    args = parser.parse_args()
    
    try:
        controller = ArduinoController()
        
        if args.port:
            controller.arduino_port = args.port
        
        light_id = controller.get_light_id_by_name(args.light_name)
        if not light_id:
            sys.exit(1)
            
        try:
            with open(controller.light_status_file, 'r+') as f:
                status_list = list(f.read().strip())
                if len(status_list) < light_id:
                    status_list.extend(['0'] * (light_id - len(status_list)))
                status_list[light_id-1] = '1' if args.status == 'ON' else '0'
                f.seek(0)
                f.write(''.join(status_list))
                f.truncate()
        except Exception as e:
            sys.exit(1)
        
        if controller.control_light(args.light_name):
            sys.exit(0)
        else:
            sys.exit(1)
            
    except Exception as e:
        traceback.print_exc()
        sys.exit(1)
