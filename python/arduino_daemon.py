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
        self.temperature_status_file = self.base_dir / 'temperature_status.txt'
        self.pid_file = self.base_dir / 'arduino_daemon.pid'
        self.temperature_file = self.base_dir / 'temperature_data.json'
        self.running = True
        
        if not self.queue_file.exists():
            self.queue_file.write_text('[]')
        
        if not self.temperature_file.exists():
            self.temperature_file.write_text(json.dumps({
                'temperature': 0,
                'humidity': 0,
                'last_update': time.strftime('%Y-%m-%d %H:%M:%S'),
                'status': 'waiting'
            }))
        
        self.pid_file.write_text(str(os.getpid()))
        
    def find_arduino(self):
        """Procura e retorna a porta do Arduino"""
        print("\n" + "="*50)
        print("Procurando portas seriais disponíveis...")
        ports = serial.tools.list_ports.comports()
        
        if not ports:
            print("Nenhuma porta serial encontrada!")
            return None
            
        print(f"Portas encontradas: {[p.device for p in ports]}")
        
        # Lista de identificadores conhecidos do Arduino
        arduino_ids = ['arduino', 'ch340', 'cp210', 'ftdi', 'usb serial']
        
        for port in ports:
            print(f"\nVerificando porta: {port.device}")
            print(f"  Descrição: {port.description}")
            print(f"  HWID: {port.hwid}")
            print(f"  VID:PID: {port.vid}:{port.pid if port.pid else 'N/A'}")
            print(f"  Número serial: {port.serial_number if hasattr(port, 'serial_number') else 'N/A'}")
            
            # Verifica se a porta parece ser um Arduino
            is_arduino_like = any(id in port.description.lower() for id in arduino_ids) if port.description else False
            
            if not is_arduino_like and port.manufacturer:
                is_arduino_like = any(id in port.manufacturer.lower() for id in arduino_ids)
            
            print(f"  Parece ser Arduino: {'Sim' if is_arduino_like else 'Não'}")
            
            try:
                # Tenta se conectar à porta
                print(f"  Tentando conectar em {port.device}...")
                ser = serial.Serial(
                    port=port.device,
                    baudrate=9600,
                    bytesize=serial.EIGHTBITS,
                    parity=serial.PARITY_NONE,
                    stopbits=serial.STOPBITS_ONE,
                    timeout=2,
                    write_timeout=2
                )
                
                # Dá um tempo para o Arduino reiniciar
                time.sleep(2)
                
                # Limpa buffers
                ser.reset_input_buffer()
                ser.reset_output_buffer()
                
                # Testa o comando LED1:ON
                test_command = "LED1:ON\n"
                print(f"  Enviando comando: {test_command.strip()}")
                ser.write(test_command.encode())
                time.sleep(0.5)
                
                # Verifica resposta
                if ser.in_waiting > 0:
                    response = ser.readline().decode(errors='ignore').strip()
                    print(f"  Resposta do Arduino: {response}")
                    if 'LIGADO' in response.upper() or 'DESLIGADO' in response.upper() or 'OK' in response.upper():
                        print(f"  Arduino encontrado em {port.device}!")
                        # Desliga o LED de teste
                        ser.write(b"LED1:OFF\n")
                        ser.flush()
                        time.sleep(0.1)
                        ser.close()
                        print("="*50 + "\n")
                        return port.device
                
                # Se não obteve resposta, tenta um comando simples de eco
                ser.write(b"ECHO:TEST\n")
                time.sleep(0.5)
                if ser.in_waiting > 0:
                    response = ser.readline().decode(errors='ignore').strip()
                    print(f"  Resposta ao ECHO: {response}")
                    if 'TEST' in response:
                        print(f"  Dispositivo de eco encontrado em {port.device}")
                        ser.close()
                        print("="*50 + "\n")
                        return port.device
                
                # Se chegou até aqui, não é um Arduino compatível
                print(f"  Dispositivo em {port.device} não respondeu como esperado")
                ser.close()
                
            except (serial.SerialException, OSError) as e:
                print(f"  Erro ao acessar {port.device}: {str(e)}")
                continue
            except Exception as e:
                print(f"  Erro inesperado em {port.device}: {str(e)}")
                continue
        
        print("\nNenhum Arduino compatível encontrado!")
        print("Verifique se o Arduino está conectado e a porta está disponível.")
        print("="*50 + "\n")
        return None
    
    def connect_to_arduino(self):
        """Conecta ao Arduino e mantém a conexão aberta"""
        # Se já estiver conectado, verifica se a conexão ainda está ativa
        if self.serial_connection and self.serial_connection.is_open:
            try:
                # Tenta enviar um comando de teste
                self.serial_connection.write(b"ECHO:TEST\n")
                time.sleep(0.1)
                if self.serial_connection.in_waiting > 0:
                    response = self.serial_connection.readline().decode(errors='ignore').strip()
                    if 'TEST' in response:
                        return True
                # Se chegou aqui, a conexão pode estar inativa
                print("Conexão inativa, tentando reconectar...")
                self.serial_connection.close()
                self.serial_connection = None
            except:
                # Em caso de erro, força a reconexão
                print("Erro na conexão existente, tentando reconectar...")
                if self.serial_connection:
                    try:
                        self.serial_connection.close()
                    except:
                        pass
                    self.serial_connection = None
        
        # Tenta encontrar o Arduino se não soubermos a porta
        max_attempts = 3
        attempt = 0
        
        while attempt < max_attempts and not self.arduino_port:
            attempt += 1
            print(f"\nTentativa {attempt} de {max_attempts} para encontrar o Arduino...")
            self.arduino_port = self.find_arduino()
            
            if not self.arduino_port and attempt < max_attempts:
                print(f"Aguardando 2 segundos antes de tentar novamente...")
                time.sleep(2)
        
        if not self.arduino_port:
            print("Erro: Não foi possível encontrar o Arduino após várias tentativas")
            return False
        
        # Tenta conectar ao Arduino
        try:
            print(f"\nConectando ao Arduino na porta {self.arduino_port}...")
            self.serial_connection = serial.Serial(
                port=self.arduino_port,
                baudrate=9600,
                bytesize=serial.EIGHTBITS,
                parity=serial.PARITY_NONE,
                stopbits=serial.STOPBITS_ONE,
                timeout=2,
                write_timeout=2,
                rtscts=False,
                dsrdtr=False
            )
            
            # Desativa o reset automático ao conectar
            self.serial_connection.dtr = False
            
            # Dá tempo para o Arduino reiniciar
            time.sleep(2)
            
            # Limpa buffers
            self.serial_connection.reset_input_buffer()
            self.serial_connection.reset_output_buffer()
            
            # Envia um comando de teste
            self.serial_connection.write(b"ECHO:TEST\n")
            time.sleep(0.5)
            
            # Verifica a resposta
            if self.serial_connection.in_waiting > 0:
                response = self.serial_connection.readline().decode(errors='ignore').strip()
                print(f"Resposta do Arduino: {response}")
                if 'TEST' in response:
                    print("Conexão com o Arduino estabelecida com sucesso!")
                    return True
            
            # Se chegou aqui, não recebeu a resposta esperada
            print("Aviso: Não foi possível verificar a conexão com o Arduino")
            print("A conexão foi estabelecida, mas o Arduino não respondeu como esperado")
            return True
            
            
            self._sync_all_lights()
            self._sync_all_temperatures()
            
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
            
            print("Sincronização de lâmpadas concluída")
        except Exception as e:
            print(f"Erro ao sincronizar lâmpadas: {str(e)}")
            
    def _sync_all_temperatures(self):
        """Sincroniza o estado de todos os ventiladores ao conectar"""
        try:
            if not self.temperature_status_file.exists():
                return
                
            status_str = self.temperature_status_file.read_text().strip()
            print(f"Sincronizando estado de todos os ventiladores: {status_str}")
            
            for i in range(min(4, len(status_str))):  # Apenas os primeiros 4 ventiladores (pinos 14-17)
                if status_str[i] in ['0', '1']:  # Garante que é 0 ou 1
                    vent_num = i + 1  # 1-based para o comando TEMP
                    vent_status = status_str[i]
                    command = f"TEMP{vent_num}:{'ON' if vent_status == '1' else 'OFF'}\n"
                    self.serial_connection.write(command.encode())
                    self.serial_connection.flush()
                    time.sleep(0.2)  # Pequena pausa entre comandos
                    
                    if self.serial_connection.in_waiting > 0:
                        response = self.serial_connection.readline().decode().strip()
                        print(f"Resposta do Arduino (TEMP{vent_num}): {response}")
            
            print("Sincronização de ventiladores concluída")
        except Exception as e:
            print(f"Erro ao sincronizar ventiladores: {str(e)}")
            # Tenta reconectar se houver erro
            try:
                self.serial_connection.close()
            except:
                pass
            self.serial_connection = None
            self.connect_to_arduino()
    
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
            if command.get('type') == 'gate':
                return self.process_gate_command(command)
            
            if command.get('type') == 'temperature':
                return self.process_temperature_command(command)
            
            light_id = command.get('light_id')
            status = command.get('status')
            
            if not light_id or not status:
                print(f"Comando inválido: {command}")
                return False
            
            status_str = self.status_file.read_text().strip() if self.status_file.exists() else ''
            status_list = list(status_str)
            
            if len(status_list) < light_id:
                status_list.extend(['0'] * (light_id - len(status_list)))
            
            status_list[light_id - 1] = '1' if status == 'ON' else '0'
            new_status = ''.join(status_list)
            self.status_file.write_text(new_status)
            
            arduino_command = f"LED{light_id}:{'ON' if status == 'ON' else 'OFF'}\n"
            print(f"Enviando comando: {arduino_command.strip()}")
            
            self.serial_connection.reset_input_buffer()
            self.serial_connection.write(arduino_command.encode())
            self.serial_connection.flush()
            
            time.sleep(0.3)
            
            if self.serial_connection.in_waiting > 0:
                response = self.serial_connection.readline().decode().strip()
                print(f"Resposta do Arduino: {response}")
            
            return True
            
        except Exception as e:
            print(f"Erro ao processar comando: {str(e)}")
            return False
    
    def process_gate_command(self, command):
        """Processa comando do portão"""
        try:
            action = command.get('action')
            
            if action not in ['OPEN', 'CLOSE']:
                print(f"Ação de portão inválida: {action}")
                return False
            
            arduino_command = f"GATE:{action}\n"
            print(f"Enviando comando de portão: {arduino_command.strip()}")
            
            self.serial_connection.reset_input_buffer()
            self.serial_connection.write(arduino_command.encode())
            self.serial_connection.flush()
            
            time.sleep(0.3)
            if self.serial_connection.in_waiting > 0:
                response = self.serial_connection.readline().decode().strip()
                print(f"Resposta do Arduino: {response}")
            
            print("Aguardando operação do portão...")
            time.sleep(5.5)
            
            if self.serial_connection.in_waiting > 0:
                response = self.serial_connection.readline().decode().strip()
                print(f"Resposta do Arduino: {response}")
            
            print(f"Comando de portão {action} concluído")
            return True
            
        except Exception as e:
            print(f"Erro ao processar comando de portão: {str(e)}")
            return False
    
    def process_temperature_command(self, command):
        """Processa comando de temperatura"""
        try:
            temperature_id = command.get('temperature_id')
            temp_index = command.get('temp_index')  # índice 1-based calculado no PHP
            status = command.get('status')
            
            if not temperature_id or not status:
                print(f"Comando de temperatura inválido: {command}")
                return False
            
            # Determina o índice a ser usado no Arduino: prioriza temp_index
            try:
                idx = int(temp_index) if temp_index is not None else int(temperature_id)
            except (ValueError, TypeError) as e:
                print(f"Erro ao converter índice/temperatura_id para inteiro: {e}")
                return False

            # Lê o arquivo de status atual ou cria um novo com '0000' se não existir
            if not self.temperature_status_file.exists():
                status_str = '0000'
                self.temperature_status_file.write_text(status_str)
            else:
                status_str = self.temperature_status_file.read_text().strip()
                
            # Garante que o status_str tenha 4 caracteres
            status_list = list(status_str.ljust(4, '0')[:4])
            
            # Atualiza o status no índice correto (1-based para 0-based)
            if 1 <= idx <= 4:
                status_list[idx - 1] = '1' if status == 'ON' else '0'
                
                # Salva o status atualizado
                self.temperature_status_file.write_text(''.join(status_list))
                print(f"Status atualizado: {''.join(status_list)} (índice {idx-1} = {status})")
                
                # Envia comando para o Arduino (TEMP1..TEMP4 para pinos 14..17)
                arduino_command = f"TEMP{idx}:{'ON' if status == 'ON' else 'OFF'}\n"
                print(f"Enviando comando de temperatura: {arduino_command.strip()}")
                
                if not hasattr(self, 'serial_connection') or not self.serial_connection or not self.serial_connection.is_open:
                    print("Erro: Conexão serial não está disponível")
                    return False
                
                self.serial_connection.reset_input_buffer()
                self.serial_connection.write(arduino_command.encode())
                self.serial_connection.flush()
                
                time.sleep(0.3)
                
                if self.serial_connection.in_waiting > 0:
                    response = self.serial_connection.readline().decode().strip()
                    print(f"Resposta do Arduino: {response}")
                
                return True
            else:
                print(f"Erro: Índice {idx} fora do intervalo 1-4")
                return False
                
        except Exception as e:
            print(f"Erro ao processar comando de temperatura: {str(e)}")
            return False
                
    def process_sensor_data(self, data):
        """Processa dados do sensor DHT11"""
        try:
            if data.startswith('DHT:'):
                parts = data.split(':')
                
                if len(parts) >= 5 and parts[1] == 'TEMP' and parts[3] == 'HUMIDITY':
                    temperature = float(parts[2])
                    humidity = float(parts[4])
                    
                    sensor_data = {
                        'temperature': temperature,
                        'humidity': humidity,
                        'last_update': time.strftime('%Y-%m-%d %H:%M:%S'),
                        'status': 'online'
                    }
                    
                    self.temperature_file.write_text(json.dumps(sensor_data, indent=2))
                    print(f"Temperatura: {temperature}°C | Umidade: {humidity}%")
                    return True
                elif 'ERROR' in data:
                    sensor_data = {
                        'temperature': 0,
                        'humidity': 0,
                        'last_update': time.strftime('%Y-%m-%d %H:%M:%S'),
                        'status': 'error'
                    }
                    self.temperature_file.write_text(json.dumps(sensor_data, indent=2))
                    print("Erro na leitura do sensor DHT11")
            
            return False
            
        except Exception as e:
            print(f"Erro ao processar dados do sensor: {str(e)}")
            return False
    
    def run(self):
        """Loop principal do daemon"""
        print("=" * 50)
        print("Arduino Daemon iniciado")
        print("=" * 50)
        
        if not self.connect_to_arduino():
            print("Falha ao conectar ao Arduino. Encerrando...")
            return
        
        print("Aguardando comandos na fila...")
        print("Pressione Ctrl+C para encerrar")
        print("=" * 50)
        
        try:
            while self.running:
                queue = self.read_queue()
                
                if queue:
                    print(f"\n{len(queue)} comando(s) na fila")
                    
                    processed_commands = []
                    for command in queue:
                        if self.process_command(command):
                            processed_commands.append(command)
                    
                    if processed_commands:
                        self.write_queue([])
                        print(f"Processados {len(processed_commands)} comando(s)")
                
                if self.serial_connection and self.serial_connection.in_waiting > 0:
                    try:
                        sensor_data = self.serial_connection.readline().decode().strip()
                        if sensor_data:
                            self.process_sensor_data(sensor_data)
                    except Exception as e:
                        print(f"Erro ao ler dados do sensor: {str(e)}")
                
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
