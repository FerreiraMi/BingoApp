# BingoController

Plataforma de controle de sessões de bingo com três camadas principais: backend PHP (APIs REST + WebSocket com Ratchet) conectado ao MongoDB, frontend web responsivo hospedado pelo Nginx e aplicativo móvel Flutter que consome os mesmos serviços.

## Visão Geral
- **Backend:** PHP 8.1 (FPM) com Composer, Ratchet WebSocket e driver oficial do MongoDB.
- **Tempo real:** `backend/server.php` inicializa o serviço WebSocket que orquestra sorteios e espectadores via `MyApp\BingoChat`.
- **APIs REST:** Endpoints em `backend/api/` oferecem login, criação de sessão, histórico e token de exibição.
- **Mobile/Web:** O diretório `flutter_app/` contém o app Bingou (Flutter 3) que autentica usuários e controla sessões.
- **Infra:** `docker-compose.yml` provisiona PHP-FPM, WebSocket, Nginx e MongoDB; `helm/` contém charts para Kubernetes.

## Estrutura Resumida
| Caminho | Descrição |
| --- | --- |
| `backend/` | Aplicação PHP (APIs, views, ativos estáticos e servidor WebSocket). |
| `backend/src/` | Código de domínio: atualmente `BingoChat.php` (componente WebSocket). |
| `backend/tests/` | Testes PHPUnit com doubles em `tests/Support`. |
| `docker/` | Dockerfiles e configuração do Nginx usados em desenvolvimento/deploy. |
| `docker-compose.yml` | Orquestra o stack local (app, websocket, webserver, MongoDB). |
| `helm/` | Charts separados para app HTTP, frontend web e serviço WebSocket. |
| `flutter_app/` | Projeto Flutter destinado a operadores e espectadores móveis. |
| `doc/` | Ponto central para a documentação técnica do projeto (ver abaixo). |

## Como Executar Localmente
1. Configure variáveis `.env` com `MONGO_DSN`, `HOST_NAME` e `WSHOST_NAME`.
2. Execute `docker compose up --build` para subir PHP, WebSocket, Nginx e MongoDB.
3. Acesse `http://localhost:8000` para o frontend web. O WebSocket fica em `ws://localhost:8080`.
4. Para o app Flutter, sincronize dependências com `flutter pub get` e use `flutter run` apontando para o mesmo backend.

## Documentação
Os artefatos técnicos ficam em [doc/README.md](doc/README.md), que lista os arquivos recomendados (`OVERVIEW.md`, `ARCHITECTURE.md`, `DOMAIN.md`, `TESTING.md`, etc.) e como evoluir cada um.

## Próximos Passos
- Popular os documentos do diretório `doc/` com os insumos fornecidos nas análises e recomendações.
- Automatizar os pipelines de build/test/deploy para alinhar com as práticas propostas neste repositório.
