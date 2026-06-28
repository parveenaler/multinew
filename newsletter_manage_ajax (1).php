<?php

ini_set('memory_limit', '256M');
error_reporting(E_ALL);
ini_set('display_errors', 'Off'); // never leak errors to client in production

require_once '../assets/includes/core.php';
require_once '../assets/includes/activation_queue.php';
require_once '../assets/includes/pagination.class.php';

/**
 * newsletter_manage_ajax.php — AJAX Backend for Newsletter Management
 *
 * Endpoints:
 *   POST action=lazy_load       — Paginated newsletter cards + live stats
 *   POST action=edit_history    — Retrieve edit history for a newsletter
 *   POST action=log_edit        — Store an edit-history snapshot
 *   POST action=total_stats     — Return total counts (for stats strip)
 *
 * Requires: $mysqli global, $sm config array (provided by the parent framework).
 * Include this after your framework bootstrap (session start, DB connect, etc.).
 */

header('Content-Type: application/json; charset=utf-8');

/* ----- Auth check (mirror the phtml gate) ----- */
if (($sm['moderator']['Users'] ?? '') === 'No') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

global $mysqli;

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$siteUrl = rtrim($sm['config']['site_url'] ?? '/', '/') . '/';

/* ==================================================================
 * HELPER — render a single card HTML (matches the phtml template)
 * ================================================================== */
