# Arquitetura Atual

## Topologia
- **Monólito PHP** hospedado via PHP-FPM, servindo páginas e APIs REST.
- **Servidor WebSocket** (`backend/server.php`) rodando Ratchet na porta 8080.
- **MongoDB** como único banco, acessado diretamente pelos scripts PHP.
- **Clientes** web (Nginx renderiza PHP) e mobile (Flutter) conectam-se por HTTP(S) e WebSocket.

```
[Flutter/Web] --HTTP--> [Nginx] --fastcgi--> [PHP-FPM backend]
[Flutter/Web] --WS--> [Ratchet BingoChat]
[Backend & WS] --Mongo protocol--> [MongoDB]
```

## Componentes
| Camada | Elementos | Responsabilidade |
| --- | --- | --- |
| Interface | `backend/*.php`, `backend/assets`, Flutter `lib/screens` | Renderização de páginas, inputs e QR codes. |
| Aplicação | `backend/api/*.php`, `server.php` | Processa requisições REST, valida dados básicos, orquestra fluxo em tempo real. |
| Domínio | `backend/src/BingoChat.php` | Regras de sorteio, autenticação de operadores e gerenciamento de espectadores. |
| Infraestrutura | `docker-compose.yml`, `helm/*`, `docker/php/Dockerfile` | Provisionamento de containers e chart básico para K8s. |

## Fluxos Técnicos
1. **HTTP:** Nginx ([docker/nginx/default.conf](../docker/nginx/default.conf)) reescreve `/bingo/{shortId}` para `bingo.php?i=...` e encaminha outras rotas para o arquivo `.php` correspondente.
2. **Autenticação:** `api/login.php` consulta `users` no Mongo e retorna `{userId, email}`; não há tokens.
3. **Sessão:** `api/session.php` cria documento `sessions` com `shortId` e retorna IDs curto/longos.
4. **Tempo real:** `BingoChat::onMessage` interpreta mensagens (`authenticate_operator`, `viewer_joined`, `new_number`, etc.), persiste no Mongo quando necessário e envia broadcasts filtrando por `sessionId`.

## Dependências Internas
- Todos os scripts incluem `config/bootstrap.php`, que conecta ao Mongo via `MONGO_DSN` e injeta `$client` global.
- `BingoChat` armazena caches em memória (`$connectionData`, `$sessionViewers`, `$sessionMap`). Informação se perde quando o processo reinicia.

## Limitações Conhecidas
- Falta separação entre controllers, serviços e repositórios.
- Estado em memória do WebSocket não é compartilhado em múltiplas réplicas (escalabilidade horizontal limitada).
- Ausência de fila/eventos para desacoplar gravações ou notificações.
- Nenhum diagrama oficial foi produzido. //TODO adicionar diagramas C4 e sequência.
