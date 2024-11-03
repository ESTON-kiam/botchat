<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$stmt = $conn->prepare("SELECT * FROM conversations WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$conversations = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - AI Assistant</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="new-chat">
                <button id="newChatBtn" class="btn-primary">
                    <i class="fas fa-plus"></i> New Chat
                </button>
            </div>
            
            <div class="conversations">
                <?php while ($conv = $conversations->fetch_assoc()): ?>
                    <div class="conversation-item" data-id="<?php echo $conv['id']; ?>">
                        <i class="fas fa-message"></i>
                        <span><?php echo htmlspecialchars($conv['title']); ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
        
        
        <div class="chat-area">
            <div id="messageContainer" class="messages"></div>
            
            <div class="input-area">
                <form id="messageForm">
                    <textarea 
                        id="userInput" 
                        placeholder="Type your message here..." 
                        rows="1"
                        required
                    ></textarea>
                    <button type="submit">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/chat.js"></script>
</body>
</html>