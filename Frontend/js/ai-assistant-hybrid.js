// ===== AI ASSISTANT - Robot Icon with Chat + Voice =====
// Both chat and voice work together

const AI_API_URL = 'https://ai-assistant-z3fp.onrender.com/api/chat';
const DEFAULT_PROJECT = 'portfolio';

// State Management
let isListening = false;
let isSpeaking = false;
let chatOpen = false;
let recognition = null;
let currentUtterance = null;

// Language Detection
const SWAHILI_KEYWORDS = ['niaje', 'mambo', 'sawa', 'asante', 'karibu', 'haya', 'eh', 'pole', 'hapana', 'ndiyo'];
const ENGLISH_KEYWORDS = ['hello', 'hi', 'hey', 'what', 'who', 'tell', 'about', 'how', 'when', 'where'];

// Initialize Speech Recognition
function initSpeechRecognition() {
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';
        
        recognition.onresult = handleSpeechResult;
        recognition.onerror = handleSpeechError;
        recognition.onend = handleSpeechEnd;
        
        return true;
    }
    return false;
}

// Handle speech recognition result
function handleSpeechResult(event) {
    const transcript = event.results[0][0].transcript;
    console.log('Speech recognized:', transcript);
    
    stopListening();
    
    // Add to chat
    addChatMessage('user', transcript);
    
    // Show processing
    showProcessingInChat();
    
    // Send to AI
    const detectedLang = detectLanguage(transcript);
    sendToAI(transcript, detectedLang);
}

// Handle speech recognition errors
function handleSpeechError(event) {
    console.error('Speech recognition error:', event.error);
    stopListening();
    updateVoiceButton();
    addChatMessage('assistant', 'Voice recognition error. Try typing instead.');
}

// Handle speech recognition end
function handleSpeechEnd() {
    if (isListening) {
        stopListening();
        updateVoiceButton();
    }
}

// Detect language
function detectLanguage(text) {
    const lowerText = text.toLowerCase();
    let swahiliCount = 0;
    let englishCount = 0;
    
    SWAHILI_KEYWORDS.forEach(keyword => {
        if (lowerText.includes(keyword)) swahiliCount++;
    });
    
    ENGLISH_KEYWORDS.forEach(keyword => {
        if (lowerText.includes(keyword)) englishCount++;
    });
    
    return swahiliCount > englishCount ? 'sw' : 'en';
}

// Send message to AI backend
async function sendToAI(message, language) {
    console.log('Sending to AI:', message);
    
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
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('AI Response:', data);
        
        if (!data || !data.reply) {
            throw new Error('Invalid response from server');
        }
        
        const aiResponse = data.reply;
        console.log('AI Reply:', aiResponse);
        
        // Remove processing message
        removeProcessingFromChat();
        
        // Check for errors
        if (aiResponse.includes("can't answer") || aiResponse.includes("API key") || aiResponse.includes("Sorry, I can't") || aiResponse.includes("Backend configuration")) {
            console.error('Backend error:', aiResponse);
            addChatMessage('assistant', '⚠️ ' + aiResponse + '\n\n💡 **Quick Fix:**\n1. Go to Render dashboard\n2. Check Environment tab - make sure OPENAI_API_KEY is set\n3. Click "Manual Deploy" → "Deploy latest commit"\n4. Wait for redeploy to finish\n\nOr test: https://ai-assistant-z3fp.onrender.com/api/debug');
            return;
        }
        
        // Add to chat
        addChatMessage('assistant', aiResponse);
        
        // Speak the response
        speakResponse(aiResponse, language);
        
    } catch (error) {
        console.error('AI API Error:', error);
        removeProcessingFromChat();
        addChatMessage('assistant', '❌ Error: ' + error.message + '. Check console for details.');
    }
}

// Speak AI response using TTS
function speakResponse(text, language) {
    if (!('speechSynthesis' in window)) {
        console.warn('Speech synthesis not supported');
        return;
    }
    
    // Cancel any ongoing speech
    speechSynthesis.cancel();
    
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = language === 'sw' ? 'sw-TZ' : 'en-US';
    utterance.rate = 0.9;
    utterance.pitch = 1;
    utterance.volume = 0.8;
    
    currentUtterance = utterance;
    isSpeaking = true;
    
    console.log('Speaking:', text);
    
    utterance.onstart = () => {
        console.log('Speech started');
        const button = document.getElementById('ai-voice-btn');
        if (button) button.classList.add('speaking');
    };
    
    utterance.onend = () => {
        console.log('Speech ended');
        isSpeaking = false;
        currentUtterance = null;
        const button = document.getElementById('ai-voice-btn');
        if (button) button.classList.remove('speaking');
    };
    
    utterance.onerror = (error) => {
        console.error('Speech synthesis error:', error);
        isSpeaking = false;
        const button = document.getElementById('ai-voice-btn');
        if (button) button.classList.remove('speaking');
    };
    
    speechSynthesis.speak(utterance);
}

