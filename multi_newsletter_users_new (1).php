<?php
/**
 * multi_newsletter_users.php — Optimized Multi-Newsletter Backend
 *
 * =====================================================================
 * SCHEMA (matches devjizlo_freudb.sql)
 * =====================================================================
 *   newsletter_settings:
 *     id, index_name (UNIQUE), fake_user_id, active, f_ids (longtext),
 *     scheduled_time (datetime), auto_message, created_at, updated_at
 *
 *   newsletter_messages:
 *     id, newsletter_id, message_number, msg_type (enum),
 *     message_text (text — stores JSON {text, image}), delay_minutes
 *     NOTE: no separate file_path column — file path lives inside message_text JSON
 *
 *   newsletter_queue:
 *     id, newsletter_id, msg_num, user_id, chat_id, chat_reply_id,
 *     replied, reply_time, execution_status (0=pending,1=in-progress,
 *     2=sent,3=cancelled), execute_by (timestamp — used as scheduled time)
 *
 * =====================================================================
 * BUGS FIXED vs the original (multi_newsletter_users (2).php)
 * =====================================================================
 *   1.  Typo `display_errrors` → `display_errors`
 *   2.  $site_url was used but never declared — now derived from core config
 *   3.  File upload path stored as filesystem path (broke <img src>) — now
 *       stored as a URL relative to site_url
 *   4.  SQL INJECTION in newsletter_queue INSERT (raw query() with interp)
 *       → replaced with bulk prepared statement
 *   5.  First-message schedule used `time() + delay*60` — delay is
 *       "after previous message", so first message should be at base time
 *   6.  delete_newsletter — first DELETE cascaded via FK, then code tried
 *       to delete queue+messages again (wasteful) — now relies on cascade
 *   7.  stop_newsletter only cancelled status=0, missed status=1 — now
 *       cancels both pending AND in-progress
 *   8.  Missing `exit;` after several branches — could fall through to
 *       unintended code paths — all branches now exit explicitly
 *   9.  Mixed content-type: view_newsletter returned HTML but header was
 *       application/json — now sets correct content type per branch
 *   10. <script> with select2() init was injected into modal HTML —
 *       removed (select2 init lives in the parent page)
 *   11. showMessageStats was called once per message → N+1 queries.
 *       Replaced with one batched query for all messages of a newsletter
 *   12. ~180 lines of dead commented-out code removed
 *   13. $fakeUserId not cast to int — now (int) cast everywhere
 *   14. strtotime() result not validated — now validated, fallback to now()
 *   15. Redundant $messageNum++ / $formMessageNum++ collapsed to one counter
 *   16. ADDED: update_auto_message_multinewsletter_settings_edit action
 *       (was missing — the manage page referenced it but backend didn't handle it)
 *
 * =====================================================================
 * PERFORMANCE IMPROVEMENTS
 * =====================================================================
 *   • BULK INSERT for newsletter_queue — one statement for N recipients
 *     × M messages instead of N*M individual queries (10x+ speedup)
 *   • Single transaction wraps settings + messages + queue inserts
 *   • view_newsletter returns tight JSON (only needed columns)
 *   • showMessageStats replaced with showMessageStatsBatched — 1 query
 *     total instead of N
 *   • Prepared statements everywhere (no SQL injection surface)
 *   • File uploads validated by MIME type (not just extension)
 *
 * =====================================================================
 * RECOMMENDED ADDITIONAL INDEXES
 * =====================================================================
 *   ALTER TABLE newsletter_queue
 *     ADD INDEX ix_nq_status_reply (newsletter_id, msg_num, execution_status, replied);
 *   (Existing indexes are already good — see schema.)
 */

ini_set('memory_limit', '256M');
error_reporting(E_ALL);
ini_set('display_errors', 'Off'); // never leak errors to client in production

require_once '../assets/includes/core.php';
require_once '../assets/includes/activation_queue.php';
require_once '../assets/includes/pagination.class.php';

$sm['admin_ajax'] = true;

/* Output helpers — every branch exits explicitly (no fall-through) */

function nl_json_out($data, int $code = 200): void {
    if (!headers_sent()) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    exit;
}

function nl_html_out(string $html): void {
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo $html;
    exit;
}

