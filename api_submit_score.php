<?php
// Ensure no HTML output interferes with JSON
header('Content-Type: application/json');
require_once 'config.php';

// 1. Authentication Check
// We use the existing session logic from config.php [cite: 2, 24]
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in. Session cookie required.']);
    exit;
}

// 2. Parse JSON Input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate parameters exists: { map: "Name", score: 123 }
if (!isset($data['map']) || !isset($data['score'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing parameters. Required: map (string), score (int)']);
    exit;
}

$mapName = trim($data['map']);
$turns = intval($data['score']);

// Basic data validation
if (empty($mapName) || $turns < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid data provided']);
    exit;
}

$conn = getDBConnection(); // Uses the connection logic from config.php [cite: 11]
$user_id = $_SESSION['user_id'];

// 3. Find Map ID by Name
// The leaderboard logic links scores to map_id, but the API receives a map Name [cite: 423]
$stmt = $conn->prepare("SELECT id FROM maps WHERE name = ?");
$stmt->bind_param("s", $mapName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Map not found']);
    $stmt->close();
    $conn->close();
    exit;
}

$map = $result->fetch_assoc();
$map_id = $map['id'];
$stmt->close();

// 4. Insert Score
// Inserting into the 'scores' table as seen in map_leaderboard.php queries 
try {
    $insert_stmt = $conn->prepare("INSERT INTO scores (user_id, map_id, turns, date) VALUES (?, ?, ?, NOW())");
    $insert_stmt->bind_param("iii", $user_id, $map_id, $turns);
    
    if ($insert_stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'Score submitted successfully',
            'data' => [
                'map' => $mapName,
                'turns' => $turns,
                'user_id' => $user_id
            ]
        ]);
    } else {
        throw new Exception("Database insert failed");
    }
    $insert_stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal Server Error']);
}

$conn->close();
?>