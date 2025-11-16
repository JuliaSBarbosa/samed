-- ============================================
-- BANCO DE DADOS SAMED
-- Sistema de Agendamento Médico
-- ============================================
-- 
-- INSTRUÇÕES:
-- 1. Execute este script no phpMyAdmin ou MySQL
-- 2. Certifique-se de que o MySQL está rodando
-- 3. Este script cria o banco de dados e a tabela de usuários
--
-- ============================================

CREATE DATABASE IF NOT EXISTS samed CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE samed;

-- ============================================
-- TABELA DE USUÁRIOS
-- ============================================
-- Armazena pacientes, médicos e enfermeiros
-- 
-- Campos:
-- - id: Identificador único (auto-incremento)
-- - nome: Nome completo do usuário (até 150 caracteres)
-- - email: E-mail único para login
-- - senha: Senha criptografada (hash)
-- - tipo: Tipo de usuário (paciente, medico, enfermeiro)
-- - crm: Registro no Conselho Regional de Medicina (apenas para médicos)
-- - coren: Registro no Conselho Regional de Enfermagem (apenas para enfermeiros)
-- - data_cadastro: Data e hora do cadastro (automático)
-- ============================================

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('paciente', 'medico', 'enfermeiro') NOT NULL,
    crm VARCHAR(20) NULL,
    coren VARCHAR(20) NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_crm (crm),
    INDEX idx_coren (coren)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

