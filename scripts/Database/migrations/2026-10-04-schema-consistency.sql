-- Migration unique depuis le schema audite le 04/10/2026 (MySQL 9.1).
-- Sauvegarder avant execution : ALTER TABLE valide implicitement les transactions.
-- Ne pas reimporter le dump phpMyAdmin qui contient des DROP TABLE.
-- Les valeurs des notes et les XP existants sont conserves.
ALTER TABLE manga
    MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    MODIFY numero INT UNSIGNED NOT NULL,
    MODIFY editeur VARCHAR(100) NULL DEFAULT NULL,
    MODIFY jacquette TINYINT UNSIGNED NULL DEFAULT NULL COMMENT 'Note de la jaquette, 1 a 5 ; NULL si non evaluee',
    MODIFY livre_note TINYINT UNSIGNED NULL DEFAULT NULL COMMENT 'Note du livre, 1 a 5 ; NULL si non evalue',
    MODIFY note TINYINT UNSIGNED NULL DEFAULT NULL COMMENT 'Somme des notes ; NULL si une note manque',
    RENAME INDEX idx_numero TO idx_manga_numero,
    RENAME INDEX idx_lu_reward TO idx_manga_lu_xp_read_rewarded,
    ADD CONSTRAINT chk_manga_numero CHECK (numero >= 1),
    ADD CONSTRAINT chk_manga_flags CHECK (lu IN (0,1) AND xp_read_rewarded IN (0,1) AND xp_series_rewarded IN (0,1)),
    ADD CONSTRAINT chk_manga_statut CHECK (statut IN ('en_cours','termine')),
    ADD CONSTRAINT chk_manga_notes CHECK ((jacquette IS NULL OR jacquette BETWEEN 1 AND 5) AND (livre_note IS NULL OR livre_note BETWEEN 1 AND 5) AND (note <=> (jacquette + livre_note)));
ALTER TABLE artbook
    MODIFY numero INT UNSIGNED NOT NULL DEFAULT 1,
    RENAME INDEX idx_auteur TO idx_artbook_auteur,
    RENAME INDEX idx_serie TO idx_artbook_serie,
    RENAME INDEX idx_created_at TO idx_artbook_created_at,
    ADD CONSTRAINT chk_artbook_numero CHECK (numero >= 1),
    ADD CONSTRAINT chk_artbook_flags CHECK (lu IN (0,1) AND xp_read_rewarded IN (0,1));
ALTER TABLE figurine
    ADD CONSTRAINT chk_figurine_numero CHECK (numero >= 1),
    ADD CONSTRAINT chk_figurine_flags CHECK (collect IN (0,1) AND collect_rewarded IN (0,1));
-- L'index unique (slug, numero) couvre deja les recherches par slug.
ALTER TABLE nendoroid
    DROP INDEX idx_slug,
    RENAME INDEX idx_waifu TO idx_nendoroid_waifu,
    ADD CONSTRAINT chk_nendoroid_numero CHECK (numero >= 1),
    ADD CONSTRAINT chk_nendoroid_flags CHECK (collect IN (0,1) AND collect_rewarded IN (0,1));
ALTER TABLE peluche
    DROP INDEX idx_slug,
    RENAME INDEX idx_waifu TO idx_peluche_waifu,
    ADD CONSTRAINT chk_peluche_numero CHECK (numero >= 1),
    ADD CONSTRAINT chk_peluche_flags CHECK (collect IN (0,1) AND collect_rewarded IN (0,1));
ALTER TABLE chinois_grammaire
    MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    MODIFY created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    RENAME INDEX idx_niveau_position TO idx_chinois_grammaire_niveau_position,
    RENAME INDEX idx_maitrise TO idx_chinois_grammaire_maitrise,
    ADD CONSTRAINT chk_chinois_grammaire_flags CHECK (maitrise IN (0,1) AND xp_rewarded IN (0,1));
ALTER TABLE chinois_vocabulaire
    MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    DROP INDEX idx_langue,
    ADD INDEX idx_chinois_vocabulaire_langue_maitrise_id (langue, maitrise ASC, id DESC),
    RENAME INDEX idx_maitrise TO idx_chinois_vocabulaire_maitrise,
    ADD CONSTRAINT chk_chinois_vocabulaire_flags CHECK (maitrise IN (0,1) AND xp_rewarded IN (0,1));
ALTER TABLE users
    ALTER COLUMN avatar_extension SET DEFAULT 'webp',
    ALTER COLUMN banner_extension SET DEFAULT 'webp',
    MODIFY frame_extension ENUM('webp','jpg','png') NOT NULL DEFAULT 'webp',
    RENAME INDEX username TO uq_users_username,
    ADD CONSTRAINT chk_users_level CHECK (level >= 1);
ALTER TABLE achievement_xp_rewards
    MODIFY user_id INT UNSIGNED NOT NULL,
    ADD CONSTRAINT fk_achievement_xp_rewards_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE login_attempts
    DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
    MODIFY identifier_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL;
-- Meme collation que manga.slug : jointures sans erreur 1267.
ALTER TABLE manga_series_rewards
    MODIFY slug VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL;
START TRANSACTION;
-- Conserver les deux sources d'historique, sans attribuer de nouveaux XP.
INSERT INTO manga_series_rewards (slug)
    SELECT DISTINCT slug FROM manga WHERE xp_series_rewarded = 1
    ON DUPLICATE KEY UPDATE slug = manga_series_rewards.slug;
UPDATE manga m INNER JOIN manga_series_rewards r ON r.slug = m.slug
    SET m.xp_series_rewarded = 1 WHERE m.xp_series_rewarded = 0;
COMMIT;
