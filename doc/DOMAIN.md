# Modelo de Domínio

## Entidades Principais
| Entidade | Fonte | Campos Observados | Observações |
| --- | --- | --- | --- |
| User | `backend/api/login.php` | `_id`, `email`, `password` | Password armazenado com `password_hash`. APIs subsequentes confiam no `userId` informado pelo cliente. |
| Session | `backend/api/session.php`, Mongo `sessions` | `_id`, `userId`, `shortId`, `sessionName`, `round`, `prize`, `status`, `isProUser`, `drawnNumbers[]`, `winners[]`, `createdAt` | `shortId` de 5 caracteres serve de chave pública; status sempre `active`. |
| Viewer (transiente) | `backend/src/BingoChat.php` | `viewerName`, `sessionId`, `resourceId` | Existente apenas em memória; usado para lista de espectadores conectados.

## Regras de Negócio Conhecidas
- Apenas operadores autenticados podem publicar `new_number` e `session_settings` no WebSocket (checado via `isOperator`).
- `session.php` garante unicidade de `shortId` via laço com `countDocuments`.
- `bingo_called` persiste campo `winners` no documento da sessão.

## Regras Implícitas / Lacunas
- Não há expiração automática de sessões ou mudança de `status`.
- Nenhum limite de espectadores por sessão ou throttling de mensagens.
- Históricos retornados em `api/history.php` incluem todos os campos sem paginação.
- Flutter app aparentemente reimplementa parte das regras; código não auditado. //TODO mapear regras no Flutter `lib/providers`.

## Pontos de Mistura Técnica x Domínio
- `BingoChat` combina lógica de domínio (autorização, sorteio) com detalhes técnicos (conexões, logging).
- Scripts REST executam validação, persistência e formatação de resposta no mesmo arquivo.
- Falta camada para aplicar invariantes (por exemplo, garantir que número sorteado não repete no array `drawnNumbers`).
