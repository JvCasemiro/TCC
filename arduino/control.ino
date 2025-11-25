#include <Arduino.h>
#include <DHT.h>
#include <Servo.h>

const int NUM_LEDS = 10;  // Ajustado para 10 LEDs conforme a sequência fornecida
// Array com os pinos dos LEDs na sequência: 2, 13, 4, 5, 6, 7, 8, 9, 10, 11
const int LED_PINS[10] = {42, 13, 4, 45, 6, 7, 8, 9, 10, 11};
bool ledStatus[NUM_LEDS];

// Controle de temperatura (ar-condicionado)
const int NUM_TEMPS = 3;
// Array com os pinos de temperatura na sequência: 14, 15, 16
const int TEMP_PINS[3] = {14, 15, 16};
bool tempStatus[NUM_TEMPS];

// Pino do motor DC (portão)
const int MOTOR_CONTROL_PIN = 12;  // Pino único para controle do portão

Servo gateServo;
const int GATE_OPEN_ANGLE = 40;
const int GATE_CLOSE_ANGLE = 180;
bool gateIsOpen = false;
unsigned long gateAutoCloseAt = 0;

// Pino do relé da piscina
const int POOL_RELAY_PIN = 30;  // Pino para controle da piscina

// Pino do relé da irrigação da horta
const int GARDEN_RELAY_PIN = 31;  // Pino para controle da irrigação da horta

// Pinos da ponte H do portão
const int GATE_EN1_PIN = 2;  // Direção 1
const int GATE_EN2_PIN = 3;  // Direção 2
const int GATE_ENA_PIN = 5;  // PWM de velocidade

// Configuração dos sensores DHT11
#define DHTPIN1 A1       // Primeiro sensor DHT11 conectado ao A1
#define DHTPIN2 A3       // Segundo sensor DHT11 conectado ao A2
#define DHTTYPE DHT11    // Tipo do sensor DHT11
DHT dht1(DHTPIN1, DHTTYPE);
DHT dht2(DHTPIN2, DHTTYPE);

// Variáveis para controle de tempo de leitura
unsigned long lastSensorRead = 0;
const long sensorInterval = 2000;  // Intervalo de leitura: 2 segundos

void setup() {
  Serial.begin(9600);
  
  // Configurar pinos dos LEDs
  for (int i = 0; i < NUM_LEDS; i++) {
    pinMode(LED_PINS[i], OUTPUT);
    digitalWrite(LED_PINS[i], LOW);
    ledStatus[i] = false;
  }
  
  // Configurar pinos de temperatura (ar-condicionado)
  for (int i = 0; i < NUM_TEMPS; i++) {
    pinMode(TEMP_PINS[i], OUTPUT);
    digitalWrite(TEMP_PINS[i], LOW);
    tempStatus[i] = false;
  }
  
  // Configurar pinos de saída
  gateServo.attach(MOTOR_CONTROL_PIN);
  gateServo.write(GATE_CLOSE_ANGLE);
  
  // Configurar pinos dos relés
  pinMode(POOL_RELAY_PIN, OUTPUT);
  digitalWrite(POOL_RELAY_PIN, LOW);
  
  pinMode(GARDEN_RELAY_PIN, OUTPUT);
  digitalWrite(GARDEN_RELAY_PIN, LOW);

  // Configurar pinos da ponte H do portão
  pinMode(GATE_EN1_PIN, OUTPUT);
  pinMode(GATE_EN2_PIN, OUTPUT);
  pinMode(GATE_ENA_PIN, OUTPUT);
  digitalWrite(GATE_EN1_PIN, LOW);
  digitalWrite(GATE_EN2_PIN, LOW);
  analogWrite(GATE_ENA_PIN, 0);
  
  // Inicializar sensores DHT11
  dht1.begin();
  dht2.begin();
  
  Serial.println("Sistema de Controle de LEDs, Temperatura, Portao e Sensor DHT11 Iniciado");
  Serial.println("Comandos aceitos:");
  Serial.println("- LEDX:ON ou LEDX:OFF (onde X é o número do LED 1-12)");
  Serial.println("- TEMPX:ON ou TEMPX:OFF (onde X é o número da temperatura 1-4)");
  Serial.println("- GATE:OPEN ou GATE:CLOSE");
  Serial.println("- POOL:ON ou POOL:OFF");
  Serial.println("- GARDEN:ON ou GARDEN:OFF");
}

