-- BLT Gamer: migração do painel administrativo
-- Execute uma vez no banco "blt" se ele já existir.
ALTER TABLE usuarios ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER ativo;
-- Depois, torne sua conta de administrador substituindo o e-mail abaixo:
-- UPDATE usuarios SET is_admin = 1 WHERE email = 'SEU_EMAIL_AQUI';
