<?php
require_once '../config.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['message']) || !isset($_POST['conversation_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $message = sanitize_input($_POST['message']);
    $conversation_id = (int)$_POST['conversation_id'];
    
    
    $stmt = $conn->prepare("SELECT id FROM conversations WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $conversation_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Invalid conversation");
    }
    
    
    $ai_response = callOpenAIAPI($message);
    
    if ($ai_response === false) {
        throw new Exception("Failed to get AI response");
    }
    
    
    $stmt = $conn->prepare("INSERT INTO messages (conversation_id, user_id, message, response) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $conversation_id, $user_id, $message, $ai_response);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to save message");
    }
    
   
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE conversation_id = ?");
    $stmt->bind_param("i", $conversation_id);
    $stmt->execute();
    $count_result = $stmt->get_result()->fetch_assoc();
    
    if ($count_result['count'] === 1) {
        $title = substr($message, 0, 50) . (strlen($message) > 50 ? '...' : '');
        $stmt = $conn->prepare("UPDATE conversations SET title = ? WHERE id = ?");
        $stmt->bind_param("si", $title, $conversation_id);
        $stmt->execute();
    }
    
   
    echo json_encode([
        'success' => true,
        'response' => $ai_response
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}