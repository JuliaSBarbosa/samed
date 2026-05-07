-- ============================================
-- TABELA DE DENÚNCIAS DE ACESSOS À FICHA MÉDICA
-- ============================================
-- Banco: samed
-- Permite que o paciente denuncie um acesso indevido à sua ficha
-- (ou à ficha de um dependente seu) feito por um profissional.
-- ============================================

USE `samed`;

CREATE TABLE IF NOT EXISTS `denuncias_acessos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `historico_acesso_id` int(11) NOT NULL,
  `denunciante_id` int(11) NOT NULL,
  `motivo` varchar(64) NOT NULL,
  `descricao` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pendente',
  `data_denuncia` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `historico_acesso_id` (`historico_acesso_id`),
  KEY `denunciante_id` (`denunciante_id`),
  CONSTRAINT `fk_denuncia_historico` FOREIGN KEY (`historico_acesso_id`) REFERENCES `historico_acessos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_denuncia_user` FOREIGN KEY (`denunciante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
