-- Indexes for newsletter_queue to improve performance
ALTER TABLE newsletter_queue
  ADD KEY idx_due_selection (newsletter_id, execution_status, execute_by),
  ADD KEY idx_msg_stats (newsletter_id, msg_num, replied),
  ADD KEY idx_chat_id (chat_id),
  ADD KEY idx_user_id (user_id),
  ADD KEY idx_newsletter_version_msg (newsletter_id, version, msg_num);

-- Helpful covering indexes
ALTER TABLE newsletter_messages
  ADD KEY idx_newsletter_message (newsletter_id, message_number);

ALTER TABLE newsletter_settings
  ADD KEY idx_active_id (active, id);
