
<?php
// api.php
header('Content-Type: application/json; charset=utf-8');

// Allow CORS only if you access from different origin (not needed for same-site XAMPP):
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

require_once 'config.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? null);
if (!$action) {
    echo json_encode(['success' => false, 'error' => 'No action provided']);
    exit;
}

if ($action === 'create') {
    // Expecting: name, email, message via POST (application/x-www-form-urlencoded or fetch form)
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $message === '') {
        echo json_encode(['success' => false, 'error' => 'Name and message required']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO feedbacks (name, email, message, created_at) VALUES (:name, :email, :message, NOW())');
    $stmt->execute([':name' => $name, ':email' => $email, ':message' => $message]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

if ($action === 'read') {
    $stmt = $pdo->query('SELECT id, name, email, message, created_at FROM feedbacks ORDER BY created_at DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

if ($action === 'delete') {
    // Accept POST with id
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid id']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM feedbacks WHERE id = :id');
    $stmt->execute([':id' => $id]);
    echo json_encode(['success' => true, 'deleted' => $stmt->rowCount()]);
    exit;
}

// fallback
echo json_encode(['success' => false, 'error' => 'Unknown action']);
