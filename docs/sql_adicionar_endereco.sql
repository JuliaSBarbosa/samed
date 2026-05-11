-- Adicionar colunas de endereço à tabela perfis_medicos
USE `samed`;

ALTER TABLE `perfis_medicos` ADD COLUMN `cep` VARCHAR(20) DEFAULT NULL AFTER `email`;
ALTER TABLE `perfis_medicos` ADD COLUMN `rua` VARCHAR(255) DEFAULT NULL AFTER `cep`;
ALTER TABLE `perfis_medicos` ADD COLUMN `numero` VARCHAR(50) DEFAULT NULL AFTER `rua`;
ALTER TABLE `perfis_medicos` ADD COLUMN `complemento` VARCHAR(255) DEFAULT NULL AFTER `numero`;
ALTER TABLE `perfis_medicos` ADD COLUMN `bairro` VARCHAR(100) DEFAULT NULL AFTER `complemento`;
ALTER TABLE `perfis_medicos` ADD COLUMN `cidade` VARCHAR(100) DEFAULT NULL AFTER `bairro`;
ALTER TABLE `perfis_medicos` ADD COLUMN `estado` VARCHAR(100) DEFAULT NULL AFTER `cidade`;
