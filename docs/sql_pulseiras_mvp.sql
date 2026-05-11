USE `samed`;

CREATE TABLE IF NOT EXISTS `pulseiras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid_tag` varchar(64) NOT NULL,
  `tipo_tag` varchar(32) NOT NULL DEFAULT 'NTAG215',
  `payload_ndef` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'disponivel' COMMENT 'disponivel | vinculada | bloqueada | erro',
  `ultimo_device_id` varchar(64) DEFAULT NULL,
  `ultima_leitura_em` datetime DEFAULT NULL,
  `ultima_gravacao_em` datetime DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_tag` (`uid_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pulseira_vinculos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pulseira_id` int(11) NOT NULL,
  `perfil_medico_id` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `vinculado_por` int(11) DEFAULT NULL,
  `vinculado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `desvinculado_em` datetime DEFAULT NULL,
  `observacao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pulseira_id` (`pulseira_id`),
  KEY `perfil_medico_id` (`perfil_medico_id`),
  KEY `ativo` (`ativo`),
  CONSTRAINT `fk_pulseira_vinculos_pulseira` FOREIGN KEY (`pulseira_id`) REFERENCES `pulseiras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pulseira_vinculos_perfil` FOREIGN KEY (`perfil_medico_id`) REFERENCES `perfis_medicos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pulseira_vinculos_usuario` FOREIGN KEY (`vinculado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pulseira_comandos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(64) NOT NULL,
  `acao` varchar(20) NOT NULL COMMENT 'gravar | ler | esquecer',
  `perfil_medico_id` int(11) DEFAULT NULL,
  `solicitante_id` int(11) DEFAULT NULL,
  `pulseira_id` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pendente' COMMENT 'pendente | em_execucao | sucesso | erro | expirado',
  `payload_desejado` longtext DEFAULT NULL,
  `resultado_json` longtext DEFAULT NULL,
  `erro_mensagem` varchar(255) DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `processado_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_status` (`device_id`, `status`),
  KEY `perfil_medico_id` (`perfil_medico_id`),
  KEY `solicitante_id` (`solicitante_id`),
  KEY `pulseira_id` (`pulseira_id`),
  CONSTRAINT `fk_pulseira_comandos_perfil` FOREIGN KEY (`perfil_medico_id`) REFERENCES `perfis_medicos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pulseira_comandos_usuario` FOREIGN KEY (`solicitante_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pulseira_comandos_pulseira` FOREIGN KEY (`pulseira_id`) REFERENCES `pulseiras` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pulseira_eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pulseira_id` int(11) DEFAULT NULL,
  `comando_id` int(11) DEFAULT NULL,
  `tipo_evento` varchar(32) NOT NULL,
  `uid_tag` varchar(64) DEFAULT NULL,
  `payload_ndef` text DEFAULT NULL,
  `device_id` varchar(64) DEFAULT NULL,
  `detalhes_json` longtext DEFAULT NULL,
  `sucesso` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pulseira_id` (`pulseira_id`),
  KEY `comando_id` (`comando_id`),
  CONSTRAINT `fk_pulseira_eventos_pulseira` FOREIGN KEY (`pulseira_id`) REFERENCES `pulseiras` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pulseira_eventos_comando` FOREIGN KEY (`comando_id`) REFERENCES `pulseira_comandos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