// Toggle Chat Window
window.toggleChat = function() {
    const chatWindow = document.getElementById('ai-chat-window');
    if (!chatWindow) return;
    
    chatOpen = !chatOpen;
    
    if (chatOpen) {
        chatWindow.classList.add('active');
        document.getElementById('ai-message-input')?.focus();
    } else {
        chatWindow.classList.remove('active');
    }
};

// Toggle Robot Button (opens chat and starts voice)
window.toggleAIAssistant = function() {
    // Open chat if not open
    if (!chatOpen) {
        toggleChat();
    }
    
    // Start voice recognition if available
    if (recognition && !isListening && !isSpeaking) {
        startVoiceInput();
    }
};

// Start voice input
function startVoiceInput() {
    if (!recognition || isListening) return;
    
    try {
        isListening = true;
        updateVoiceButton();
        recognition.start();
        console.log('Voice recognition started');
    } catch (error) {
        console.error('Error starting recognition:', error);
        isListening = false;
        updateVoiceButton();
        addChatMessage('assistant', 'Could not start voice recognition. Try typing instead.');
    }
}

// Stop listening
function stopListening() {
    isListening = false;
    updateVoiceButton();
}

// Update voice button state
function updateVoiceButton() {
    const voiceBtn = document.getElementById('ai-chat-voice-btn');
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

// Toggle voice input from chat button
window.toggleVoiceInput = function() {
    if (!recognition) {
        alert('Voice input not supported in your browser.');
        return;
    }
    
    if (isListening) {
        recognition.stop();
        stopListening();
    } else {
        startVoiceInput();
    }
};

// Chat Functions
function addChatMessage(role, message) {
    const chatMessages = document.getElementById('ai-chat-messages');
    if (!chatMessages) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `ai-message ai-message-${role}`;
    messageDiv.innerHTML = `
        <div class="ai-message-content">${escapeHtml(message)}</div>
        <div class="ai-message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
    `;
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showProcessingInChat() {
    const chatMessages = document.getElementById('ai-chat-messages');
    if (!chatMessages) return;
    
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'ai-message ai-message-assistant';
    loadingDiv.id = 'processing-msg';
    loadingDiv.innerHTML = `
        <div class="ai-message-content">
            <div class="ai-typing-indicator">
                <span></span><span></span><span></span>
            </div>
        </div>
    `;
    chatMessages.appendChild(loadingDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function removeProcessingFromChat() {
    const processingMsg = document.getElementById('processing-msg');
    if (processingMsg) {
        processingMsg.remove();
    }
}

// Send chat message
window.sendChatMessage = async function() {
    const input = document.getElementById('ai-message-input');
    if (!input) return;
    
    const message = input.value.trim();
    if (!message) return;
    
    input.value = '';
    addChatMessage('user', message);
    showProcessingInChat();
    
    const detectedLang = detectLanguage(message);
    
    try {
        await sendToAI(message, detectedLang);
    } catch (error) {
        removeProcessingFromChat();
        addChatMessage('assistant', '❌ Error: ' + error.message);
    }
};

// Handle Enter key
window.handleChatInputKeyPress = function(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendChatMessage();
    }
};

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing AI Assistant...');
    
    // Initialize speech recognition
    const speechSupported = initSpeechRecognition();
    console.log('Speech recognition supported:', speechSupported);
    
    if (!speechSupported) {
        const button = document.getElementById('ai-voice-btn');
        if (button) {
            button.style.opacity = '0.7';
            button.title = 'Voice recognition not supported';
        }
    }
    
    // Robot button click handler
    const robotBtn = document.getElementById('ai-voice-btn');
    if (robotBtn) {
        robotBtn.addEventListener('click', toggleAIAssistant);
        console.log('Robot button handler attached');
    }
    
    // Chat toggle button
    const chatToggle = document.getElementById('ai-chat-toggle');
    if (chatToggle) {
        chatToggle.addEventListener('click', toggleChat);
    }
    
    // Welcome message
    setTimeout(() => {
        const chatMessages = document.getElementById('ai-chat-messages');
        if (chatMessages) {
            addChatMessage('assistant', 'Haya! Niaje? I\'m Victor\'s AI assistant. Ask me anything! 😊\n\nClick the robot button to use voice, or type your question.');
        }
    }, 500);
    
    console.log('AI Assistant initialized');
});
