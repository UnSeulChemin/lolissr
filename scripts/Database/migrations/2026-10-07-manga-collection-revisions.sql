CREATE TABLE manga_collection_revisions (
    user_id INT NOT NULL PRIMARY KEY,
    revision CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL
) ENGINE=InnoDB;

INSERT INTO manga_collection_revisions (user_id, revision)
SELECT user_id, UUID() FROM manga GROUP BY user_id;

CREATE TRIGGER manga_collection_revision_insert AFTER INSERT ON manga
FOR EACH ROW
INSERT INTO manga_collection_revisions (user_id, revision) VALUES (NEW.user_id, UUID())
ON DUPLICATE KEY UPDATE revision = UUID();

CREATE TRIGGER manga_collection_revision_delete AFTER DELETE ON manga
FOR EACH ROW
INSERT INTO manga_collection_revisions (user_id, revision) VALUES (OLD.user_id, UUID())
ON DUPLICATE KEY UPDATE revision = UUID();

CREATE TRIGGER manga_collection_revision_update AFTER UPDATE ON manga
FOR EACH ROW
INSERT INTO manga_collection_revisions (user_id, revision)
SELECT OLD.user_id, UUID() UNION ALL SELECT NEW.user_id, UUID()
ON DUPLICATE KEY UPDATE revision = UUID();