function renderCard(array $n, array $rates): string {
    $nid     = (int)$n['id'];
    $active  = (int)$n['active'] === 1;
    $escName = htmlspecialchars($n['fake_user_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');

    $ratesHtml = '';
    if (!empty($rates)) {
        $ratesHtml = '<div style="margin-top:14px;">'
            . '<div style="font-size:.78rem;font-weight:600;color:var(--nl-muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.04em;">Per-Message Reply Rates</div>';
        foreach ($rates as $r) {
            $pct   = (int)round($r['reply_rate']);
            $color = $pct >= 50 ? 'var(--nl-success)' : ($pct >= 25 ? 'var(--nl-warn)' : 'var(--nl-danger)');
            $ratesHtml .= '<div class="nlm-rate-bar">'
                . '<div class="nlm-rate-bar__head">'
                .   '<span>Message #' . (int)$r['msg_num'] . '</span>'
                .   '<span><strong>' . (int)$r['replied_users'] . '/' . (int)$r['sent_users'] . '</strong> · ' . number_format($r['reply_rate'], 1) . '%</span>'
                . '</div>'
                . '<div class="nlm-rate-bar__track">'
                .   '<div class="nlm-rate-bar__fill" style="width:' . $pct . '%;background:' . $color . ';"></div>'
                . '</div></div>';
        }
        $ratesHtml .= '</div>';
    }

    return '<div class="nlm-card"'
        . ' data-id="' . $nid . '"'
        . ' data-name="' . htmlspecialchars(strtolower($n['fake_user_name'] ?? ''), ENT_QUOTES, 'UTF-8') . '"'
        . ' data-status="' . ($active ? 'active' : 'stopped') . '">'
        . '<div class="nlm-card__head">'
        .   '<div>'
        .     '<div class="nlm-card__id">#' . $nid . '</div>'
        .     '<div class="nlm-card__title">' . $escName . '</div>'
        .   '</div>'
        .   '<span class="nlm-status nlm-status--' . ($active ? 'active' : 'stopped') . '">'
        .     '<span class="nlm-status__dot"></span>' . ($active ? 'Active' : 'Stopped')
        .   '</span>'
        . '</div>'
        . '<div class="nlm-card__body">'
        .   '<div class="nlm-card__row"><span>Messages</span><strong>' . (int)$n['message_count'] . '</strong></div>'
        .   '<div class="nlm-card__row"><span>Sent</span><strong>' . (int)$n['total_sent'] . '</strong></div>'
        .   '<div class="nlm-card__row"><span>Replies</span><strong>' . (int)$n['total_replied'] . '</strong></div>'
        .   '<div class="nlm-card__row"><span>Pending</span><strong>' . (int)$n['pending'] . '</strong></div>'
        .   $ratesHtml
        . '</div>'
        . '<div class="nlm-card__foot">'
        .   '<button class="nlm-btn nlm-btn--primary" data-action="view" data-id="' . $nid . '">'
        .     '<i class="material-icons">visibility</i> View'
        .   '</button>'
        .   '<button class="nlm-btn nlm-btn--ghost" data-action="edit" data-id="' . $nid . '">'
        .     '<i class="material-icons">edit</i> Edit'
        .   '</button>'
        .   '<button class="nlm-btn nlm-btn--accent" data-action="history" data-id="' . $nid . '">'
        .     '<i class="material-icons">history</i> History'
        .   '</button>'
        .   '<button class="nlm-btn nlm-btn--warn" data-action="stop" data-id="' . $nid . '"' . ($active ? '' : ' disabled') . '>'
        .     '<i class="material-icons">pause_circle</i> Stop'
        .   '</button>'
        .   '<button class="nlm-btn nlm-btn--danger" data-action="delete" data-id="' . $nid . '">'
        .     '<i class="material-icons">delete</i> Delete'
        .   '</button>'
        . '</div></div>';
}

/* ==================================================================
 * ACTION: total_stats
 * ================================================================== */
if ($action === 'total_stats') {
    $row = $mysqli->query("
        SELECT
            COUNT(*)                    AS total,
            SUM(CASE WHEN ns.active = 1 THEN 1 ELSE 0 END) AS active_count,
            COALESCE(SUM(msg_counts.cnt), 0) AS total_messages
        FROM newsletter_settings ns
        LEFT JOIN (
            SELECT newsletter_id, COUNT(*) AS cnt
            FROM newsletter_messages
            GROUP BY newsletter_id
        ) msg_counts ON msg_counts.newsletter_id = ns.id
    ")->fetch_assoc();

    $sentReplied = $mysqli->query("
        SELECT
            COUNT(DISTINCT CASE WHEN nq.execution_status = 2 THEN nq.user_id END) AS total_sent,
            COUNT(DISTINCT CASE WHEN nq.replied = 1 THEN nq.user_id END)        AS total_replied
        FROM newsletter_queue nq
    ")->fetch_assoc();

    echo json_encode([
        'status'    => 'success',
        'total'     => (int)($row['total'] ?? 0),
        'active'    => (int)($row['active_count'] ?? 0),
        'messages'  => (int)($row['total_messages'] ?? 0),
        'sent'      => (int)($sentReplied['total_sent'] ?? 0),
        'replies'   => (int)($sentReplied['total_replied'] ?? 0),
    ]);
    exit;
}

/* ==================================================================
 * ACTION: lazy_load  (paginated cards)
 * ================================================================== */
if ($action === 'lazy_load') {
    $limit  = min(max((int)($_POST['limit'] ?? 30), 1), 100);
    $offset = max((int)($_POST['offset'] ?? 0), 0);
    $search = trim($_POST['search'] ?? '');
    $status = $_POST['status'] ?? 'all';

    /* Build WHERE clause */
    $where = '1=1';
    $params = [];
    $types  = '';

    if ($search !== '') {
        $where .= ' AND (CAST(ns.id AS CHAR) LIKE ? OR LOWER(u.username) LIKE ?)';
        $like  = '%' . $search . '%';
        $params[] = $like;
        $params[] = strtolower($like);
        $types .= 'ss';
    }
    if ($status === 'active') {
        $where .= ' AND ns.active = 1';
    } elseif ($status === 'stopped') {
        $where .= ' AND ns.active = 0';
    }

    /* Main paginated query */
    $sql = "
        SELECT
            ns.id,
            ns.fake_user_id,
            ns.active,
            ns.created_at,
            ns.index_name,
            ns.scheduled_time,
            u.username AS fake_user_name,
            u.id       AS fake_user_pk,
            COUNT(DISTINCT nm.id) AS message_count,
            COUNT(DISTINCT CASE WHEN nq.execution_status = 2 THEN nq.user_id END) AS total_sent,
            COUNT(DISTINCT CASE WHEN nq.replied = 1          THEN nq.user_id END) AS total_replied,
            COUNT(DISTINCT CASE WHEN nq.execution_status IN (0,1) THEN nq.user_id END) AS pending
        FROM newsletter_settings ns
        LEFT JOIN newsletter_messages nm ON nm.newsletter_id = ns.id
        LEFT JOIN newsletter_queue     nq ON nq.newsletter_id = ns.id
        LEFT JOIN users                 u ON u.id             = ns.fake_user_id
        WHERE {$where}
        GROUP BY ns.id
        ORDER BY ns.id DESC
        LIMIT {$limit} OFFSET {$offset}
    ";

    if ($params) {
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $newsletters = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $newsletters = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    /* Reply rates for this batch */
    $ratesByNewsletter = [];
    if ($newsletters) {
        $ids = array_column($newsletters, 'id');
        $ph  = implode(',', array_fill(0, count($ids), '?'));
        $tp  = str_repeat('i', count($ids));
        $rateStmt = $mysqli->prepare("
            SELECT
                nq.newsletter_id,
                nq.msg_num,
                COUNT(DISTINCT CASE WHEN nq.execution_status = 2 THEN nq.user_id END) AS sent_users,
                COUNT(DISTINCT CASE WHEN nq.replied = 1          THEN nq.user_id END) AS replied_users
            FROM newsletter_queue nq
            WHERE nq.newsletter_id IN ({$ph})
            GROUP BY nq.newsletter_id, nq.msg_num
            ORDER BY nq.newsletter_id, nq.msg_num
        ");
        $rateStmt->bind_param($tp, ...$ids);
        $rateStmt->execute();
        $rateRows = $rateStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $rateStmt->close();
        foreach ($rateRows as $r) {
            $nid = (int)$r['newsletter_id'];
            $s   = (int)$r['sent_users'];
            $rp  = (int)$r['replied_users'];
            $ratesByNewsletter[$nid][] = [
                'msg_num'       => (int)$r['msg_num'],
                'sent_users'    => $s,
                'replied_users' => $rp,
                'reply_rate'    => $s > 0 ? round(($rp / $s) * 100, 2) : 0,
            ];
        }
    }

    /* Build card HTML */
    $html = '';
    foreach ($newsletters as $n) {
        $html .= renderCard($n, $ratesByNewsletter[(int)$n['id']] ?? []);
    }

    /* Check if there are more */
    $countSql = "SELECT COUNT(DISTINCT ns.id) AS total FROM newsletter_settings ns
                 LEFT JOIN users u ON u.id = ns.fake_user_id WHERE {$where}";
    if ($params) {
        $cStmt = $mysqli->prepare($countSql);
        $cStmt->bind_param($types, ...$params);
        $cStmt->execute();
        $totalRow = $cStmt->get_result()->fetch_assoc();
        $cStmt->close();
    } else {
        $totalRow = $mysqli->query($countSql)->fetch_assoc();
    }
    $totalCount = (int)($totalRow['total'] ?? 0);
    $hasMore = ($offset + $limit) < $totalCount;

    /* Build lightweight data map for JS actions */
    $dataMap = [];
    foreach ($newsletters as $n) {
        $nid = (int)$n['id'];
        $dataMap[] = [
            'id'             => $nid,
            'fake_user_id'   => (int)$n['fake_user_id'],
            'fake_user_name' => $n['fake_user_name'] ?? 'Unknown',
            'active'         => (int)$n['active'],
            'message_count'  => (int)$n['message_count'],
            'total_sent'     => (int)$n['total_sent'],
            'total_replied'  => (int)$n['total_replied'],
            'pending'        => (int)$n['pending'],
            'rates'          => $ratesByNewsletter[$nid] ?? [],
        ];
    }

    echo json_encode([
        'status'   => 'success',
        'html'     => $html,
        'data'     => $dataMap,
        'offset'   => $offset + $limit,
        'hasMore'  => $hasMore,
        'total'    => $totalCount,
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    exit;
}

/* ==================================================================
 * ACTION: edit_history  (retrieve history entries)
 * ================================================================== */
if ($action === 'edit_history') {
    $newsletterId = (int)($_POST['newsletter_id'] ?? $_GET['newsletter_id'] ?? 0);
    if (!$newsletterId) {
        echo json_encode(['status' => 'error', 'message' => 'Missing newsletter_id']);
        exit;
    }

    /* Check table exists — create if not (self-healing) */
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS newsletter_edit_history (
            id                INT AUTO_INCREMENT PRIMARY KEY,
            newsletter_id     INT NOT NULL,
            message_num       INT DEFAULT NULL,
            field_changed     VARCHAR(50) NOT NULL DEFAULT 'message_text',
            old_value         LONGTEXT,
            new_value         LONGTEXT,
            edited_by         INT NOT NULL DEFAULT 0,
            edited_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX ix_neh_nl   (newsletter_id),
            INDEX ix_neh_date (edited_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $stmt = $mysqli->prepare("
        SELECT h.*, u.username AS editor_name
        FROM newsletter_edit_history h
        LEFT JOIN users u ON u.id = h.edited_by
        WHERE h.newsletter_id = ?
        ORDER BY h.edited_at DESC
        LIMIT 200
    ");
    $stmt->bind_param('i', $newsletterId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    /* Group by edit session (same edited_at second) */
    $grouped = [];
    foreach ($rows as $row) {
        $ts = substr($row['edited_at'], 0, 19); // group by second
        if (!isset($grouped[$ts])) {
            $grouped[$ts] = [
                'edited_at'   => $row['edited_at'],
                'editor_name' => $row['editor_name'] ?? 'System',
                'edited_by'   => (int)$row['edited_by'],
                'changes'     => [],
            ];
        }
        $grouped[$ts]['changes'][] = $row;
    }
    $history = array_values($grouped);

    echo json_encode(['status' => 'success', 'history' => $history]);
    exit;
}

/* ==================================================================
 * ACTION: log_edit  (store edit snapshot after successful save)
 * ================================================================== */
if ($action === 'log_edit') {
    $newsletterId = (int)($_POST['newsletter_id'] ?? 0);
    $editedBy     = (int)($sm['user']['id'] ?? $_POST['edited_by'] ?? 0);
    $changes      = json_decode($_POST['changes'] ?? '[]', true);

    if (!$newsletterId || empty($changes)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing data']);
        exit;
    }

    /* Ensure table exists */
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS newsletter_edit_history (
            id                INT AUTO_INCREMENT PRIMARY KEY,
            newsletter_id     INT NOT NULL,
            message_num       INT DEFAULT NULL,
            field_changed     VARCHAR(50) NOT NULL DEFAULT 'message_text',
            old_value         LONGTEXT,
            new_value         LONGTEXT,
            edited_by         INT NOT NULL DEFAULT 0,
            edited_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX ix_neh_nl   (newsletter_id),
            INDEX ix_neh_date (edited_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $stmt = $mysqli->prepare("
        INSERT INTO newsletter_edit_history
            (newsletter_id, message_num, field_changed, old_value, new_value, edited_by)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($changes as $ch) {
        $msgNum   = (int)($ch['message_num'] ?? 0);
        $field    = substr($ch['field'] ?? 'message_text', 0, 50);
        $oldVal   = $ch['old_value'] ?? '';
        $newVal   = $ch['new_value'] ?? '';
        $stmt->bind_param('iisssi', $newsletterId, $msgNum, $field, $oldVal, $newVal, $editedBy);
        $stmt->execute();
    }
    $stmt->close();

    echo json_encode(['status' => 'success', 'message' => 'History logged']);
    exit;
}

/* ==================================================================
 * Fallback
 * ================================================================== */
echo json_encode(['status' => 'error', 'message' => 'Unknown action']);