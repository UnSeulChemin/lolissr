DROP TRIGGER manga_collection_revision_update;

CREATE TRIGGER manga_collection_revision_update AFTER UPDATE ON manga
FOR EACH ROW
INSERT INTO manga_collection_revisions (user_id, revision)
SELECT OLD.user_id, UUID()
WHERE NOT (OLD.user_id <=> NEW.user_id)
   OR NOT (CAST(OLD.slug AS BINARY) <=> CAST(NEW.slug AS BINARY))
   OR NOT (CAST(OLD.livre AS BINARY) <=> CAST(NEW.livre AS BINARY))
   OR NOT (OLD.numero <=> NEW.numero)
UNION ALL
SELECT NEW.user_id, UUID() WHERE NOT (OLD.user_id <=> NEW.user_id)
ON DUPLICATE KEY UPDATE revision = UUID();
