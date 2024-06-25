<?php
// db_connection.php
// This script establishes a connection to the SQLite database

try {
    // Path to the SQLite database file
    $db_path = realpath('db/final.db');
    
    // Establish a connection to the SQLite database
    $conn = new PDO("sqlite:$db_path");
    
    // Set PDO attributes
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If connection fails, display an error message
    echo "Connection failed: " . $e->getMessage();
    die(); // Exit the script
}
?>
