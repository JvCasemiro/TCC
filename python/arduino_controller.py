import serial
import serial.tools.list_ports
import time
import os
from datetime import datetime

class ArduinoController:
    def __init__(self):
        self.port = None
        self.serial_connection = None
        self.find_arduino()
    
    def find_arduino(self):
        """Find and connect to Arduino"""
        # First try COM9 specifically (your Arduino port)
        self.port = 'COM9'
        try:
            self.serial_connection = serial.Serial(self.port, 9600, timeout=1)
            time.sleep(2)  # Wait for Arduino to reset
            print(f"Connected to Arduino on {self.port}")
            return
        except Exception as e:
            print(f"Could not connect to {self.port}: {e}")
            self.port = None
        
        # If COM9 fails, try to find Arduino automatically
        ports = list(serial.tools.list_ports.comports())
        for p in ports:
            if 'Arduino' in str(p.description) or 'CH340' in str(p.description):
                try:
                    self.port = p.device
                    self.serial_connection = serial.Serial(self.port, 9600, timeout=1)
                    time.sleep(2)  # Wait for Arduino to reset
                    print(f"Connected to Arduino on {self.port}")
                    return
                except Exception as e:
                    print(f"Error connecting to {p.device}: {e}")
        
        print("Arduino not found. Please check the connection and try again.")
        print("Make sure the Arduino IDE is closed and the board is properly connected.")
    
    def send_command(self, command):
        """Send a command to Arduino"""
        if self.serial_connection and self.serial_connection.is_open:
            try:
                self.serial_connection.write(f"{command}\n".encode())
                response = self.serial_connection.readline().decode().strip()
                print(f"Arduino: {response}")
                return True
            except Exception as e:
                print(f"Error sending command: {e}")
                return False
        else:
            print("Not connected to Arduino")
            return False
    
    def close(self):
        """Close the serial connection"""
        if self.serial_connection and self.serial_connection.is_open:
            self.serial_connection.close()
            print("Serial connection closed")

def check_light_status():
    """Check the light status from the database or file"""
    # In a real implementation, you would query the database here
    # For this example, we'll use a simple file to store the status
    status_file = 'light_status.txt'
    try:
        with open(status_file, 'r') as f:
            return f.read().strip() == 'ON'
    except FileNotFoundError:
        return False

def update_light_status(status):
    """Update the light status in the database or file"""
    status_file = 'light_status.txt'
    with open(status_file, 'w') as f:
        f.write('ON' if status else 'OFF')

if __name__ == "__main__":
    controller = ArduinoController()
    
    try:
        while True:
            # This would be replaced with your actual web interface logic
            # For now, we'll just toggle the light every 5 seconds as an example
            current_status = check_light_status()
            new_status = not current_status
            
            if new_status:
                print(f"[{datetime.now()}] Turning light ON")
                controller.send_command("ON")
            else:
                print(f"[{datetime.now()}] Turning light OFF")
                controller.send_command("OFF")
            
            update_light_status(new_status)
            time.sleep(5)  # Check every 5 seconds
            
    except KeyboardInterrupt:
        print("\nExiting...")
    finally:
        controller.close()
