# DevOps e Entrega

## Infraestrutura Existente
- **Docker Compose:** `docker-compose.yml` sobe `app` (PHP-FPM), `websocket`, `webserver` (Nginx) e `db` (Mongo). Não publica Mongo externamente.
- **Dockerfiles:** `docker/php/Dockerfile` instala extensões (sockets, mongodb) e executa `composer install`. Há variantes para app/web/websocket no diretório `docker/php`.
- **Nginx:** Configuração em `docker/nginx/default.conf` realiza rewrite para `/bingo/{shortId}`.
- **Kubernetes:** Charts em `helm/app`, `helm/web`, `helm/websocket` com valores default apontando para `registry.gitlab.com`. Probes são TCP e `envFrom` referencia `app-secret-app` (não incluso).

## CI/CD
- Não há pipelines GitLab configurados no repositório (README padrão aponta passos iniciais).
- Builds e deploys provavelmente manuais.

## Versionamento e Deploy
- Docker image `app-main` utiliza `pullPolicy: Always`, mas `tag` vazia depende do `appVersion` default (`1.16.0`).
- Sem registro de versões no repositório (`CHANGELOG.md` inexistente até este commit).
- Rollback depende de reverter manualmente imagens/containers.

## Recomendações
1. Configurar GitLab CI com estágios: `lint`, `test`, `build`, `scan`, `deploy`.
2. Adotar multi-stage Dockerfile separando dependências de desenvolvimento e produção.
3. Definir tags semânticas para imagens e atualizar Helm charts para consumi-las.
4. Declarar ConfigMaps/Secrets em Helm para `MONGO_DSN`, `HOST_NAME`, `WSHOST_NAME`.
5. Documentar processo de deploy/rollback no RUNBOOK (ver `RUNBOOK.md`).

## Pendências
- Terraform/Infra as Code para provisionar clusters, buckets de backup, etc. //TODO avaliar necessidade.
- Automatização de backups do MongoDB. //TODO definir estratégia.
