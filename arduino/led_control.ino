const int ledPin = 13;

void setup() {
  pinMode(ledPin, OUTPUT);
  digitalWrite(ledPin, LOW);
  Serial.begin(9600);
}

void loop() {
  if (Serial.available() > 0) {
    String command = Serial.readStringUntil('\n');
    command.trim();
    
    if (command == "ON") {
      digitalWrite(ledPin, HIGH);
    } 
    else if (command == "OFF") {
      digitalWrite(ledPin, LOW);
    }
  }
  delay(100);
}
