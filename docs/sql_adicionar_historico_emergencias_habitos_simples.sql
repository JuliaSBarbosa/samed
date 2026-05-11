-- Adicionar colunas de histórico de emergências e hábitos importantes à tabela perfis_medicos
-- Execute este script no phpMyAdmin ou cliente MySQL para o banco 'samed'

USE `samed`;

ALTER TABLE `perfis_medicos`
  ADD COLUMN `historico_emergencias` TEXT AFTER `cirurgias`,
  ADD COLUMN `habitos_importantes` TEXT AFTER `historico_emergencias`;