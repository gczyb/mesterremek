<?php
require_once 'config.php';

// Get user ID from URL parameter
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['profile_picture']) {
            // Detect image type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->buffer($user['profile_picture']);
            
            header("Content-Type: " . $mime_type);
            header("Content-Length: " . strlen($user['profile_picture']));
            header("Cache-Control: max-age=3600"); // Cache for 1 hour
            
            // Output image data
            echo $user['profile_picture'];
            exit;
        } else {
            // No profile picture - return 404
            header("HTTP/1.0 404 Not Found");
            echo "No profile picture found";
        }
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "User not found";
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("HTTP/1.0 400 Bad Request");
    echo "Missing user ID";
}
?>