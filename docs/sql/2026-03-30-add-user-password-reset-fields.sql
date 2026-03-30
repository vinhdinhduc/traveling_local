-- Migration: Add password reset fields for users
-- Date: 2026-03-30

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS reset_password_token VARCHAR(64) NULL AFTER password,
    ADD COLUMN IF NOT EXISTS reset_password_expires_at DATETIME NULL AFTER reset_password_token;
