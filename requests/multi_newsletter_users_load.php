<?php
// Paginated user search for multi-newsletter compose UI
// Returns JSON: { status: 'success', rows, total, offset, hasMore }

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');

try {
    // Core/bootstrap
    require_once dirname(__DIR__) . '/assets/includes/core.php';

    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    // Basic admin/auth guard. Adjust to your project's admin session/token check as needed
    if (!isset($_SESSION['admin']) && !isset($_SESSION['staff'])) {
        echo json_encode([ 'status' => 'error', 'message' => 'Unauthorized' ]);
        exit;
    }

    // DB handle
    if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        echo json_encode([ 'status' => 'error', 'message' => 'DB not initialized' ]);
        exit;
    }

    // Inputs
    $limit  = isset($_POST['limit']) ? (int)$_POST['limit'] : 200;
    if ($limit <= 0) { $limit = 200; }
    if ($limit > 500) { $limit = 500; }
    $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;

    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $age1   = isset($_POST['age1']) ? (int)$_POST['age1'] : 0;
    $age2   = isset($_POST['age2']) ? (int)$_POST['age2'] : 0;
    $country = isset($_POST['country']) ? trim($_POST['country']) : '';
    $city    = isset($_POST['city']) ? trim($_POST['city']) : '';
    $premium = isset($_POST['premium']) ? trim($_POST['premium']) : '';
    $verified = isset($_POST['verified']) ? trim($_POST['verified']) : '';

    // Build WHERE
    $where = [];
    $params = [];
    $types = '';

    // Exclude fake users by default (assuming "fake" column; adjust if different)
    if (column_exists($mysqli, 'users', 'fake')) {
        $where[] = 'fake = 0';
    }

    if ($search !== '') {
        // Sargable prefix match on username OR email if available
        if (column_exists($mysqli, 'users', 'email')) {
            $where[] = '(username LIKE CONCAT(?, "%") OR email LIKE CONCAT(?, "%"))';
            $params[] = $search; $params[] = $search; $types .= 'ss';
        } else {
            $where[] = 'username LIKE CONCAT(?, "%")';
            $params[] = $search; $types .= 's';
        }
    }
    if ($gender !== '') {
        // Many schemas use s_gender for stored gender
        $col = column_exists($mysqli, 'users', 's_gender') ? 's_gender' : (column_exists($mysqli, 'users', 'gender') ? 'gender' : '');
        if ($col !== '') {
            $where[] = "$col = ?";
            $params[] = $gender; $types .= 's';
        }
    }
    if ($age1 > 0) {
        if (column_exists($mysqli, 'users', 'age')) {
            $where[] = 'age >= ?';
            $params[] = $age1; $types .= 'i';
        }
    }
    if ($age2 > 0) {
        if (column_exists($mysqli, 'users', 'age')) {
            $where[] = 'age <= ?';
            $params[] = $age2; $types .= 'i';
        }
    }
    if ($country !== '' && column_exists($mysqli, 'users', 'country')) {
        $where[] = 'country = ?';
        $params[] = $country; $types .= 's';
    }
    if ($city !== '' && column_exists($mysqli, 'users', 'city')) {
        $where[] = 'city = ?';
        $params[] = $city; $types .= 's';
    }
    if ($premium !== '') {
        if (column_exists($mysqli, 'users', 'premium')) {
            $where[] = 'premium = ?';
            $params[] = (int)$premium; $types .= 'i';
        }
    }
    if ($verified !== '') {
        if (column_exists($mysqli, 'users', 'verified')) {
            $where[] = 'verified = ?';
            $params[] = (int)$verified; $types .= 'i';
        }
    }

    $whereSql = '';
    if (!empty($where)) {
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    // Columns to select — keep light
    $selectCols = ['id', 'username'];
    foreach (['city','country','last_access','credits','premium','verified'] as $c) {
        if (column_exists($mysqli, 'users', $c)) $selectCols[] = $c;
    }
    $selectSql = 'SELECT ' . implode(', ', $selectCols) . ' FROM users ' . $whereSql . ' ORDER BY id DESC LIMIT ? OFFSET ?';

    $stmt = $mysqli->prepare($selectSql);
    if (!$stmt) {
        echo json_encode([ 'status' => 'error', 'message' => 'Prepare failed' ]);
        exit;
    }
    $typesLimit = $types . 'ii';
    $bindParams = $params;
    $bindParams[] = $limit; $bindParams[] = $offset;

    $stmt->bind_param($typesLimit, ...$bindParams);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
    }
    $stmt->close();

    // Total count (could be expensive — compute for first page or when explicitly requested)
    $countSql = 'SELECT COUNT(*) AS c FROM users ' . $whereSql;
    $stmt2 = $mysqli->prepare($countSql);
    if ($stmt2) {
        if ($types !== '') {
            $stmt2->bind_param($types, ...$params);
        }
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $total = 0;
        if ($res2 && ($row = $res2->fetch_assoc())) {
            $total = (int)$row['c'];
        }
        $stmt2->close();
    } else {
        $total = 0;
    }

    $nextOffset = $offset + count($rows);
    $hasMore = $nextOffset < $total;

    echo json_encode([
        'status' => 'success',
        'rows' => $rows,
        'total' => $total,
        'offset' => $nextOffset,
        'hasMore' => $hasMore,
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([ 'status' => 'error', 'message' => 'Server error' ]);
    exit;
}

// Utility: check if column exists on a table
function column_exists(mysqli $mysqli, string $table, string $column): bool {
    $sql = 'SHOW COLUMNS FROM `'.$table.'` LIKE ?';
    if (!$stmt = $mysqli->prepare($sql)) return false;
    $stmt->bind_param('s', $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = $res && $res->num_rows > 0;
    $stmt->close();
    return $ok;
}
