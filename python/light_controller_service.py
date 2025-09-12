import time
from datetime import datetime
from arduino_controller import ArduinoController
import os

def read_light_status():
    """Read the current light status from the status file"""
    status_file = os.path.join(os.path.dirname(__file__), '..', 'light_status.txt')
    try:
        with open(status_file, 'r') as f:
            return f.read().strip() == 'ON'
    except FileNotFoundError:
        # If file doesn't exist, create it with default OFF status
        with open(status_file, 'w') as f:
            f.write('OFF')
        return False

def main():
    print("Starting Light Controller Service...")
    print("Press Ctrl+C to stop")
    
    arduino = ArduinoController()
    last_status = None
    
    try:
        while True:
            current_status = read_light_status()
            
            # Only send command if status changed
            if current_status != last_status:
                status_str = "ON" if current_status else "OFF"
                print(f"[{datetime.now()}] Light status changed to: {status_str}")
                
                if arduino.send_command(status_str):
                    print(f"  -> Command sent to Arduino: {status_str}")
                else:
                    print("  -> Failed to send command to Arduino")
                
                last_status = current_status
            
            # Check status every second
            time.sleep(1)
            
    except KeyboardInterrupt:
        print("\nStopping Light Controller Service...")
    finally:
        arduino.close()

if __name__ == "__main__":
    main()
