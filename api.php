<?php
// api.php
header('Content-Type: application/json');
require_once 'config.php';

// 1. Get JSON input from Godot
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

$action = $data['action'] ?? '';
$conn = getDBConnection();

// --- ACTION: LOGIN ---
if ($action === 'login') {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    // Check credentials
    $stmt = $conn->prepare("SELECT id, password, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));

            // Save token to database
            $update = $conn->prepare("UPDATE users SET api_token = ? WHERE id = ?");
            $update->bind_param("si", $token, $user['id']);
            $update->execute();

            echo json_encode([
                'status' => 'success',
                'token' => $token,
                'username' => $user['username']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
}

// --- ACTION: SUBMIT SCORE ---
elseif ($action === 'submit_score') {
    $token = $data['token'] ?? '';
    $map_id = $data['map_id'] ?? 0;
    $turns = $data['turns'] ?? 0;

    // Validate Token
    $stmt = $conn->prepare("SELECT id FROM users WHERE api_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows === 1) {
        $user = $user_result->fetch_assoc();
        $user_id = $user['id'];

        // Insert Score
        // Note: Assuming your scores table has user_id, map_id, turns, and date
        $insert = $conn->prepare("INSERT INTO scores (user_id, map_id, turns, date) VALUES (?, ?, ?, NOW())");
        $insert->bind_param("iii", $user_id, $map_id, $turns);
        
        if ($insert->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Score saved']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
    }
}

else {
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}

$conn->close();
?>