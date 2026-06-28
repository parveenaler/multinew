-- Create newsletter_message_versions table
CREATE TABLE IF NOT EXISTS newsletter_message_versions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  newsletter_id INT NOT NULL,
  version INT NOT NULL,
  message_number INT NOT NULL,
  msg_type ENUM('text','image','text_image') NOT NULL,
  message_text LONGTEXT NULL,
  delay_minutes INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_newsletter_version_message (newsletter_id, version, message_number),
  KEY idx_newsletter_version (newsletter_id, version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alter newsletter_queue to add version column
ALTER TABLE newsletter_queue
  ADD COLUMN version INT NOT NULL DEFAULT 1 AFTER msg_num,
  ADD KEY idx_newsletter_version_msg (newsletter_id, version, msg_num);

-- Alter newsletter_settings to add current_version
ALTER TABLE newsletter_settings
  ADD COLUMN current_version INT NOT NULL DEFAULT 1 AFTER active;

-- Backfill versions from current drafts (run once)
INSERT INTO newsletter_message_versions (newsletter_id, version, message_number, msg_type, message_text, delay_minutes)
SELECT nm.newsletter_id, 1 AS version, nm.message_number, nm.msg_type, nm.message_text, COALESCE(nm.delay_minutes, 0)
FROM newsletter_messages nm
LEFT JOIN newsletter_message_versions nmv
  ON nmv.newsletter_id = nm.newsletter_id AND nmv.version = 1 AND nmv.message_number = nm.message_number
WHERE nmv.id IS NULL;

-- Ensure settings have current_version = 1 where null/missing
UPDATE newsletter_settings SET current_version = 1 WHERE current_version IS NULL OR current_version = 0;

-- Set all existing queue rows to version 1 if NULL or 0
UPDATE newsletter_queue SET version = 1 WHERE version IS NULL OR version = 0;

-- Create edit history table
CREATE TABLE IF NOT EXISTS newsletter_edit_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  newsletter_id INT NOT NULL,
  version INT NOT NULL,
  action VARCHAR(20) NOT NULL,
  editor_id INT NULL,
  old_data JSON NULL,
  new_data JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_newsletter (newsletter_id),
  KEY idx_version (version),
  KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
