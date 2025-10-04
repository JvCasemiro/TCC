// Pinos dos LEDs (2 a 13)
const int NUM_LEDS = 12;
const int FIRST_LED_PIN = 2;

// Status dos LEDs (0-11 correspondendo aos pinos 2-13)
bool ledStatus[NUM_LEDS] = {false};

void setup() {
  // Inicializa a comunicação serial
  Serial.begin(9600);
  
  // Configura todos os pinos dos LEDs como saída
  for (int i = 0; i < NUM_LEDS; i++) {
    pinMode(FIRST_LED_PIN + i, OUTPUT);
    digitalWrite(FIRST_LED_PIN + i, LOW);
  }
}

void loop() {
  if (Serial.available() > 0) {
    String command = Serial.readStringUntil('\n');
    command.trim();
    
    // Comandos no formato: LEDX:STATE (ex: LED1:ON, LED3:OFF)
    if (command.startsWith("LED") && command.indexOf(':') != -1) {
      // Extrai o número do LED (1-12)
      int ledNumber = command.substring(3, command.indexOf(':')).toInt();
      String state = command.substring(command.indexOf(':') + 1);
      
      // Verifica se o número do LED é válido
      if (ledNumber >= 1 && ledNumber <= NUM_LEDS) {
        int pin = FIRST_LED_PIN + (ledNumber - 1);
        
        if (state == "ON") {
          digitalWrite(pin, HIGH);
          ledStatus[ledNumber-1] = true;
        } 
        else if (state == "OFF") {
          digitalWrite(pin, LOW);
          ledStatus[ledNumber-1] = false;
        }
        
        // Atualiza o arquivo de status
        updateStatusFile();
      }
    }
    // Comando para ler o status atual
    else if (command == "STATUS") {
      updateStatusFile();
    }
  }
  delay(100);
}

// Função para atualizar o arquivo de status
void updateStatusFile() {
  String statusString = "";
  for (int i = 0; i < NUM_LEDS; i++) {
    statusString += ledStatus[i] ? "1" : "0";
  }
  
  // Envia o status para o computador via Serial
  Serial.print("STATUS:");
  Serial.println(statusString);
}
