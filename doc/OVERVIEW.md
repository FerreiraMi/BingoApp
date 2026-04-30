# Visão Geral

## Propósito do Produto
Plataforma para criação e condução de sessões de bingo em tempo real. Operadores autenticam-se via app Flutter ou painel web, criam sessões no MongoDB e distribuem um código curto para espectadores acompanharem sorteios via WebSocket.

## Componentes Atuais
| Componente | Descrição |
| --- | --- |
| Backend PHP | Scripts em `backend/` que expõem APIs REST (`api/*.php`), páginas PHP tradicionais e o servidor WebSocket (`server.php`). |
| MongoDB | Banco documental que persiste usuários (`users`) e sessões (`sessions`). |
| WebSocket Ratchet | Componente `MyApp\\BingoChat` em `backend/src/BingoChat.php` gerencia sorteios, lista de espectadores e broadcast. |
| Nginx + PHP-FPM | Stack containerizado por `docker-compose.yml` e Helm para servir HTTP e encaminhar requisições. |
| Flutter App | Cliente móvel em `flutter_app/` que autentica operadores, cria sessões e consome WebSocket. |

## Personas
- **Operador:** cria sessões, sorteia números e anuncia vencedores.
- **Espectador:** recebe o ID curto, entra na sessão e acompanha sorteios em tempo real.
- **Time técnico:** mantém infraestrutura Docker/Kubernetes e evolui a base PHP/Flutter.

## Ambientes Conhecidos
| Ambiente | Status |
| --- | --- |
| Desenvolvimento local | Suportado via `docker compose up` (PHP + WebSocket + Nginx + Mongo). |
| Produção | Indícios de uso em `app-bingo.iw7.com.br` e `app-bingo-ws.iw7.com.br`, porém sem documentação oficial. |
| Stage/Test | //TODO descrever ou criar ambiente intermediário.

## Fluxo Macro
1. Operador autentica via `backend/api/login.php`.
2. Cria sessão com `backend/api/session.php` (gera `shortId`).
3. Web e app conectam-se a `server.php` (Ratchet) com `authenticate_operator`, `subscribe` ou `viewer_joined`.
4. Eventos `new_number`, `session_settings`, `bingo_called` são persistidos e broadcast via WebSocket.

## Dependências Externas
- MongoDB DSN definido via `MONGO_DSN` (obrigatório).
- Serviços HTTP/WS expostos via Nginx e Docker networking.
- App Flutter depende das APIs e do WebSocket configurados em `flutter_app/lib/providers` (não documentados aqui). //TODO detalhar contratos Flutter.
