class AppConstants {
  // MUDE AQUI PARA O IP DA SUA MÁQUINA NA REDE LOCAL (ex: 192.168.1.5)
  // NÃO USE localhost ou 127.0.0.1, pois o celular/emulador não entenderá.
  static const String YOUR_LOCAL_IP = "app-bingo.iw7.com.br"; // <--- MUDE AQUI

  static const String API_URL = "https://$YOUR_LOCAL_IP/api";
  static const String WEB_PAGE_URL = "https://$YOUR_LOCAL_IP/bingo";

  static const String WEBSOCKET_URL = "wss://app-bingo-ws.iw7.com.br";
}