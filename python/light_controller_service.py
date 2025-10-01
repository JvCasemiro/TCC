import sys
sys.dont_write_bytecode = True

import time
from arduino_controller import ArduinoController
import os

def read_light_status():
    """Read the current light status from the status file"""
    status_file = os.path.join(os.path.dirname(__file__), '..', 'light_status.txt')
    try:
        with open(status_file, 'r') as f:
            return f.read().strip() == 'ON'
    except FileNotFoundError:
        return False

def main():
    
    arduino = ArduinoController()
    last_status = None
    
    try:
        while True:
            current_status = read_light_status()
            
            if current_status != last_status:
                status_str = "ON" if current_status else "OFF"
                arduino.send_command(status_str)
                
                last_status = current_status
            
            time.sleep(1)
            
    except KeyboardInterrupt:
        print("\nStopping Light Controller Service...")
    finally:
        arduino.close()

if __name__ == "__main__":
    main()
