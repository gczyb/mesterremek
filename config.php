<?php
session_start();

// Database configuration - Auto-detect setup
function detectDatabaseConfig() {
    // Common MySQL configurations to try
    $configurations = [
        // XAMPP (most common)
        ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => '', 'name' => 'XAMPP'],
        ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => '', 'name' => 'XAMPP localhost'],
        
        // MAMP
        ['host' => '127.0.0.1', 'port' => 8889, 'user' => 'root', 'pass' => 'root', 'name' => 'MAMP 8889'],
        ['host' => 'localhost', 'port' => 8889, 'user' => 'root', 'pass' => 'root', 'name' => 'MAMP localhost 8889'],
        ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => 'root', 'name' => 'MAMP 3306'],
        
        // Standard MySQL
        ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => 'root', 'name' => 'Standard MySQL'],
        ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => 'root', 'name' => 'Standard MySQL IP'],
        
        // MySQL with socket (Mac)
        ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => '', 'name' => 'MySQL Socket'],
    ];
    
    foreach ($configurations as $config) {
        $conn = @new mysqli($config['host'], $config['user'], $config['pass'], 'information_schema', $config['port']);
        
        if (!$conn->connect_error) {
            $conn->close();
            return $config;
        }
    }
    
    return null;
}

// Try to detect configuration
$detectedConfig = detectDatabaseConfig();

if ($detectedConfig) {
    define('DB_HOST', $detectedConfig['host']);
    define('DB_PORT', $detectedConfig['port']);
    define('DB_USER', $detectedConfig['user']);
    define('DB_PASS', $detectedConfig['pass']);
    define('DB_NAME', 'treasure_quest');
    define('DB_CONFIG_NAME', $detectedConfig['name']);
} else {
    // Fallback to manual configuration
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', 3306);
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'treasure_quest');
    define('DB_CONFIG_NAME', 'Manual');
}

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        // If treasure_quest database doesn't exist, try to create it
        $conn_temp = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
        
        if (!$conn_temp->connect_error) {
            $conn_temp->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
            $conn_temp->close();
            
            // Try connecting again
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            if (!$conn->connect_error) {
                return $conn;
            }
        }
        
        // Show helpful error message
        die("
        <div style='font-family: Arial; padding: 20px; background: #0f172a; color: #cbd5e1;'>
            <h2 style='color: #ef4444;'>Database Connection Failed</h2>
            <p><strong>Detected Configuration:</strong> " . DB_CONFIG_NAME . "</p>
            <p><strong>Error:</strong> " . $conn->connect_error . "</p>
            
            <h3 style='color: #fbbf24; margin-top: 20px;'>Connection Details:</h3>
            <ul>
                <li>Host: " . DB_HOST . "</li>
                <li>Port: " . DB_PORT . "</li>
                <li>User: " . DB_USER . "</li>
                <li>Database: " . DB_NAME . "</li>
            </ul>
            
            <h3 style='color: #fbbf24; margin-top: 20px;'>Quick Fixes:</h3>
            <ol>
                <li><strong>Make sure MySQL is running</strong>
                    <ul>
                        <li>XAMPP: Start Apache and MySQL in XAMPP Control Panel</li>
                        <li>MAMP: Start servers in MAMP application</li>
                    </ul>
                </li>
                <li><strong>Create the database:</strong>
                    <ul>
                        <li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin' style='color: #fbbf24;'>http://localhost/phpmyadmin</a></li>
                        <li>Or MAMP: <a href='http://localhost:8888/phpMyAdmin/' style='color: #fbbf24;'>http://localhost:8888/phpMyAdmin/</a></li>
                        <li>Click 'New' and create database named: <strong>treasure_quest</strong></li>
                        <li>Import the SQL from database.sql file</li>
                    </ul>
                </li>
                <li><strong>Manual Configuration:</strong> Edit config.php and set your credentials manually</li>
            </ol>
            
            <h3 style='color: #fbbf24; margin-top: 20px;'>Need Help?</h3>
            <p>Run this in Terminal to test your MySQL connection:</p>
            <pre style='background: #1e293b; padding: 10px; border-radius: 5px;'>mysql -u root -p</pre>
        </div>
        ");
    }
    
    return $conn;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    // UPDATED: Added 'admin' to the SELECT query
    $stmt = $conn->prepare("SELECT id, username, email, profile_picture, admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $user;
}
?>