void loop() {
  if (Serial.available() > 0) {
    String command = Serial.readStringUntil('\n');
    command.trim();
    
    if (command.startsWith("LED") && command.indexOf(':') != -1) {
      int ledNumber = command.substring(3, command.indexOf(':')).toInt();
      String state = command.substring(command.indexOf(':') + 1);
      
      if (ledNumber >= 1 && ledNumber <= NUM_LEDS) {
        // Usa o pino correspondente da sequência
        int pin = LED_PINS[ledNumber - 1];
        
        if (state == "ON") {
          digitalWrite(pin, HIGH);
          ledStatus[ledNumber-1] = true;
          Serial.print("LED");
          Serial.print(ledNumber);
          Serial.println(": LIGADO");
        } 
        else if (state == "OFF") {
          digitalWrite(pin, LOW);
          ledStatus[ledNumber-1] = false;
          Serial.print("LED");
          Serial.print(ledNumber);
          Serial.println(": DESLIGADO");
        }
      } else {
        Serial.println("ERRO: Número do LED inválido. Use um valor entre 1 e 12.");
      }
    }
    else if (command.startsWith("TEMP") && command.indexOf(':') != -1) {
      int tempNumber = command.substring(4, command.indexOf(':')).toInt();
      String state = command.substring(command.indexOf(':') + 1);
      
      if (tempNumber >= 1 && tempNumber <= NUM_TEMPS) {
        int pin = TEMP_PINS[tempNumber - 1];
        
        if (state == "ON") {
          digitalWrite(pin, HIGH);
          tempStatus[tempNumber-1] = true;
          Serial.print("TEMPERATURA");
          Serial.print(tempNumber);
          Serial.println(": LIGADA");
        } 
        else if (state == "OFF") {
          digitalWrite(pin, LOW);
          tempStatus[tempNumber-1] = false;
          Serial.print("TEMPERATURA");
          Serial.print(tempNumber);
          Serial.println(": DESLIGADA");
        }
      } else {
        Serial.println("ERRO: Número da temperatura inválido. Use um valor entre 1 e 4.");
      }
    }
    else if (command == "POOL:ON") {
      // Ligar piscina
      digitalWrite(POOL_RELAY_PIN, HIGH);
      Serial.println("PISCINA: LIGADA");
    }
    else if (command == "POOL:OFF") {
      // Desligar piscina
      digitalWrite(POOL_RELAY_PIN, LOW);
      Serial.println("PISCINA: DESLIGADA");
    }
    else if (command == "GARDEN:ON") {
      // Ligar irrigação da horta
      digitalWrite(GARDEN_RELAY_PIN, HIGH);
      Serial.println("IRRIGACAO HORTA: LIGADA");
    }
    else if (command == "GARDEN:OFF") {
      // Desligar irrigação da horta
      digitalWrite(GARDEN_RELAY_PIN, LOW);
      Serial.println("IRRIGACAO HORTA: DESLIGADA");
    }
    else if (command.startsWith("GATE:")) {
      String payload = command.substring(5);
      String action = payload;
      String mode = "";
      int sep = payload.indexOf(':');
      if (sep != -1) {
        action = payload.substring(0, sep);
        mode = payload.substring(sep + 1);
      }
      
      if (action == "OPEN") {
        Serial.println("PORTAO: ABRINDO");
        gateServo.write(GATE_OPEN_ANGLE);
        delay(800);
        Serial.println("PORTAO: ABERTO");
        gateIsOpen = true;
        if (mode == "AUTO") {
          gateAutoCloseAt = millis() + 20000UL;
        } else {
          gateAutoCloseAt = 0;
        }
      }
      else if (action == "CLOSE") {
        Serial.println("PORTAO: FECHANDO");
        gateServo.write(GATE_CLOSE_ANGLE);
        delay(800);
        Serial.println("PORTAO: FECHADO");
        gateIsOpen = false;
        gateAutoCloseAt = 0;
      }
    }
    else if (command.startsWith("ECHO:")) {
      // Echo back the payload after "ECHO:"
      String payload = command.substring(5);
      Serial.println(payload);
    }
  }
  
  // Leitura periódica do sensor DHT11
  unsigned long currentMillis = millis();
  if (currentMillis - lastSensorRead >= sensorInterval) {
    lastSensorRead = currentMillis;
    
    // Ler temperatura de ambos os sensores
    float temperature1 = dht1.readTemperature();
    float temperature2 = dht2.readTemperature();
    
    // Verificar se as leituras foram bem-sucedidas e formatar a saída
    Serial.print("DHT:");
    
    // Sensor 1
    if (isnan(temperature1)) {
      Serial.print("TEMP1:ERROR");
    } else {
      Serial.print("TEMP1:");
      Serial.print(temperature1, 2);
    }
    
    Serial.print(":");
    
    // Sensor 2
    if (isnan(temperature2)) {
      Serial.print("TEMP2:ERROR");
    } else {
      Serial.print("TEMP2:");
      Serial.print(temperature2, 2);
    }
    
    // Finaliza a linha
    Serial.println();
  }
  
  if (gateIsOpen && gateAutoCloseAt > 0 && (long)(millis() - gateAutoCloseAt) >= 0) {
    Serial.println("PORTAO: FECHANDO");
    gateServo.write(GATE_CLOSE_ANGLE);
    delay(800);
    Serial.println("PORTAO: FECHADO");
    gateIsOpen = false;
    gateAutoCloseAt = 0;
  }
}
