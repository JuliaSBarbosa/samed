-- Alterações para validação de médicos e enfermeiros
-- Execute estes comandos no banco de dados "samed"

ALTER TABLE usuarios
    ADD COLUMN status_validacao VARCHAR(20) NOT NULL DEFAULT 'aprovado' AFTER coren,
    ADD COLUMN foto_documento  VARCHAR(255) NULL AFTER status_validacao,
    ADD COLUMN foto_selfie     VARCHAR(255) NULL AFTER foto_documento,
    ADD COLUMN data_validacao  DATETIME NULL AFTER foto_selfie,
    ADD COLUMN validado_por    INT NULL AFTER data_validacao;

-- Observações:
-- - Usuários existentes ficarão com status_validacao = 'aprovado'
-- - Novos médicos/enfermeiros terão status_validacao = 'pendente' (ajustado no PHP)
-- - O admin poderá aprovar/reprovar pelo painel de validações

