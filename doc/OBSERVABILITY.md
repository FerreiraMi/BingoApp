# Observabilidade e Operação

## Logs
- Backend REST e WebSocket usam `echo`/`print_r` para logar mensagens, sem níveis ou estrutura.
- Não há correlação entre requests e mensagens de WebSocket.
- Logs ficam no stdout dos containers, sem agregação central.

## Métricas
- Nenhuma métrica customizada exposta.
- Helm charts apenas verificam porta TCP 9000 para liveness/readiness.

## Tracing
- Não há tracing distribuído, spans ou IDs de correlação nos headers.

## Health Checks
- `helm/app/values.yaml` define probes TCP (9000), porém API HTTP roda em Nginx (porta 80) e WebSocket em 8080; health real não é validado.

## Erros e Resiliência
- Endpoints REST retornam JSON simples com `success/error`, sem códigos padronizados.
- WebSocket captura exceções genéricas e apenas loga string.
- Não há retry/circuit breaker em clientes Flutter/web.

## Próximos Passos
1. Adotar biblioteca PSR-3 (Monolog) e definir formato de log com campos `timestamp`, `corrId`, `sessionId`.
2. Instrumentar métricas Prometheus (contadores de mensagens, conexões ativas, latência de API).
3. Adicionar endpoint `/healthz` e `/readyz` em PHP para probes HTTP reais.
4. Incluir tracing (OpenTelemetry PHP/Flutter) para relacionar requisições e eventos.
5. Criar runbooks descrevendo como coletar logs e interpretar métricas. //TODO detalhar ferramentas escolhidas.
