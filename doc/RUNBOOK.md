# Runbook Operacional

## Subir Ambiente Local
1. Configurar `.env` com `MONGO_DSN`, `HOST_NAME`, `WSHOST_NAME` (usar valores seguros). //TODO adicionar template `.env.example`.
2. Executar `docker compose up --build` na raiz do repositório.
3. Acessar `http://localhost:8000` para o frontend; WebSocket exposto em `ws://localhost:8080`.
4. Verificar logs com `docker compose logs -f app websocket webserver`.

## Reiniciar Servidor WebSocket em Produção
1. Identificar pod/contêiner correspondente (`app-bingo-websocket`).
2. Executar `kubectl rollout restart deployment/app-bingo-websocket`. //TODO confirmar nome real dos manifests.
3. Monitorar métricas/conexões (ainda não implementadas) e logs para garantir que espectadores reconectem.

## Recuperação do MongoDB
- Backup/restore não documentado. //TODO definir rotina e ferramentas (mongodump/mongorestore, snapshots gerenciados).

## Incidentes Comuns
| Sintoma | Diagnóstico | Ação |
| --- | --- | --- |
| Operador não autentica no WebSocket | Ver logs do container `websocket`; conferir se `MONGO_DSN` está acessível e se IDs enviados são válidos. | Reiniciar WebSocket e validar documentos em `sessions`. |
| Espectadores não recebem números | Checar `BingoChat` para ver se operador está marcado como `isOperator`. | Reautenticar operador, verificar `sessionId` correto. |
| API retorna erro 500 ao iniciar | Variável `MONGO_DSN` ausente em `config/bootstrap.php`. | Exportar variáveis e reiniciar containers. |

## Escala / Manutenção
- Não há política automáticas de scaling; qualquer aumento de réplicas requer sincronização manual do estado em memória. //TODO definir procedimento caso horizontal scale seja necessário.
