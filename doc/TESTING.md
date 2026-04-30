# Estratégia de Testes

## Situação Atual
- `phpunit.xml` configura suíte única apontando para `backend/tests`.
- Testes implementados:
  - `tests/BingoChatTest.php`: cobre autenticação de operador, broadcast e alguns fluxos de erro usando mocks.
  - `tests/SessionApiTest.php`: valida geração de `shortId` e estrutura de documento, porém desacoplado do endpoint real.
- Não há testes automatizados para outros endpoints (`login.php`, `history.php`, etc.), nem para o aplicativo Flutter.
- Não existe pipeline de CI configurado para executar a suíte.

## Lacunas Identificadas
| Área | Status | Observação |
| --- | --- | --- |
| API REST | Ausente | Nenhum teste de integração com Mongo real/test container. |
| Flutter | Ausente | Apenas dependências para `flutter_test`/`mocktail`; nenhum teste versionado. |
| E2E web | Ausente | Sem testes que simulem fluxo operador + espectador. |
| Performance | Ausente | Sem testes de carga para WebSocket ou Mongo. |

## Próximos Passos Propostos
1. Criar camada de serviços para permitir testes unitários sem depender de scripts globais.
2. Utilizar `mongodb/mongodb` com `mongodb-memory-server` ou contêiner dedicado em ambiente de testes.
3. Adicionar testes de contrato (JSON schema/Pact) entre Flutter e APIs.
4. Implementar suíte E2E com Cypress/Playwright para validar rota `/bingo/{shortId}` e WebSocket.
5. Automatizar execução via GitLab CI.

## Comandos
- Backend: `cd backend && ./vendor/bin/phpunit` (necessário instalar dependências antes com `composer install`).
- Flutter: //TODO documentar comandos de testes quando existirem.
