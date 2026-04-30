# Segurança

## Controles Existentes
- Senhas de usuários armazenadas com `password_hash` (ver `backend/api/login.php`).
- Conexão com MongoDB depende de `MONGO_DSN` provido por variável de ambiente.
- WebSocket exige mensagem `authenticate_operator` antes de permitir `new_number` ou `session_settings`.

## Riscos Identificados
| Categoria | Descrição | Evidência |
| --- | --- | --- |
| Autenticação/API | Endpoints aceitam `userId` informado pelo cliente sem verificar token ou sessão. | `backend/api/session.php`, `history.php` utilizam `$_POST['userId']`/`$_GET['userId']`. |
| CORS | Todos os endpoints liberam `Access-Control-Allow-Origin: *`. | `login.php`, `session.php`, `history.php`. |
| Exposição de logs | `BingoChat::logMessage` imprime IPs e IDs de usuários no stdout. | `backend/src/BingoChat.php`. |
| Secrets | `config/vars.php` define defaults embutidos; Helm usa `envFrom secretRef` sem especificação. | `backend/config/vars.php`, `helm/*/values.yaml`. |
| Transporte | Falta documentação ou enforcement de HTTPS/WSS nos ambientes locais. | docker-compose usa `ws://`. |

## Recomendações
1. Implementar autenticação baseada em tokens (JWT/OAuth) e middleware compartilhado para APIs e WebSocket.
2. Restringir CORS a domínios confiáveis e habilitar `Access-Control-Allow-Credentials` quando necessário.
3. Centralizar logs via PSR-3 e mascarar dados sensíveis.
4. Gerenciar secrets via `.env` (local), Kubernetes Secrets e vault apropriado; remover defaults em `vars.php`.
5. Adicionar rate limiting e validações explícitas de `ObjectId` (usar `ObjectId::isValid`).

## Próximos Artefatos
- Threat model formal e matriz STRIDE. //TODO elaborar.
- Procedimento de resposta a incidentes e checklist OWASP ASVS. //TODO documentar.