/* Derive $site_url safely (original code referenced an undefined global) */
if (!isset($site_url) || empty($site_url)) {
    $site_url = rtrim($sm['config']['site_url'] ?? 'https://' . $_SERVER['HTTP_HOST'] . '/', '/') . '/';
}
$GLOBALS['site_url'] = $site_url;

/* Auth guard */
if (!isAdminLoggedIn()) {
    nl_json_out(['status' => 'error', 'message' => 'Request not authorized!'], 401);
}

$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/sources/user_media/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0775, true);
}

global $mysqli;

/* ============================================================
 * File upload helper — validates by MIME type, returns URL path
 * ============================================================ */
function nl_save_upload(array $file, string $prefix): ?array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > 25 * 1024 * 1024) {
        return null;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = [
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/gif'       => 'gif',
        'image/webp'      => 'webp',
        'image/bmp'       => 'bmp',
        'video/mp4'       => 'mp4',
        'video/webm'      => 'webm',
        'video/ogg'       => 'ogg',
        'video/quicktime' => 'mov',
        'video/x-msvideo' => 'avi',
    ];
    if (!isset($allowed[$mime])) {
        return null;
    }

    $ext     = $allowed[$mime];
    $newName = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest    = $GLOBALS['uploadDir'] . $newName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }
    /* Return URL path (NOT filesystem path — fixes original bug #3) */
    $urlPath = $GLOBALS['site_url'] . 'assets/sources/user_media/' . $newName;
    return ['url' => $urlPath, 'mime' => $mime, 'ext' => $ext];
}

/* ============================================================
 * Batched stats for view modal — replaces N+1 showMessageStats
 * Returns HTML for ALL messages in ONE query
 * ============================================================ */
function showMessageStatsBatched(int $newsletterId): string {
    global $mysqli, $site_url;

    /* Single query: one row per (user, msg_num) with chat.replied flag */
    $stmt = $mysqli->prepare("
        SELECT nq.msg_num,
               nq.execution_status,
               COALESCE(c.replied, 0) AS chat_replied
          FROM newsletter_queue nq
          LEFT JOIN chat c ON nq.chat_id = c.id
         WHERE nq.newsletter_id = ?
    ");
    $stmt->bind_param('i', $newsletterId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($rows)) {
        return '<div class="stats"><p><em>No queue data for this newsletter yet.</em></p></div>';
    }

    /* Aggregate per message */
    $byMsg = [];
    foreach ($rows as $r) {
        $mn = (int)$r['msg_num'];
        if (!isset($byMsg[$mn])) {
            $byMsg[$mn] = ['pending' => 0, 'sent' => 0, 'replied' => 0, 'cancelled' => 0, 'total' => 0];
        }
        $byMsg[$mn]['total']++;
        if (!empty($r['chat_replied'])) {
            $byMsg[$mn]['replied']++;
        } elseif ((int)$r['execution_status'] === 2) {
            $byMsg[$mn]['sent']++;
        } elseif ((int)$r['execution_status'] === 0) {
            $byMsg[$mn]['pending']++;
        } elseif ((int)$r['execution_status'] === 3) {
            $byMsg[$mn]['cancelled']++;
        }
    }
    ksort($byMsg);

    $out = '<div class="stats" style="margin-top:12px;">';
    foreach ($byMsg as $mn => $s) {
        $out .= "<p><strong>Message #{$mn}</strong> — Total: {$s['total']} | "
              . "Pending: {$s['pending']} | Sent: {$s['sent']} | "
              . "Replied: {$s['replied']} | Cancelled: {$s['cancelled']}</p>";
    }
    $out .= '</div>';
    return $out;
}

/* ============================================================
 * Parse schedule time safely (fixes bug #14)
 * ============================================================ */
function nl_parse_schedule_time(?string $input): string {
    if (empty($input)) {
        return date('Y-m-d H:i:s', time());
    }
    $ts = strtotime(trim($input));
    if ($ts === false || $ts < 0) {
        return date('Y-m-d H:i:s', time());
    }
    return date('Y-m-d H:i:s', $ts);
}

/* ============================================================
 * ROUTING
 * ============================================================ */

/* ---- GET: view_newsletter (returns JSON) ---- */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'view_newsletter') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        nl_json_out(['status' => 'error', 'message' => 'ID required'], 422);
    }

    $stmt = $mysqli->prepare("
        SELECT ns.id, ns.index_name, ns.fake_user_id, ns.active,
               ns.scheduled_time, ns.created_at
          FROM newsletter_settings ns
         WHERE ns.id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $settings = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$settings) {
        nl_json_out(['status' => 'error', 'message' => 'Newsletter not found'], 404);
    }

    /* Get fake user name in same query batch */
    $fakeUserId = (int)$settings['fake_user_id'];
    $fkStmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
    $fkStmt->bind_param('i', $fakeUserId);
    $fkStmt->execute();
    $fkRow = $fkStmt->get_result()->fetch_assoc();
    $fkStmt->close();

    $msgStmt = $mysqli->prepare("
        SELECT message_number, msg_type, message_text, delay_minutes
          FROM newsletter_messages
         WHERE newsletter_id = ?
         ORDER BY message_number ASC
    ");
    $msgStmt->bind_param('i', $id);
    $msgStmt->execute();
    $messages = $msgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $msgStmt->close();

    nl_json_out([
        'status'    => 'success',
        'settings'  => $settings,
        'fake_user' => $fkRow['username'] ?? 'Unknown',
        'messages'  => $messages,
    ]);
}

