import serial
import serial.tools.list_ports
import time
import json
import os
import sys
from pathlib import Path

class ArduinoDaemon:
    def __init__(self):
        self.arduino_port = None
        self.serial_connection = None
        self.base_dir = Path(__file__).parent.parent
        self.queue_file = self.base_dir / 'arduino_queue.json'
        self.status_file = self.base_dir / 'light_status.txt'
        self.pid_file = self.base_dir / 'arduino_daemon.pid'
        self.running = True
        
        # Criar arquivo de fila se não existir
        if not self.queue_file.exists():
            self.queue_file.write_text('[]')
        
        # Salvar PID do processo
        self.pid_file.write_text(str(os.getpid()))
        
    def find_arduino(self):
        """Procura e retorna a porta do Arduino"""
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
                
                # Testar se é Arduino
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
        
        print("Nenhum Arduino compatível encontrado")
        return None
    
    def connect_to_arduino(self):
        """Conecta ao Arduino e mantém a conexão aberta"""
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
            # Prevenir reset do Arduino
            self.serial_connection.dtr = False
            time.sleep(2)
            self.serial_connection.reset_input_buffer()
            self.serial_connection.reset_output_buffer()
            print("Conexão com o Arduino estabelecida com sucesso")
            
            # Sincronizar todos os LEDs com o estado atual
            self._sync_all_lights()
            
            return True
        except (serial.SerialException, OSError) as e:
            print(f"Erro ao conectar ao Arduino: {str(e)}")
            self.serial_connection = None
            return False
    
    def _sync_all_lights(self):
        """Sincroniza o estado de todas as lâmpadas ao conectar"""
        try:
            if not self.status_file.exists():
                return
            
            status_str = self.status_file.read_text().strip()
            print(f"Sincronizando estado de todas as lâmpadas: {status_str}")
            
            for i in range(len(status_str)):
                led_num = i + 1
                led_status = status_str[i]
                command = f"LED{led_num}:{'ON' if led_status == '1' else 'OFF'}\n"
                self.serial_connection.write(command.encode())
                self.serial_connection.flush()
                time.sleep(0.1)
            
            print("Sincronização concluída")
        except Exception as e:
            print(f"Erro ao sincronizar lâmpadas: {str(e)}")
    
    def read_queue(self):
        """Lê comandos da fila"""
        try:
            with open(self.queue_file, 'r') as f:
                queue = json.load(f)
            return queue
        except (IOError, json.JSONDecodeError):
            return []
    
    def write_queue(self, queue):
        """Escreve a fila atualizada"""
        try:
            with open(self.queue_file, 'w') as f:
                json.dump(queue, f)
            return True
        except IOError:
            return False
    
    def process_command(self, command):
        """Processa um comando individual"""
        try:
            light_id = command.get('light_id')
            status = command.get('status')
            
            if not light_id or not status:
                print(f"Comando inválido: {command}")
                return False
            
            # Ler status atual
            status_str = self.status_file.read_text().strip() if self.status_file.exists() else ''
            status_list = list(status_str)
            
            # Garantir que a lista tenha o tamanho adequado
            if len(status_list) < light_id:
                status_list.extend(['0'] * (light_id - len(status_list)))
            
            # Atualizar status
            status_list[light_id - 1] = '1' if status == 'ON' else '0'
            new_status = ''.join(status_list)
            self.status_file.write_text(new_status)
            
            # Enviar comando para Arduino
            arduino_command = f"LED{light_id}:{'ON' if status == 'ON' else 'OFF'}\n"
            print(f"Enviando comando: {arduino_command.strip()}")
            
            self.serial_connection.reset_input_buffer()
            self.serial_connection.write(arduino_command.encode())
            self.serial_connection.flush()
            
            time.sleep(0.3)
            
            # Ler resposta
            if self.serial_connection.in_waiting > 0:
                response = self.serial_connection.readline().decode().strip()
                print(f"Resposta do Arduino: {response}")
            
            return True
            
        except Exception as e:
            print(f"Erro ao processar comando: {str(e)}")
            return False
    
    def run(self):
        """Loop principal do daemon"""
        print("=" * 50)
        print("Arduino Daemon iniciado")
        print("=" * 50)
        
        # Conectar ao Arduino
        if not self.connect_to_arduino():
            print("Falha ao conectar ao Arduino. Encerrando...")
            return
        
        print("Aguardando comandos na fila...")
        print("Pressione Ctrl+C para encerrar")
        print("=" * 50)
        
        try:
            while self.running:
                # Verificar se há comandos na fila
                queue = self.read_queue()
                
                if queue:
                    print(f"\n{len(queue)} comando(s) na fila")
                    
                    # Processar todos os comandos
                    processed_commands = []
                    for command in queue:
                        if self.process_command(command):
                            processed_commands.append(command)
                    
                    # Limpar fila
                    if processed_commands:
                        self.write_queue([])
                        print(f"Processados {len(processed_commands)} comando(s)")
                
                # Pequeno delay para não sobrecarregar a CPU
                time.sleep(0.1)
                
        except KeyboardInterrupt:
            print("\n\nEncerrando daemon...")
            self.cleanup()
        except Exception as e:
            print(f"\nErro fatal: {str(e)}")
            self.cleanup()
    
    def cleanup(self):
        """Limpa recursos ao encerrar"""
        print("Limpando recursos...")
        
        if self.serial_connection and self.serial_connection.is_open:
            self.serial_connection.close()
            print("Conexão serial fechada")
        
        if self.pid_file.exists():
            self.pid_file.unlink()
            print("Arquivo PID removido")
        
        self.running = False
        print("Daemon encerrado")

if __name__ == "__main__":
    daemon = ArduinoDaemon()
    daemon.run()
