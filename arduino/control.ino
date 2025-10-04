#include <Arduino.h>

const int NUM_LEDS = 12;
const int FIRST_LED_PIN = 2;
bool ledStatus[NUM_LEDS];

void setup() {
  Serial.begin(9600);
  
  for (int i = 0; i < NUM_LEDS; i++) {
    pinMode(FIRST_LED_PIN + i, OUTPUT);
    digitalWrite(FIRST_LED_PIN + i, LOW);
    ledStatus[i] = false;
  }
  
  Serial.println("Sistema de Controle de LEDs Iniciado");
  Serial.println("Comandos aceitos: LEDX:ON ou LEDX:OFF (onde X é o número do LED 1-12)");
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
  }
}
