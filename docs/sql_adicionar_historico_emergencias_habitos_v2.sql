-- Adicionar colunas de histórico de emergências e hábitos importantes à tabela perfis_medicos
-- Execute este script no phpMyAdmin ou cliente MySQL para o banco 'samed'

USE `samed`;

-- Adicionar as colunas se não existirem (usando abordagem condicional)
SET @historico_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'samed' AND TABLE_NAME = 'perfis_medicos' AND COLUMN_NAME = 'historico_emergencias');
SET @habitos_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'samed' AND TABLE_NAME = 'perfis_medicos' AND COLUMN_NAME = 'habitos_importantes');

SET @sql = CONCAT(
    'ALTER TABLE `perfis_medicos` ',
    IF(@historico_exists = 0, 'ADD COLUMN `historico_emergencias` TEXT AFTER `cirurgias`, ', ''),
    IF(@habitos_exists = 0, 'ADD COLUMN `habitos_importantes` TEXT AFTER `historico_emergencias`', ''),
    ';'
);

-- Remover vírgula extra se necessário
SET @sql = REPLACE(@sql, ', ;', ';');
SET @sql = REPLACE(@sql, ',;', ';');

-- Executar apenas se houver algo para alterar
IF @sql != 'ALTER TABLE `perfis_medicos` ;' THEN
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
ELSE
    SELECT 'Colunas já existem ou nada a alterar';
END IF;