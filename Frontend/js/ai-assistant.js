// AI Assistant Configuration
const AI_API_URL = 'https://ai-assistant-z3fp.onrender.com/api/chat';
const DEFAULT_PROJECT = 'portfolio';

// AI Assistant State
let isAssistantOpen = false;
let isListening = false;
let recognition = null;
let currentUtterance = null;

// Initialize Speech Recognition (if available)
function initSpeechRecognition() {
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            document.getElementById('ai-message-input').value = transcript;
            isListening = false;
            updateVoiceButton();
        };

        recognition.onerror = function(event) {
            console.error('Speech recognition error:', event.error);
            isListening = false;
            updateVoiceButton();
            showNotification('Voice recognition error. Please try typing instead.', 'error');
        };

        recognition.onend = function() {
            isListening = false;
            updateVoiceButton();
        };
    }
}

// Toggle AI Assistant
function toggleAIAssistant() {
    const assistant = document.getElementById('ai-assistant');
    const chatWindow = document.getElementById('ai-chat-window');
    
    isAssistantOpen = !isAssistantOpen;
    
    if (isAssistantOpen) {
        assistant.classList.add('active');
        chatWindow.classList.add('active');
        document.getElementById('ai-message-input').focus();
    } else {
        assistant.classList.remove('active');
        chatWindow.classList.remove('active');
        // Stop any ongoing speech
        if (currentUtterance) {
            speechSynthesis.cancel();
        }
        if (isListening && recognition) {
            recognition.stop();
            isListening = false;
        }
    }
}

// Send message to AI
async function sendAIMessage() {
    const input = document.getElementById('ai-message-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Add user message to chat
    addChatMessage('user', message);
    input.value = '';
    
    // Show loading indicator
    const loadingId = addChatMessage('assistant', 'Thinking...', true);
    
    try {
        const response = await fetch(AI_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                project: DEFAULT_PROJECT
            })
        });
        
        const data = await response.json();
        
        // Remove loading message
        const loadingMsg = document.getElementById(loadingId);
        if (loadingMsg) loadingMsg.remove();
        
        // Add AI response
        addChatMessage('assistant', data.reply);
        
        // Speak the response
        speakMessage(data.reply);
        
    } catch (error) {
        console.error('AI API Error:', error);
        
        // Remove loading message
        const loadingMsg = document.getElementById(loadingId);
        if (loadingMsg) loadingMsg.remove();
        
        addChatMessage('assistant', 'Sorry, I can\'t connect right now. Please try again later. 😴');
    }
}

// Add message to chat
function addChatMessage(role, message, isLoading = false) {
    const chatMessages = document.getElementById('ai-chat-messages');
    const messageId = 'msg-' + Date.now();
    
    const messageDiv = document.createElement('div');
    messageDiv.id = messageId;
    messageDiv.className = `ai-message ai-message-${role}`;
    
    if (isLoading) {
        messageDiv.innerHTML = `
            <div class="ai-message-content">
                <div class="ai-typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="ai-message-content">${escapeHtml(message)}</div>
            <div class="ai-message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
        `;
    }
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    return messageId;
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Speak message using browser SpeechSynthesis
function speakMessage(text) {
    if ('speechSynthesis' in window) {
        // Cancel any ongoing speech
        speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-US';
        utterance.rate = 0.9;
        utterance.pitch = 1;
        utterance.volume = 0.8;
        
        currentUtterance = utterance;
        
        utterance.onend = function() {
            currentUtterance = null;
        };
        
        speechSynthesis.speak(utterance);
    }
}

// Toggle voice input
function toggleVoiceInput() {
    if (!recognition) {
        showNotification('Voice input not supported in your browser.', 'error');
        return;
    }
    
    if (isListening) {
        recognition.stop();
        isListening = false;
    } else {
        recognition.start();
        isListening = true;
    }
    
    updateVoiceButton();
}

// Update voice button state
function updateVoiceButton() {
    const voiceBtn = document.getElementById('ai-voice-btn');
    if (voiceBtn) {
        if (isListening) {
            voiceBtn.classList.add('listening');
            voiceBtn.innerHTML = '<i class="fas fa-stop"></i>';
        } else {
            voiceBtn.classList.remove('listening');
            voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        }
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // You can implement a toast notification here if needed
    console.log(`[${type.toUpperCase()}] ${message}`);
}

// Handle Enter key in input
function handleAIInputKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendAIMessage();
    }
}

// Initialize AI Assistant when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize speech recognition
    initSpeechRecognition();
    
    // Add welcome message
    setTimeout(() => {
        if (document.getElementById('ai-chat-messages')) {
            addChatMessage('assistant', 'Haya! Niaje? I\'m Victor\'s AI assistant. Ask me anything about his projects, skills, or experience! 😊');
        }
    }, 500);
});

