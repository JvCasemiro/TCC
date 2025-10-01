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
        self.port = 'COM9'
        try:
            self.serial_connection = serial.Serial(self.port, 9600, timeout=1)
            time.sleep(2)
            print(f"Connected to Arduino on {self.port}")
            return
        except Exception as e:
            print(f"Could not connect to {self.port}: {e}")
            self.port = None
        
        ports = list(serial.tools.list_ports.comports())
        for p in ports:
            if 'Arduino' in str(p.description) or 'CH340' in str(p.description):
                try:
                    self.port = p.device
                    self.serial_connection = serial.Serial(self.port, 9600, timeout=1)
                    time.sleep(2)
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

if __name__ == "__main__":
    print("This script is meant to be imported, not run directly.")
