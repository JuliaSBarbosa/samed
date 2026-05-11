-- Adicionar colunas de histórico de emergências e hábitos importantes à tabela perfis_medicos
-- Execute este script no phpMyAdmin ou cliente MySQL para o banco 'samed'

USE `samed`;

-- Verificar se a coluna 'historico_emergencias' existe e, se não, adicionar
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'samed' AND TABLE_NAME = 'perfis_medicos' AND COLUMN_NAME = 'historico_emergencias') = 0,
    'ALTER TABLE `perfis_medicos` ADD COLUMN `historico_emergencias` TEXT AFTER `cirurgias`;',
    'SELECT ''Coluna historico_emergencias já existe'';'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar se a coluna 'habitos_importantes' existe e, se não, adicionar
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'samed' AND TABLE_NAME = 'perfis_medicos' AND COLUMN_NAME = 'habitos_importantes') = 0,
    'ALTER TABLE `perfis_medicos` ADD COLUMN `habitos_importantes` TEXT AFTER `historico_emergencias`;',
    'SELECT ''Coluna habitos_importantes já existe'';'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