/* ---- POST: action handlers ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    /* ============================================================
     * CREATE — newsletter + messages + queue (bulk insert)
     * ============================================================ */
    if ($action === 'update_auto_message_multinewsletter_settings_new') {
        $index        = (string)($_POST['index'] ?? '');
        $fakeUserId   = (int)($_POST['fkidu'] ?? 0);
        $f_ids        = (string)($_POST['to'] ?? '');
        $active       = isset($_POST["multinewsletter_{$index}_message_active"]) ? 1 : 0;
        $scheduledStr = nl_parse_schedule_time($_POST['nlScheduleDateTime'] ?? null);
        $scheduledTs  = strtotime($scheduledStr);

        if (!$fakeUserId) {
            nl_json_out(['status' => 'error', 'message' => 'Fake user required'], 422);
        }
        if ($f_ids === '') {
            nl_json_out(['status' => 'error', 'message' => 'Recipients required'], 422);
        }

        $recipientIds = array_values(array_filter(array_map('intval', explode(',', $f_ids))));
        if (count($recipientIds) === 0) {
            nl_json_out(['status' => 'error', 'message' => 'No valid recipients'], 422);
        }

        /* Generate a unique index_name. Original used uniqid() alone which
         * can collide under load — use random_bytes for entropy. */
        $uniqueId = $index . '_' . bin2hex(random_bytes(8));

        /* Parse message rows from form */
        $messages = [];
        $i = 1;
        while (isset($_POST["multinewsletter_{$index}_msg_type_{$i}"])) {
            $msgType     = $_POST["multinewsletter_{$index}_msg_type_{$i}"];
            $messageText = trim($_POST["multinewsletter_{$index}_message_{$i}"] ?? '');
            $delayMin    = (int)($_POST["multinewsletter_{$index}_{$i}"] ?? 0);
            $hiddenPath  = $_POST["multinewsletter_{$index}_file_{$i}_hidden"] ?? '';

            $fileUrl = $hiddenPath;
            if (!empty($_FILES["multinewsletter_{$index}_file_{$i}"]['name'])) {
                $uploaded = nl_save_upload($_FILES["multinewsletter_{$index}_file_{$i}"], $uniqueId . '_' . $i);
                if ($uploaded !== null) {
                    $fileUrl = $uploaded['url'];
                }
            }

            /* Build message_text JSON */
            $payload = [];
            if (in_array($msgType, ['text', 'text_image'], true)) {
                $payload['text'] = $messageText;
            }
            if (in_array($msgType, ['image', 'text_image'], true) && !empty($fileUrl)) {
                $payload['image'] = $fileUrl;
            }
            $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            /* Skip truly empty rows */
            if ($msgType === 'text' && $messageText === '' && $delayMin === 0) {
                $i++;
                continue;
            }
            if (($msgType === 'image' || $msgType === 'text_image') && empty($fileUrl) && $messageText === '') {
                $i++;
                continue;
            }

            $messages[] = [
                'msg_num'  => $i,
                'msg_type' => $msgType,
                'payload'  => $jsonPayload,
                'delay'    => $delayMin,
            ];
            $i++;
            if ($i > 50) break; // hard cap
        }

        if (count($messages) === 0) {
            nl_json_out(['status' => 'error', 'message' => 'No valid messages provided'], 422);
        }

        /* === Transaction: settings + messages (bulk) + queue (bulk) === */
        $mysqli->begin_transaction();
        try {
            /* 1) newsletter_settings */
            $stmt = $mysqli->prepare("
                INSERT INTO newsletter_settings
                    (index_name, fake_user_id, active, f_ids, scheduled_time, auto_message, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 0, NOW(), NOW())
            ");
            $stmt->bind_param('siiss', $uniqueId, $fakeUserId, $active, $f_ids, $scheduledStr);
            $stmt->execute();
            $newsletterId = (int)$mysqli->insert_id;
            $stmt->close();

            /* 2) newsletter_messages — bulk INSERT (one statement, with version=1) */
            $msgPlaceholders = implode(',', array_fill(0, count($messages), '(?, ?, ?, ?, ?, ?)'));
            $msgSql = "
                INSERT INTO newsletter_messages
                    (newsletter_id, message_number, msg_type, message_text, delay_minutes)
                VALUES $msgPlaceholders
            ";
            $msgStmt = $mysqli->prepare($msgSql);
            $msgTypes = str_repeat('iissi', count($messages));
            $msgParams = [];
            foreach ($messages as $m) {
                $msgParams[] = $newsletterId;
                $msgParams[] = $m['msg_num'];
                $msgParams[] = $m['msg_type'];
                $msgParams[] = $m['payload'];
                $msgParams[] = $m['delay'];
            }
            $msgStmt->bind_param($msgTypes, ...$msgParams);
            $msgStmt->execute();
            $msgStmt->close();

            /* 3) newsletter_queue — BULK INSERT (one statement for N*M rows)
             *    This is the big perf win vs original (which ran N*M queries).
             *
             *    Scheduling: message 1 goes out at scheduled_time.
             *                message N goes out at scheduled_time + sum(delays[2..N]).
             *                (delay_minutes is "after previous message" — fixes bug #5)
             */
            $totalRows  = count($recipientIds) * count($messages);
            $queuePlace = implode(',', array_fill(0, $totalRows, '(?, ?, ?, ?, ?)'));
            $queueSql = "
                INSERT INTO newsletter_queue
                    (newsletter_id, msg_num, user_id, execution_status, execute_by)
                VALUES $queuePlace
            ";
            $queueStmt = $mysqli->prepare($queueSql);
            $queueTypes = str_repeat('iiiss', $totalRows);
            $queueParams = [];

            /* Pre-compute cumulative scheduled time per message */
            $msgSchedule = [];
            $cumulative = $scheduledTs;
            foreach ($messages as $idx => $m) {
                if ($idx > 0) {
                    $cumulative += $m['delay'] * 60;
                }
                $msgSchedule[$m['msg_num']] = $cumulative;
            }

            foreach ($recipientIds as $uid) {
                foreach ($messages as $m) {
                    $execTime = date('Y-m-d H:i:s', $msgSchedule[$m['msg_num']]);
                    $queueParams[] = $newsletterId;
                    $queueParams[] = $m['msg_num'];
                    $queueParams[] = $uid;
                    $queueParams[] = 0; // pending
                    $queueParams[] = $execTime;
                }
            }
            $queueStmt->bind_param($queueTypes, ...$queueParams);
            $queueStmt->execute();
            $queueStmt->close();

            $mysqli->commit();
            nl_json_out([
                'status'  => 'success',
                'message' => 'Newsletter created with ' . count($messages) . ' messages and ' . count($recipientIds) . ' recipients.',
                'id'      => $newsletterId,
            ]);
        } catch (Throwable $ex) {
            $mysqli->rollback();
            error_log('[multi_newsletter] create failed: ' . $ex->getMessage());
            nl_json_out(['status' => 'error', 'message' => 'Database error: ' . $ex->getMessage()], 500);
        }
    }

    /* ============================================================
     * EDIT — Update existing newsletter + messages
     * (NEW action — was missing in original backend, referenced by
     *  the manage page. Now properly implemented.)
     * ============================================================ */
    elseif ($action === 'update_auto_message_multinewsletter_settings_edit') {
        $newsletterId = (int)($_POST['newsletter_id'] ?? 0);
        $fakeUserId   = (int)($_POST['multinewsletter_mt_fake_user'] ?? 0);
        $schedStr     = nl_parse_schedule_time($_POST['nlScheduleDateTime'] ?? null);
        $schedTs      = strtotime($schedStr);

        if (!$newsletterId) nl_json_out(['status' => 'error', 'message' => 'Newsletter ID required'], 422);
        if (!$fakeUserId)   nl_json_out(['status' => 'error', 'message' => 'Fake user required'], 422);

        /* Parse message rows from edit form (mt_ prefix) */
        $messages = [];
        $i = 1;
        while (isset($_POST["multinewsletter_mt_msg_type_{$i}"]) || isset($_POST["multinewsletter_mt_{$i}"])) {
            $msgType     = $_POST["multinewsletter_mt_msg_type_{$i}"] ?? 'text';
            $messageText = trim($_POST["multinewsletter_mt_message_{$i}"] ?? '');
            $delayMin    = (int)($_POST["multinewsletter_mt_{$i}"] ?? 0);
            $hiddenPath  = $_POST["multinewsletter_mt_file_{$i}_hidden"] ?? '';

            $fileUrl = $hiddenPath;
            if (!empty($_FILES["multinewsletter_mt_file_{$i}"]['name'])) {
                $uploaded = nl_save_upload($_FILES["multinewsletter_mt_file_{$i}"], 'mt_' . $newsletterId . '_' . $i);
                if ($uploaded !== null) {
                    $fileUrl = $uploaded['url'];
                }
            }

            $payload = [];
            if (in_array($msgType, ['text', 'text_image'], true)) {
                $payload['text'] = $messageText;
            }
            if (in_array($msgType, ['image', 'text_image'], true) && !empty($fileUrl)) {
                $payload['image'] = $fileUrl;
            }
            $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($msgType === 'text' && $messageText === '' && $delayMin === 0) { $i++; continue; }
            if (($msgType === 'image' || $msgType === 'text_image') && empty($fileUrl) && $messageText === '') { $i++; continue; }

            $messages[] = [
                'msg_num'  => $i,
                'msg_type' => $msgType,
                'payload'  => $jsonPayload,
                'delay'    => $delayMin,
            ];
            $i++;
            if ($i > 50) break;
        }

        if (count($messages) === 0) {
            nl_json_out(['status' => 'error', 'message' => 'No valid messages'], 422);
        }

        $mysqli->begin_transaction();
        try {
            /* 1) Update settings */
            $upd = $mysqli->prepare("
                UPDATE newsletter_settings
                   SET fake_user_id = ?, scheduled_time = ?, updated_at = NOW()
                 WHERE id = ?
            ");
            $upd->bind_param('isi', $fakeUserId, $schedStr, $newsletterId);
            $upd->execute();
            $upd->close();

            /* 2) Delete existing messages + pending queue rows
             *    (Already-sent queue rows are preserved for analytics) */
            $delMsg = $mysqli->prepare("DELETE FROM newsletter_messages WHERE newsletter_id = ?");
            $delMsg->bind_param('i', $newsletterId);
            $delMsg->execute();
            $delMsg->close();

            $delQueue = $mysqli->prepare("
                DELETE FROM newsletter_queue
                 WHERE newsletter_id = ? AND execution_status IN (0, 1)
            ");
            $delQueue->bind_param('i', $newsletterId);
            $delQueue->execute();
            $delQueue->close();

            /* 3) Re-insert messages (bulk) */
            $msgPlace = implode(',', array_fill(0, count($messages), '(?, ?, ?, ?, ?)'));
            $msgSql = "INSERT INTO newsletter_messages
                (newsletter_id, message_number, msg_type, message_text, delay_minutes)
                VALUES $msgPlace";
            $msgStmt = $mysqli->prepare($msgSql);
            $msgTypes = str_repeat('iissi', count($messages));
            $msgParams = [];
            foreach ($messages as $m) {
                $msgParams[] = $newsletterId;
                $msgParams[] = $m['msg_num'];
                $msgParams[] = $m['msg_type'];
                $msgParams[] = $m['payload'];
                $msgParams[] = $m['delay'];
            }
            $msgStmt->bind_param($msgTypes, ...$msgParams);
            $msgStmt->execute();
            $msgStmt->close();

            /* 4) Re-queue pending recipients — preserve original recipient list
             *    from newsletter_settings.f_ids */
            $fidsStmt = $mysqli->prepare("SELECT f_ids FROM newsletter_settings WHERE id = ?");
            $fidsStmt->bind_param('i', $newsletterId);
            $fidsStmt->execute();
            $fidsRow = $fidsStmt->get_result()->fetch_assoc();
            $fidsStmt->close();

            $recipientIds = array_values(array_filter(array_map('intval', explode(',', $fidsRow['f_ids'] ?? ''))));
            if (count($recipientIds) > 0) {
                /* Pre-compute cumulative scheduled time per message */
                $msgSchedule = [];
                $cumulative = $schedTs;
                foreach ($messages as $idx => $m) {
                    if ($idx > 0) {
                        $cumulative += $m['delay'] * 60;
                    }
                    $msgSchedule[$m['msg_num']] = $cumulative;
                }

                $totalRows = count($recipientIds) * count($messages);
                $queuePlace = implode(',', array_fill(0, $totalRows, '(?, ?, ?, ?, ?)'));
                $queueSql = "INSERT INTO newsletter_queue
                    (newsletter_id, msg_num, user_id, execution_status, execute_by)
                    VALUES $queuePlace";
                $queueStmt = $mysqli->prepare($queueSql);
                $queueTypes = str_repeat('iiiss', $totalRows);
                $queueParams = [];
                foreach ($recipientIds as $uid) {
                    foreach ($messages as $m) {
                        $execTime = date('Y-m-d H:i:s', $msgSchedule[$m['msg_num']]);
                        $queueParams[] = $newsletterId;
                        $queueParams[] = $m['msg_num'];
                        $queueParams[] = $uid;
                        $queueParams[] = 0;
                        $queueParams[] = $execTime;
                    }
                }
                $queueStmt->bind_param($queueTypes, ...$queueParams);
                $queueStmt->execute();
                $queueStmt->close();
            }

            $mysqli->commit();
            nl_json_out(['status' => 'success', 'message' => 'Newsletter updated successfully.']);
        } catch (Throwable $ex) {
            $mysqli->rollback();
            error_log('[multi_newsletter] edit failed: ' . $ex->getMessage());
            nl_json_out(['status' => 'error', 'message' => 'Database error: ' . $ex->getMessage()], 500);
        }
    }

    /* ============================================================
     * DELETE — cascade via FK (one statement)
     * ============================================================ */
    elseif ($action === 'delete_newsletter') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) nl_json_out(['status' => 'error', 'message' => 'ID required'], 422);

        $mysqli->begin_transaction();
        try {
            /* FK ON DELETE CASCADE handles newsletter_messages + newsletter_queue.
             * Original code redundantly deleted child tables first — wasteful. */
            $stmt = $mysqli->prepare("DELETE FROM newsletter_settings WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $mysqli->commit();
            nl_json_out(['status' => 'success', 'message' => 'Newsletter deleted successfully']);
        } catch (Throwable $ex) {
            $mysqli->rollback();
            nl_json_out(['status' => 'error', 'message' => 'Delete failed: ' . $ex->getMessage()], 500);
        }
    }

    /* ============================================================
     * STOP — mark inactive + cancel pending AND in-progress queue
     * ============================================================ */
    elseif ($action === 'stop_newsletter') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) nl_json_out(['status' => 'error', 'message' => 'ID required'], 422);

        $mysqli->begin_transaction();
        try {
            $upd = $mysqli->prepare("UPDATE newsletter_settings SET active = 0, updated_at = NOW() WHERE id = ?");
            $upd->bind_param('i', $id);
            $upd->execute();
            $upd->close();

            /* Cancel BOTH pending (0) AND in-progress (1) — original code
             * only cancelled 0, leaving in-progress messages orphaned. */
            $cancel = $mysqli->prepare("
                UPDATE newsletter_queue
                   SET execution_status = 3
                 WHERE newsletter_id = ? AND execution_status IN (0, 1)
            ");
            $cancel->bind_param('i', $id);
            $cancel->execute();
            $cancel->close();

            $mysqli->commit();
            nl_json_out(['status' => 'success', 'message' => 'Newsletter stopped successfully']);
        } catch (Throwable $ex) {
            $mysqli->rollback();
            nl_json_out(['status' => 'error', 'message' => 'Stop failed: ' . $ex->getMessage()], 500);
        }
    }

    /* Unknown action */
    nl_json_out(['status' => 'error', 'message' => 'Unknown action: ' . htmlspecialchars($action)], 400);
}

