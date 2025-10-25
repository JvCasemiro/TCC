#include <Arduino.h>
#include <DHT.h>

const int NUM_LEDS = 12;
const int FIRST_LED_PIN = 2;
bool ledStatus[NUM_LEDS];

// Pino do motor DC (portão)
const int MOTOR_CONTROL_PIN = 13;  // Pino único para controle do portão

// Configuração do sensor DHT11
#define DHTPIN A1        // Pino de dados do DHT11 conectado ao A1
#define DHTTYPE DHT11    // Tipo do sensor DHT11
DHT dht(DHTPIN, DHTTYPE);

// Variáveis para controle de tempo de leitura
unsigned long lastSensorRead = 0;
const long sensorInterval = 2000;  // Intervalo de leitura: 2 segundos

void setup() {
  Serial.begin(9600);
  
  // Configurar pinos dos LEDs
  for (int i = 0; i < NUM_LEDS; i++) {
    pinMode(FIRST_LED_PIN + i, OUTPUT);
    digitalWrite(FIRST_LED_PIN + i, LOW);
    ledStatus[i] = false;
  }
  
  // Configurar pino do motor
  pinMode(MOTOR_CONTROL_PIN, OUTPUT);
  digitalWrite(MOTOR_CONTROL_PIN, LOW);
  
  // Inicializar sensor DHT11
  dht.begin();
  
  Serial.println("Sistema de Controle de LEDs, Portao e Sensor DHT11 Iniciado");
  Serial.println("Comandos aceitos:");
  Serial.println("- LEDX:ON ou LEDX:OFF (onde X é o número do LED 1-12)");
  Serial.println("- GATE:OPEN ou GATE:CLOSE");
  Serial.println("- Leitura automática de temperatura e umidade a cada 2s");
}

void loop() {
  if (Serial.available() > 0) {
    String command = Serial.readStringUntil('\n');
    command.trim();
    
    if (command.startsWith("LED") && command.indexOf(':') != -1) {
      int ledNumber = command.substring(3, command.indexOf(':')).toInt();
      String state = command.substring(command.indexOf(':') + 1);
      
      if (ledNumber >= 1 && ledNumber <= NUM_LEDS) {
        int pin = FIRST_LED_PIN + (ledNumber - 1);
        
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
    else if (command.startsWith("GATE:")) {
      String action = command.substring(5);
      
      if (action == "OPEN") {
        // Abrir portão - envia pulsos rápidos no pino A0 por 5 segundos
        Serial.println("PORTAO: ABRINDO");
        unsigned long startTime = millis();
        while (millis() - startTime < 5000) {
          digitalWrite(MOTOR_CONTROL_PIN, HIGH);
          delay(50);  // Pulso HIGH de 50ms
          digitalWrite(MOTOR_CONTROL_PIN, LOW);
          delay(50);  // Pulso LOW de 50ms
        }
        digitalWrite(MOTOR_CONTROL_PIN, LOW);  // Garantir que está LOW
        Serial.println("PORTAO: ABERTO");
      }
      else if (action == "CLOSE") {
        // Fechar portão - mantém pino A0 HIGH constante por 5 segundos
        Serial.println("PORTAO: FECHANDO");
        digitalWrite(MOTOR_CONTROL_PIN, HIGH);
        delay(5000);  // Motor ligado por 5 segundos
        digitalWrite(MOTOR_CONTROL_PIN, LOW);
        Serial.println("PORTAO: FECHADO");
      }
      else {
        Serial.println("ERRO: Comando de portão inválido. Use GATE:OPEN ou GATE:CLOSE");
      }
    }
  }
  
  // Leitura periódica do sensor DHT11
  unsigned long currentMillis = millis();
  if (currentMillis - lastSensorRead >= sensorInterval) {
    lastSensorRead = currentMillis;
    
    // Ler temperatura e umidade
    float humidity = dht.readHumidity();
    float temperature = dht.readTemperature();
    
    // Verificar se a leitura foi bem-sucedida
    if (isnan(humidity) || isnan(temperature)) {
      Serial.println("DHT:ERROR");
    } else {
      // Enviar dados no formato: DHT:TEMP:XX.XX:HUMIDITY:XX.XX
      Serial.print("DHT:TEMP:");
      Serial.print(temperature, 2);
      Serial.print(":HUMIDITY:");
      Serial.println(humidity, 2);
    }
  }
}
