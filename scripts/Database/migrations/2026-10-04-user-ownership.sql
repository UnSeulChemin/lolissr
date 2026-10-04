-- Migration unique APRES 2026-10-04-schema-consistency.sql.
-- Base personnelle existante : les contenus historiques appartiennent a users.id=1.
-- Verifier cet identifiant sur un autre serveur avant execution et sauvegarder.
-- DDL : commits implicites. Les comptes existants gardent leurs XP et succes.
ALTER TABLE users
    ADD COLUMN is_admin TINYINT UNSIGNED NOT NULL DEFAULT 0,
    ADD CONSTRAINT chk_users_is_admin CHECK (is_admin IN (0,1));
UPDATE users SET is_admin = 1, updated_at = updated_at WHERE id = 1;

ALTER TABLE manga
    ADD COLUMN user_id INT UNSIGNED NOT NULL DEFAULT 1,
    DROP INDEX uq_manga_slug_numero,
    ADD UNIQUE KEY uq_manga_user_slug_numero (user_id, slug, numero),
    DROP INDEX idx_manga_numero,
    ADD INDEX idx_manga_user_numero (user_id, numero),
    DROP INDEX idx_manga_lu_xp_read_rewarded,
    ADD INDEX idx_manga_user_lu_xp_read_rewarded (user_id, lu, xp_read_rewarded),
    ADD CONSTRAINT fk_manga_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
ALTER TABLE manga ALTER COLUMN user_id DROP DEFAULT;
ALTER TABLE artbook
    ADD COLUMN user_id INT UNSIGNED NOT NULL DEFAULT 1,
    DROP INDEX uq_artbook_slug_numero,
    ADD UNIQUE KEY uq_artbook_user_slug_numero (user_id, slug, numero),
    DROP INDEX idx_artbook_auteur,
    ADD INDEX idx_artbook_user_auteur (user_id, auteur),
    DROP INDEX idx_artbook_serie,
    ADD INDEX idx_artbook_user_serie (user_id, serie),
    DROP INDEX idx_artbook_created_at,
    ADD INDEX idx_artbook_user_created_at (user_id, created_at),
    ADD CONSTRAINT fk_artbook_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
ALTER TABLE artbook ALTER COLUMN user_id DROP DEFAULT;
ALTER TABLE figurine
    ADD COLUMN user_id INT UNSIGNED NOT NULL DEFAULT 1,
    DROP INDEX uq_figurine_slug_numero,
    ADD UNIQUE KEY uq_figurine_user_slug_numero (user_id, slug, numero),
    ADD CONSTRAINT fk_figurine_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
ALTER TABLE figurine ALTER COLUMN user_id DROP DEFAULT;
ALTER TABLE nendoroid
    ADD COLUMN user_id INT UNSIGNED NOT NULL DEFAULT 1,
    DROP INDEX uq_nendoroid_slug_numero,
    ADD UNIQUE KEY uq_nendoroid_user_slug_numero (user_id, slug, numero),
    DROP INDEX idx_nendoroid_waifu,
    ADD INDEX idx_nendoroid_user_waifu (user_id, waifu),
    ADD CONSTRAINT fk_nendoroid_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
ALTER TABLE nendoroid ALTER COLUMN user_id DROP DEFAULT;
ALTER TABLE peluche
    ADD COLUMN user_id INT UNSIGNED NOT NULL DEFAULT 1,
    DROP INDEX uq_peluche_slug_numero,
    ADD UNIQUE KEY uq_peluche_user_slug_numero (user_id, slug, numero),
    DROP INDEX idx_peluche_waifu,
    ADD INDEX idx_peluche_user_waifu (user_id, waifu),
    ADD CONSTRAINT fk_peluche_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
ALTER TABLE peluche ALTER COLUMN user_id DROP DEFAULT;
ALTER TABLE chinois_grammaire
    ADD COLUMN user_id INT UNSIGNED NOT NULL DEFAULT 1,
    DROP INDEX idx_chinois_grammaire_niveau_position,
    ADD INDEX idx_chinois_grammaire_user_niveau_position (user_id, niveau, section_position, categorie_position, position),
    DROP INDEX idx_chinois_grammaire_maitrise,
    ADD INDEX idx_chinois_grammaire_user_maitrise (user_id, maitrise),
    ADD CONSTRAINT fk_chinois_grammaire_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
ALTER TABLE chinois_grammaire ALTER COLUMN user_id DROP DEFAULT;
ALTER TABLE chinois_vocabulaire
    ADD COLUMN user_id INT UNSIGNED NOT NULL DEFAULT 1,
    DROP INDEX idx_chinois_vocabulaire_langue_maitrise_id,
    ADD INDEX idx_chinois_vocabulaire_user_langue_maitrise_id (user_id, langue, maitrise ASC, id DESC),
    DROP INDEX idx_chinois_vocabulaire_maitrise,
    ADD INDEX idx_chinois_vocabulaire_user_maitrise (user_id, maitrise),
    ADD CONSTRAINT fk_chinois_vocabulaire_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
ALTER TABLE chinois_vocabulaire ALTER COLUMN user_id DROP DEFAULT;
ALTER TABLE manga_series_rewards
    ADD COLUMN user_id INT UNSIGNED NOT NULL DEFAULT 1,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (user_id, slug),
    ADD CONSTRAINT fk_manga_series_rewards_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
ALTER TABLE manga_series_rewards ALTER COLUMN user_id DROP DEFAULT;
