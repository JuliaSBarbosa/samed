-- ============================================
-- INSERIR ADMIN PADRÃO (uma vez por ambiente)
-- ============================================
-- Rodar DEPOIS de: sql_validacao_profissionais.sql (precisa da coluna status_validacao)
-- e DEPOIS de sql_adicionar_tipo_admin.sql se o tipo for ENUM sem 'admin'.
--
-- Login após importar:
--   E-mail: admin@samed.com
--   Senha:  Admin@123
-- ============================================

USE `samed`;

INSERT INTO usuarios (nome, email, senha, tipo, crm, coren, status_validacao)
SELECT
    'Administrador SAMED',
    'admin@samed.com',
    '$2y$10$wepkKLwtRV28ILZFAusDVuuPOBssjh7qBFjt0AtYBWdvQVU9dzP32',
    'admin',
    NULL,
    NULL,
    'aprovado'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'admin@samed.com' LIMIT 1);