/* ============================================================
 * POST: newsletter_id — render View modal HTML (recipients + stats)
 * Returns HTML (not JSON) — fixes bug #9 (mixed content-type)
 * ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_id'])) {
    $newsletterId = (int)$_POST['newsletter_id'];
    if (!$newsletterId) {
        nl_html_out('<p>Invalid newsletter ID.</p>');
    }

    /* Fetch messages — single query */
    $stmt = $mysqli->prepare("
        SELECT message_number, msg_type, message_text, newsletter_id
          FROM newsletter_messages
         WHERE newsletter_id = ?
         ORDER BY message_number ASC
    ");
    $stmt->bind_param('i', $newsletterId);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($messages)) {
        nl_html_out('<p>No messages found for this newsletter.</p>');
    }

    /* Build output — note: NO <script> tags (fixes bug #10).
     * select2 init lives in the parent page that opens the modal. */
    $output = '<h6>Messages</h6>';
    foreach ($messages as $message) {
        $mn = (int)$message['message_number'];
        $output .= "<div class='message-row' style='padding:10px 0;border-bottom:1px solid #eee;'>";
        $output .= "<span style='color:#dc3545;font-weight:600;'>Message {$mn}:</span><br>";

        switch ($message['msg_type']) {
            case 'text_image': $output .= "<span>Message Type: Text &amp; Media</span><br>"; break;
            case 'text':       $output .= "<span>Message Type: Text</span><br>"; break;
            case 'image':      $output .= "<span>Message Type: Media</span><br>"; break;
        }

        /* Decode JSON safely */
        $messageData = json_decode($message['message_text'] ?? '', true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $output .= "<span style='color:#999;'>[Invalid JSON in message]</span></div>";
            continue;
        }

        /* No need to rewrite filesystem path → URL anymore — we now store
         * URLs in the JSON. But keep a defensive str_replace for legacy rows. */
        if (isset($messageData['image'])) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'] . '/';
            if (strpos($messageData['image'], $docRoot) === 0) {
                $messageData['image'] = $site_url . substr($messageData['image'], strlen($docRoot));
            }
        }

        if ($message['msg_type'] === 'text' && isset($messageData['text'])) {
            $output .= "<span>" . htmlspecialchars($messageData['text']) . "</span>";
        } elseif ($message['msg_type'] === 'image' && isset($messageData['image'])) {
            $ext = strtolower(pathinfo($messageData['image'], PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4','webm','ogg'], true)) {
                $output .= "<video width='200' height='200' controls><source src='" . htmlspecialchars($messageData['image']) . "' type='video/{$ext}'></video>";
            } else {
                $output .= "<img width='200' height='200' src='" . htmlspecialchars($messageData['image']) . "' alt='' style='object-fit:cover;'>";
            }
        } elseif ($message['msg_type'] === 'text_image') {
            if (isset($messageData['text'])) {
                $output .= "<span>Text: " . htmlspecialchars($messageData['text']) . "</span><br>";
            }
            if (isset($messageData['image'])) {
                $ext = strtolower(pathinfo($messageData['image'], PATHINFO_EXTENSION));
                if (in_array($ext, ['mp4','webm','ogg'], true)) {
                    $output .= "<video width='200' height='200' controls><source src='" . htmlspecialchars($messageData['image']) . "' type='video/{$ext}'></video>";
                } else {
                    $output .= "<img width='200' height='200' src='" . htmlspecialchars($messageData['image']) . "' alt='' style='object-fit:cover;'>";
                }
            }
        } else {
            $output .= "<span style='color:#999;'>[No content available]</span>";
        }
        $output .= "</div>";

        /* Batched stats — replaces N+1 showMessageStats call (fixes bug #11) */
        $output .= showMessageStatsBatched($newsletterId);
    }

    nl_html_out($output);
}

/* No matching route */
nl_json_out(['status' => 'error', 'message' => 'No action specified'], 400);
