/*
  ESP32 WiFi Scanner and Hotspot

  This script sets up the ESP32 as a WiFi Access Point (Hotspot) and runs a web server.
  The web server provides a webpage listing all available WiFi networks with their SSIDs and signal strengths.

  Author: [Your Name]
  Date: [Date]
*/

#include <WiFi.h>
#include <WebServer.h>

// Define the SSID and password for the ESP32 hotspot
const char* AP_SSID = "ESP32_Hotspot";         // Change to your desired SSID
const char* AP_PASSWORD = "your_password";     // Change to your desired password (minimum 8 characters)

// Create an instance of the web server on port 80
WebServer server(80);

// Function to handle the root URL "/"
void handleRoot() {
  String html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>ESP32 WiFi Scanner</title></head><body>";
  html += "<h1>Available WiFi Networks</h1>";
  html += "<table border='1' style='width:100%; text-align:left;'>";
  html += "<tr><th>SSID</th><th>RSSI (dBm)</th></tr>";

  // Start scanning for networks
  int n = WiFi.scanNetworks(false, true, false, 5000); // Parameters: async=false, show_hidden=true, passive=false, timeout=5000ms
  if(n == 0){
    html += "<tr><td colspan='2'>No networks found</td></tr>";
  } else {
    for (int i = 0; i < n; ++i){
      html += "<tr><td>" + WiFi.SSID(i) + "</td><td>" + String(WiFi.RSSI(i)) + "</td></tr>";
    }
  }
  html += "</table>";
  html += "<p>Refresh the page to scan again.</p>";
  html += "</body></html>";

  server.send(200, "text/html", html);
}

// Function to handle not found URLs
void handleNotFound(){
  String message = "File Not Found\n\n";
  message += "URI: " + server.uri() + "\n";
  message += "Method: " + String(server.method()) + "\n";
  message += "Arguments: " + String(server.args()) + "\n";
  for (uint8_t i = 0; i < server.args(); i++) {
    message += " " + server.argName(i) + ": " + server.arg(i) + "\n";
  }
  server.send(404, "text/plain", message);
}

void setup() {
  // Initialize Serial for debugging
  Serial.begin(115200);
  delay(1000);
  Serial.println();
  Serial.println("ESP32 WiFi Scanner and Hotspot");

  // Set WiFi mode to both AP and STA
  WiFi.mode(WIFI_AP_STA);

  // Start the Access Point
  bool apStarted = WiFi.softAP(AP_SSID, AP_PASSWORD);
  if(apStarted){
    Serial.println("Access Point started successfully");
    Serial.print("SSID: ");
    Serial.println(AP_SSID);
    Serial.print("Password: ");
    Serial.println(AP_PASSWORD);
    Serial.print("AP IP address: ");
    Serial.println(WiFi.softAPIP());
  } else {
    Serial.println("Failed to start Access Point!");
  }

  // Start the web server and define route handlers
  server.on("/", handleRoot);
  server.onNotFound(handleNotFound);
  server.begin();
  Serial.println("Web server started");
}

void loop() {
  server.handleClient();
}
