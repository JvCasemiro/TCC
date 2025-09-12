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
        # Return default OFF status if file doesn't exist
        return False

def main():
    
    arduino = ArduinoController()
    last_status = None
    
    try:
        while True:
            current_status = read_light_status()
            
            # Only send command if status changed
            if current_status != last_status:
                status_str = "ON" if current_status else "OFF"
                arduino.send_command(status_str)
                
                last_status = current_status
            
            # Check status every second
            time.sleep(1)
            
    except KeyboardInterrupt:
        print("\nStopping Light Controller Service...")
    finally:
        arduino.close()

if __name__ == "__main__":
    main()
