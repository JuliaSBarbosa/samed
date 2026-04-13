-- =============================================================================
-- SAMED — script para RECRIAR o banco do zero (estrutura principal do projeto)
-- =============================================================================
-- Onde ficam os dados no XAMPP: pasta C:\xampp\mysql\data\ (não é dentro de htdocs)
-- Este arquivo fica no projeto em: docs/sql_schema_samed_completo.sql
--
-- Como usar (phpMyAdmin ou cliente MySQL):
--   1. Abra este arquivo e execute tudo (ou por partes).
--   2. Depois rode (se ainda não tiver admin): docs/sql_adicionar_tipo_admin.sql
--      e use criar_admin.php ou INSERT manual.
--
-- ATENÇÃO: apaga as tabelas listadas abaixo se já existirem no banco "samed".
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `samed` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `samed`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `historico_acessos`;
DROP TABLE IF EXISTS `contatos_emergencia`;
DROP TABLE IF EXISTS `perfis_medicos`;
DROP TABLE IF EXISTS `dependentes`;
DROP TABLE IF EXISTS `usuarios`;

SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------------------------------
-- usuarios (login, CRM/COREN, validação facial/documental)
-- -----------------------------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` varchar(32) NOT NULL DEFAULT 'paciente' COMMENT 'paciente | medico | enfermeiro | admin',
  `crm` varchar(32) DEFAULT NULL,
  `coren` varchar(32) DEFAULT NULL,
  `status_validacao` varchar(20) NOT NULL DEFAULT 'aprovado',
  `foto_documento` varchar(255) DEFAULT NULL,
  `foto_selfie` varchar(255) DEFAULT NULL,
  `data_validacao` datetime DEFAULT NULL,
  `validado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `crm` (`crm`),
  UNIQUE KEY `coren` (`coren`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- dependentes (vinculados ao paciente usuario_id = paciente_id)
-- -----------------------------------------------------------------------------
CREATE TABLE `dependentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `sexo` varchar(32) DEFAULT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  CONSTRAINT `fk_dependentes_usuario` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- perfis_medicos (ficha do titular OU do dependente — um dos dois preenchido)
-- -----------------------------------------------------------------------------
CREATE TABLE `perfis_medicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL COMMENT 'titular; NULL se for perfil de dependente',
  `dependente_id` int(11) DEFAULT NULL COMMENT 'NULL se for titular',
  `data_nascimento` date DEFAULT NULL,
  `sexo` varchar(32) DEFAULT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `tipo_sanguineo` varchar(16) DEFAULT NULL,
  `doencas_cronicas` text,
  `alergias` text,
  `medicacao_continua` text,
  `doenca_mental` text,
  `dispositivo_implantado` text,
  `info_relevantes` text,
  `cirurgias` text,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `autorizacao_usuario` varchar(8) NOT NULL DEFAULT 'nao',
  `compartilhar_localizacao` varchar(8) NOT NULL DEFAULT 'nao',
  `data_atualizacao` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `dependente_id` (`dependente_id`),
  CONSTRAINT `fk_pm_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pm_dependente` FOREIGN KEY (`dependente_id`) REFERENCES `dependentes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- contatos_emergencia (titular OU dependente)
-- -----------------------------------------------------------------------------
CREATE TABLE `contatos_emergencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `dependente_id` int(11) DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `parentesco` varchar(64) DEFAULT NULL,
  `telefone` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `dependente_id` (`dependente_id`),
  CONSTRAINT `fk_ce_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ce_dependente` FOREIGN KEY (`dependente_id`) REFERENCES `dependentes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- historico_acessos (quem consultou qual paciente / dependente)
-- -----------------------------------------------------------------------------
CREATE TABLE `historico_acessos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profissional_id` int(11) NOT NULL,
  `paciente_id` int(11) DEFAULT NULL,
  `dependente_id` int(11) DEFAULT NULL,
  `tipo_acesso` varchar(255) DEFAULT NULL,
  `registro_profissional` varchar(64) DEFAULT NULL,
  `data_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `profissional_id` (`profissional_id`),
  KEY `paciente_id` (`paciente_id`),
  KEY `dependente_id` (`dependente_id`),
  CONSTRAINT `fk_hist_prof` FOREIGN KEY (`profissional_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hist_pac` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hist_dep` FOREIGN KEY (`dependente_id`) REFERENCES `dependentes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- Fim. Opcional: criar admin com criar_admin.php após o MySQL estar estável.
-- =============================================================================
