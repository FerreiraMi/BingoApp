# Contribuindo

## Requisitos
- PHP 8.1+ com Composer
- Node/Flutter (opcional) para trabalhar no app móvel
- Docker Desktop para rodar o stack localmente

## Setup
1. `composer install` dentro de `backend/` para instalar dependências PHP.
2. `flutter pub get` dentro de `flutter_app/` se for atuar no app.
3. Configure as variáveis `MONGO_DSN`, `HOST_NAME`, `WSHOST_NAME` (use `.env` ou export).
4. Execute `docker compose up --build` para disponibilizar HTTP/WS/Mongo.

## Convenções de Código
- Seguir PSR-12 para PHP (configuração de linters ainda não disponível). //TODO adicionar PHP-CS-Fixer.
- Para Flutter usar `dart format` e `flutter analyze`.
- Nomear branches com padrão `feat/<descricao>` ou `fix/<descricao>`.

## Testes
- Backend: `cd backend && ./vendor/bin/phpunit`.
- Flutter: `cd flutter_app && flutter test` (não há testes atualmente, mas comando deve ser mantido). //TODO adicionar casos reais.

## Processo de Merge
1. Abrir merge request no GitLab descrevendo mudanças e impactos.
2. Garantir que o pipeline (quando existir) esteja verde.
3. Solicitar revisão de pelo menos um membro do time.
4. Após merge, atualizar documentação em `doc/` conforme necessário.
