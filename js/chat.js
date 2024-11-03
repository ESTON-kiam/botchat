document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('messageForm');
    const userInput = document.getElementById('userInput');
    const messageContainer = document.getElementById('messageContainer');
    const newChatBtn = document.getElementById('newChatBtn');
    let currentConversationId = null;

    // Auto-resize textarea
    userInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Create new chat
    newChatBtn.addEventListener('click', async function() {
        try {
            const response = await fetch('api/create_conversation.php', {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                currentConversationId = data.conversation_id;
                messageContainer.innerHTML = '';
                // Add new conversation to sidebar
                addConversationToSidebar(data.conversation_id, 'New Chat');
            }
        } catch (error) {
            console.error('Error creating new chat:', error);
        }
    });

    // Handle message submission
    messageForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = userInput.value.trim();
        if (!message) return;

        // Add user message to chat
        addMessageToChat('user', message);
        userInput.value = '';
        userInput.style.height = 'auto';

        try {
            const response = await fetch('api/send_