-- Adicionar colunas 'fuma' e 'bebe' à tabela perfis_medicos se não existirem
-- Execute este script no phpMyAdmin ou cliente MySQL para o banco 'samed'

USE `samed`;

-- Verificar se a coluna 'fuma' existe, se não, adicionar
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'samed' AND TABLE_NAME = 'perfis_medicos' AND COLUMN_NAME = 'fuma') = 0,
    'ALTER TABLE `perfis_medicos` ADD COLUMN `fuma` VARCHAR(8) NOT NULL DEFAULT ''nao'' AFTER `tipo_sanguineo`;',
    'SELECT ''Coluna fuma já existe'';'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar se a coluna 'bebe' existe, se não, adicionar
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'samed' AND TABLE_NAME = 'perfis_medicos' AND COLUMN_NAME = 'bebe') = 0,
    'ALTER TABLE `perfis_medicos` ADD COLUMN `bebe` VARCHAR(8) NOT NULL DEFAULT ''nao'' AFTER `fuma`;',
    'SELECT ''Coluna bebe já existe'';'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;