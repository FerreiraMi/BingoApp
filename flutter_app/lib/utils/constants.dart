class AppConstants {
  // MUDE AQUI PARA O IP DA SUA MÁQUINA NA REDE LOCAL (ex: 192.168.1.5)
  // NÃO USE localhost ou 127.0.0.1, pois o celular/emulador não entenderá.
  static const String YOUR_LOCAL_IP = "localhost"; // <--- MUDE AQUI

  static const String API_URL = "http://$YOUR_LOCAL_IP:8000/api";
  static const String WEBSOCKET_URL = "ws://$YOUR_LOCAL_IP:8080";
  static const String WEB_PAGE_URL = "http://$YOUR_LOCAL_IP:8000/bingo";
}