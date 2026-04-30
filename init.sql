-- init.sql

-- Tabela para armazenar a rodada atual
CREATE TABLE IF NOT EXISTS bingo_round (
    current_round INTEGER NOT NULL,
    round_description VARCHAR(255)  NULL
);

DO $$
BEGIN
   IF NOT EXISTS (SELECT 1 FROM bingo_round) THEN
      INSERT INTO bingo_round (current_round) VALUES (1);
   END IF;
END
$$;

-- Tabela para os números sorteados
CREATE TABLE IF NOT EXISTS bingo_draws (
    id SERIAL PRIMARY KEY,
    round INTEGER NOT NULL,
    draw INTEGER NOT NULL,
    drawn_at TIMESTAMP DEFAULT NOW()
);

-- Tabela para armazenar os nomes dos ganhadores da rodada finalizada
CREATE TABLE IF NOT EXISTS bingo_winners (
    id SERIAL PRIMARY KEY,
    round INTEGER NOT NULL,
    winners TEXT NOT NULL,
    recorded_at TIMESTAMP DEFAULT NOW()
);

-- Tabela para usuários administradores (para autenticação)
CREATE TABLE IF NOT EXISTS admin_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM admin_users WHERE username='admin') THEN
    -- A senha abaixo é o hash da senha "admin123"
    INSERT INTO admin_users (username, password)
    VALUES ('admin', '$2y$10$HMKCk7fUha7OZiVNgRBrRuRtkyxlb99bjks9gva3QBqzQQVn2kAU6');
  END IF;
END
$$;
