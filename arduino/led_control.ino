// LED Control via Serial
// Connect LED to pin 13 (or any other digital pin with a resistor)

const int ledPin = 13;  // Built-in LED on most Arduino boards

void setup() {
  pinMode(ledPin, OUTPUT);
  digitalWrite(ledPin, LOW);  // Start with LED off
  Serial.begin(9600);         // Start serial communication at 9600 baud
  Serial.println("Arduino LED Control Ready");
}

void loop() {
  if (Serial.available() > 0) {
    String command = Serial.readStringUntil('\n');
    command.trim();  // Remove any whitespace
    
    if (command == "ON") {
      digitalWrite(ledPin, HIGH);
      Serial.println("LED ON");
    } 
    else if (command == "OFF") {
      digitalWrite(ledPin, LOW);
      Serial.println("LED OFF");
    }
  }
  delay(100);  // Small delay to prevent serial buffer overflow
}
