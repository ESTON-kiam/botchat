<?php
require_once '../config.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    
    
    $stmt = $conn->prepare("INSERT INTO conversations (user_id, title) VALUES (?, 'New Chat')");
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create conversation");
    }
    
    $conversation_id = $stmt->insert_id;
    
    
    echo json_encode([
        'success' => true,
        'conversation_id' => $conversation_id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}