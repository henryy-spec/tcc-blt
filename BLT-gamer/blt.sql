-- ============================================================
-- BLUE LIGHT - BANCO FINAL
-- Execute este arquivo no MySQL/phpMyAdmin.
-- ATENÇÃO: DROP DATABASE apaga o banco blt existente.
-- ============================================================

DROP DATABASE IF EXISTS blt;
CREATE DATABASE blt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blt;

CREATE TABLE usuarios (
    id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_login DATETIME NULL,
    assinatura_ate DATETIME NULL,
    xp INT NOT NULL DEFAULT 0,
    nivel INT NOT NULL DEFAULT 1,
    primeira_assinatura TINYINT(1) NOT NULL DEFAULT 0,
    primeiro_jogo TINYINT(1) NOT NULL DEFAULT 0,
    ultimo_xp_login DATE NULL,
    foto_perfil VARCHAR(255) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    INDEX idx_usuarios_email (email),
    INDEX idx_usuarios_ativo (ativo)
) ENGINE=InnoDB;

CREATE TABLE usuarios_inativos (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    motivo VARCHAR(255) NULL,
    inativado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reativado_em DATETIME NULL,
    PRIMARY KEY (id),
    INDEX idx_inativos_usuario (usuario_id),
    INDEX idx_inativos_email (email),
    CONSTRAINT fk_inativos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE planos (
    id INT NOT NULL AUTO_INCREMENT,
    slug VARCHAR(30) NOT NULL UNIQUE,
    nome VARCHAR(50) NOT NULL,
    valor DECIMAL(8,2) NOT NULL,
    dias INT NOT NULL DEFAULT 30,
    ram VARCHAR(50) NULL,
    ssd VARCHAR(50) NULL,
    suporte VARCHAR(100) NULL,
    descricao TEXT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE assinaturas (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    plano VARCHAR(30) NOT NULL,
    valor DECIMAL(8,2) NOT NULL,
    metodo VARCHAR(20) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pendente',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_assinaturas_usuario (usuario_id),
    CONSTRAINT fk_assinaturas_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE jogos (
    id INT NOT NULL AUTO_INCREMENT,
    slug VARCHAR(60) NOT NULL UNIQUE,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    genero VARCHAR(50) NULL,
    classificacao VARCHAR(10) NULL,
    tamanho_gb DECIMAL(5,2) NULL,
    desenvolvedora VARCHAR(100) NULL,
    data_lancamento DATE NULL,
    capa VARCHAR(255) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE biblioteca (
    usuario_id INT NOT NULL,
    jogo_id INT NOT NULL,
    data_adicionado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, jogo_id),
    CONSTRAINT fk_biblioteca_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_biblioteca_jogo
        FOREIGN KEY (jogo_id) REFERENCES jogos(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pagamentos (
    id INT NOT NULL AUTO_INCREMENT,
    assinatura_id INT NULL,
    valor DECIMAL(8,2) NOT NULL,
    forma_pagamento VARCHAR(20) NOT NULL,
    data_pagamento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) NOT NULL DEFAULT 'pendente',
    PRIMARY KEY (id),
    INDEX idx_pagamentos_assinatura (assinatura_id),
    CONSTRAINT fk_pagamentos_assinatura
        FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE atividades (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_atividades_usuario (usuario_id),
    CONSTRAINT fk_atividades_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE favoritos (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    jogo_slug VARCHAR(60) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_favorito_usuario_jogo (usuario_id, jogo_slug),
    CONSTRAINT fk_favoritos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE conquistas (
    id INT NOT NULL AUTO_INCREMENT,
    slug VARCHAR(60) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    icone VARCHAR(100) NOT NULL DEFAULT 'fa-solid fa-trophy',
    categoria VARCHAR(30) NOT NULL DEFAULT 'geral',
    raridade VARCHAR(20) NOT NULL DEFAULT 'comum',
    xp INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE usuario_conquistas (
    usuario_id INT NOT NULL,
    conquista_id INT NOT NULL,
    desbloqueada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, conquista_id),
    CONSTRAINT fk_uc_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_uc_conquista
        FOREIGN KEY (conquista_id) REFERENCES conquistas(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tokens_senha (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expira_em DATETIME NOT NULL,
    usado TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_tokens_usuario (usuario_id),
    INDEX idx_tokens_expiracao (expira_em),
    CONSTRAINT fk_tokens_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO planos (slug,nome,valor,dias,ram,ssd,suporte,descricao) VALUES
('basico','Básico',19.90,30,'1 GB RAM','10 GB SSD','Suporte Básico','Plano básico da Blue Light.'),
('premium','Premium',49.90,30,'4 GB RAM','50 GB SSD','Suporte Prioritário','Plano premium da Blue Light.'),
('enterprise','Enterprise',99.90,30,'8 GB RAM','100 GB SSD','Suporte 24/7','Plano Enterprise da Blue Light.');

INSERT INTO jogos (slug,titulo,descricao,genero,classificacao,desenvolvedora,capa) VALUES
('chronocide','Chronocide','Aventura e mistério em diferentes épocas.','Aventura','Livre','Blue Light Team','darkhouse.jpg');

INSERT INTO conquistas (slug,nome,descricao,icone,categoria,raridade,xp) VALUES
('bem_vindo','Bem-vindo','Criou uma conta na Blue Light.','fa-solid fa-door-open','geral','comum',50),
('primeiro_favorito','Primeiro Favorito','Favoritou seu primeiro jogo.','fa-solid fa-heart','jogos','comum',50),
('primeira_assinatura','Primeira Assinatura','Realizou sua primeira assinatura.','fa-solid fa-crown','assinatura','raro',100),
('primeiro_jogo','Primeiro Jogo','Começou a jogar pela primeira vez.','fa-solid fa-gamepad','jogos','comum',100),
('explorador','Explorador','Visitou diferentes áreas da plataforma Blue Light.','fa-solid fa-compass','exploracao','raro',150),
('nivel_5','Nível 5','Alcançou o nível 5.','fa-solid fa-star','especial','epico',300),
('colecionador','Colecionador','Construiu uma coleção de jogos.','fa-solid fa-layer-group','jogos','raro',200),
('maratonista','Maratonista','Manteve uma assinatura ativa por um longo período.','fa-solid fa-fire','assinatura','epico',300),
('veterano','Veterano','É um usuário veterano da Blue Light.','fa-solid fa-medal','especial','lendario',1000),
('fa_da_blt','Fã da BLT','Adicionou Chronocide aos favoritos.','fa-solid fa-heart-circle-check','especial','lendario',500);

DELIMITER $$

CREATE TRIGGER trg_usuario_inativado
AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF OLD.ativo = 1 AND NEW.ativo = 0 THEN
        INSERT INTO usuarios_inativos (usuario_id, nome, email, motivo)
        VALUES (NEW.id, NEW.nome, NEW.email, 'Conta marcada como inativa');
    END IF;
END$$

CREATE TRIGGER trg_usuario_reativado
AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF OLD.ativo = 0 AND NEW.ativo = 1 THEN
        UPDATE usuarios_inativos
        SET reativado_em = NOW()
        WHERE usuario_id = NEW.id AND reativado_em IS NULL;
    END IF;
END$$

DELIMITER ;

-- Conferência
SHOW TABLES;
SELECT id, slug, nome, valor FROM planos;
SELECT id, slug, nome, xp FROM conquistas;
