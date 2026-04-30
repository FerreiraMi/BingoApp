# Roadmap Priorizado

## Curto Prazo (0-2 meses)
| Item | Impacto | Complexidade | Status |
| --- | --- | --- | --- |
| Implementar autenticação/tokenização para APIs e WebSocket | Alta | Média | Em análise |
| Refatorar `BingoChat` em serviços menores com testes adicionais | Média | Média | Não iniciado |
| Configurar pipeline GitLab CI (lint, PHPUnit, Flutter tests, build Docker) | Alta | Média | Não iniciado |
| Restringir CORS e externalizar secrets | Média | Baixa | Não iniciado |

## Médio Prazo (2-6 meses)
| Item | Impacto | Complexidade | Status |
| --- | --- | --- | --- |
| Introduzir observabilidade (logs estruturados, métricas, health endpoints) | Alta | Média | Não iniciado |
| Expandir suíte de testes (integração, contrato, E2E) | Alta | Alta | Não iniciado |
| Documentar arquitetura/domínio completo com diagramas | Média | Baixa | Em andamento |

## Longo Prazo (6-12 meses)
| Item | Impacto | Complexidade | Status |
| --- | --- | --- | --- |
| Avaliar modularização ou microsserviços para separar autenticação/sessões | Média | Alta | Não iniciado |
| Implementar autoscaling e mecanismos de resiliente (HPA, retries, circuit breaker) | Alta | Alta | Não iniciado |
| Estabelecer programa de segurança contínuo (SAST, DAST, pentest) | Alta | Média | Não iniciado |

## Quick Wins
- Ajustar `config/vars.php` para usar apenas `getenv` sem defaults sensíveis.
- Criar scripts `make lint` / `make test` para padronizar comandos locais.
- Documentar processo de subida local no RUNBOOK.
