<?php

require_once 'Database.php';
include 'session.php';

header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=63072000');
header('X-Robots-Tag: noindex, nofollow', true);

checkSession();

$verify_user = $_SESSION['username'];

$database = new Database();
$db = $database->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        handlePost($db);
        break;
    case 'PUT':
        handlePut($db);
        break;
    case 'DELETE':
        handleDelete($db);
        break;
    case 'GET':
        handleGet($db, $verify_user);
        break;
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        break;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags($data));
}

function handlePost($db) {
    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->description) || !isset($data->amount) || !isset($data->status) || !isset($data->due_date) || !isset($data->username)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid input. Please provide description, amount, status, and due_date.']);
        return;
    }

    $description = sanitize($data->description);
    $amount = filter_var($data->amount, FILTER_VALIDATE_FLOAT);
    $status = sanitize($data->status);
    $due_date = sanitize($data->due_date);
    $username = sanitize($data->username);

    if ($amount === false || $amount <= 0) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid amount. Amount must be a positive number.']);
        return;
    }

    $checkQuery = 'SELECT COUNT(*) FROM expenses WHERE description = :description AND amount = :amount AND status = :status AND due_date = :due_date';
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':description', $description);
    $checkStmt->bindParam(':amount', $amount);
    $checkStmt->bindParam(':status', $status);
    $checkStmt->bindParam(':due_date', $due_date);
    $checkStmt->execute();

    if ($checkStmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(['message' => 'Duplicate entry detected.']);
        return;
    }

    $query = 'INSERT INTO expenses (description, amount, status, due_date, username) VALUES (:description, :amount, :status, :due_date, :username)';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':due_date', $due_date);
    $stmt->bindParam(':username', $username);

    try {
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['message' => 'Expense created successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create expense.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Internal Server Error', 'error' => $e->getMessage()]);
    }
}

function handlePut($db) {
    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->id) || !isset($data->description) || !isset($data->amount) || !isset($data->status) || !isset($data->due_date)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid input. Please provide id, description, amount, status, and due_date.']);
        return;
    }

    $id = filter_var($data->id, FILTER_VALIDATE_INT);
    $description = sanitize($data->description);
    $amount = filter_var($data->amount, FILTER_VALIDATE_FLOAT);
    $status = sanitize($data->status);
    $due_date = sanitize($data->due_date);

    if ($id === false || $amount === false || $amount <= 0) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid input. ID must be an integer and amount must be a positive number.']);
        return;
    }

    $checkQuery = 'SELECT COUNT(*) FROM expenses WHERE description = :description AND amount = :amount AND status = :status AND due_date = :due_date AND id != :id';
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':description', $description);
    $checkStmt->bindParam(':amount', $amount);
    $checkStmt->bindParam(':status', $status);
    $checkStmt->bindParam(':due_date', $due_date);
    $checkStmt->bindParam(':id', $id);
    $checkStmt->execute();

    if ($checkStmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(['message' => 'Duplicate entry detected.']);
        return;
    }

    $query = 'UPDATE expenses SET description = :description, amount = :amount, status = :status, due_date = :due_date WHERE id = :id';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':due_date', $due_date);
    $stmt->bindParam(':id', $id);

    try {
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(['message' => 'Expense updated successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update expense.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Internal Server Error', 'error' => $e->getMessage()]);
    }
}

function handleDelete($db) {
    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid input. Please provide the id.']);
        return;
    }

    $id = filter_var($data->id, FILTER_VALIDATE_INT);
    if ($id === false) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid ID.']);
        return;
    }

    $query = 'DELETE FROM expenses WHERE id = :id';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);

    try {
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(['message' => 'Expense deleted successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete expense.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Internal Server Error', 'error' => $e->getMessage()]);
    }
}

function handleGet($db, $verify_user) {

    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 3;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $status = isset($_GET['status']) ? sanitize($_GET['status']) : null;

    if ($limit < 1 || $offset < 0) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid limit or offset']);
        return;
    }

    $query = 'SELECT * FROM expenses WHERE username = :username';

    if ($status) {
        $query .= ' AND status = :status';
    }

    $query .= ' ORDER BY created_at DESC';
    $query .= ' LIMIT :limit OFFSET :offset';

    try {

        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $verify_user, PDO::PARAM_STR);
        if ($status) {
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countQuery = 'SELECT COUNT(*) as total FROM expenses WHERE username = :username';
        if ($status) {
            $countQuery .= ' AND status = :status';
        }
        $countStmt = $db->prepare($countQuery);
        $countStmt->bindParam(':username', $verify_user, PDO::PARAM_STR);
        if ($status) {
            $countStmt->bindParam(':status', $status, PDO::PARAM_STR);
        }
        $countStmt->execute();
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        http_response_code(200);
        echo json_encode(['expenses' => $expenses, 'total' => $totalCount]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Internal Server Error']);
    }
}

?>