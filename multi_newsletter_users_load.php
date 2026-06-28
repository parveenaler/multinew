<?php
ini_set('display_errors',0);
ini_set('display_startup_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
require_once('assets/includes/core.php');

// Basic admin/auth guard placeholder. Adjust to your auth system.
if (!isset($sm) && !isset($_SESSION)) { session_start(); }
$admin = false;
if (function_exists('isAdmin')) {
    $admin = isAdmin();
} elseif (!empty($_SESSION['admin'])) {
    $admin = !!$_SESSION['admin'];
}
if (!$admin) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$cursor = isset($_GET['cursor']) ? (int)$_GET['cursor'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
if ($limit <= 0 || $limit > 50) { $limit = 50; }

if (mb_strlen($q) < 3) {
    echo json_encode([
        'results' => [],
        'pagination' => ['more' => false],
        'cursor' => $cursor,
    ]);
    exit;
}

$like = '%' . mb_strtolower($q) . '%';

$stmt = $mysqli->prepare("SELECT id, username FROM users WHERE fake = 0 AND LOWER(username) LIKE ? AND id > ? ORDER BY id ASC LIMIT ?");
if (!$stmt) {
    echo json_encode(['results' => [], 'pagination' => ['more' => false], 'cursor' => $cursor]);
    exit;
}
$stmt->bind_param('sii', $like, $cursor, $limit);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

$results = [];
$next_cursor = $cursor;
foreach ($rows as $r) {
    $results[] = [
        'id' => (int)$r['id'],
        'text' => $r['username'],
    ];
    $next_cursor = (int)$r['id'];
}

$hasMore = count($rows) === $limit;

echo json_encode([
    'results' => $results,
    'pagination' => ['more' => $hasMore],
    'cursor' => $next_cursor,
]);
