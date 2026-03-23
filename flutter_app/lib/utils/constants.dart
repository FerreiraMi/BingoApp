class AppConstants {
  // -----------------------------------------------------------------------
  // Ambiente resolvido via --dart-define=ENV=local  (padrão: prod)
  //
  // Exemplos de uso:
  //   flutter run                            → produção
  //   flutter run --dart-define=ENV=local    → Docker local
  //   flutter build ipa                      → produção
  //   flutter build ipa --dart-define=ENV=local → build local
  // -----------------------------------------------------------------------
  static const String _env = String.fromEnvironment('ENV', defaultValue: 'prod');

  // ── Produção ──────────────────────────────────────────────────────────
  static const String _prodApiBase = "https://app-bingo.iw7.com.br";
  static const String _prodWsBase  = "wss://app-bingo-ws.iw7.com.br";

  // ── Local (Docker) ────────────────────────────────────────────────────
  // Dispositivo físico → IP LAN da máquina (ipconfig getifaddr en0)
  // iOS Simulator     → 127.0.0.1
  // Emulador Android  → 10.0.2.2
  static const String _localHost    = "192.168.2.198";
  static const String _localApiBase = "http://$_localHost:8000";
  static const String _localWsBase  = "ws://$_localHost:8080";

  // ── Resolução automática ──────────────────────────────────────────────
  static const String _apiBase = _env == 'local' ? _localApiBase : _prodApiBase;
  static const String _wsBase  = _env == 'local' ? _localWsBase  : _prodWsBase;

  static const String API_URL       = "$_apiBase/api";
  static const String WEB_PAGE_URL  = "$_apiBase/bingo";
  static const String WEBSOCKET_URL = _wsBase;
}
