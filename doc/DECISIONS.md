# Registro de Decisões Arquiteturais (ADR)

## ADR-001: Stack Monolítica PHP + Ratchet
- **Contexto:** Projeto inicial precisava de solução rápida para sorteios em tempo real usando tecnologias familiares.
- **Decisão:** Utilizar PHP 8.1 com Ratchet WebSocket, MongoDB e app Flutter como cliente principal.
- **Consequências:** Simplicidade inicial, porém alto acoplamento e dificuldades para escalar múltiplas réplicas do WebSocket.

## ADR-002: IDs curtos para sessões
- **Contexto:** Operadores compartilham código com espectadores em transmissões/TV.
- **Decisão:** Gerar `shortId` de 5 caracteres alfanuméricos no endpoint `api/session.php` e persistir em Mongo.
- **Consequências:** UX simples, mas risco de colisão (mitigado por verificação manual) e ausência de validação criptográfica.

//TODO registrar decisões futuras (ex.: autenticação baseada em tokens, adoção de observabilidade, estratégias de deploy).
