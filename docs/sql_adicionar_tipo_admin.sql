-- ============================================
-- PERMITIR TIPO "admin" NA TABELA usuarios
-- ============================================
-- No banco, a coluna "tipo" muitas vezes é ENUM só com:
--   paciente, medico, enfermeiro
-- Por isso o INSERT do admin falha: "admin" não existe no ENUM.
--
-- Execute este script no banco "samed" ANTES de criar o usuário admin.
-- Depois rode: http://localhost/samed-1/criar_admin.php
-- ============================================

-- Se a coluna tipo for ENUM, use um destes (dependendo do que você tem hoje):

-- Opção A: coluna atualmente ENUM('paciente','medico','enfermeiro')
ALTER TABLE usuarios
MODIFY COLUMN tipo ENUM('paciente', 'medico', 'enfermeiro', 'admin') NOT NULL;

-- Se der erro (ex.: "Duplicate column" ou tipo for VARCHAR), a coluna já aceita qualquer texto.
-- Nesse caso não precisa alterar nada; só criar o usuário com criar_admin.php.
