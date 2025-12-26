// ===== AI ASSISTANT - Hybrid (Voice + Chat) =====
// Combines voice-only button with minimal chat interface

const AI_API_URL = 'https://ai-assistant-z3fp.onrender.com/api/chat';
const DEFAULT_PROJECT = 'portfolio';

// State Management
let isListening = false;
let isSpeaking = false;
let isActive = false;
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
    const detectedLang = detectLanguage(transcript);
    
    stopListening();
    showProcessingState();
    
    // Add to chat if open
    if (chatOpen) {
        addChatMessage('user', transcript);
    }
    
    // Send to AI backend
    sendToAI(transcript, detectedLang);
}

// Handle speech recognition errors
function handleSpeechError(event) {
    console.error('Speech recognition error:', event.error);
    stopListening();
    showErrorState('Voice recognition failed. Try typing instead.');
    setTimeout(() => resetToIdle(), 2000);
}

// Handle speech recognition end
function handleSpeechEnd() {
    if (isListening) {
        stopListening();
    }
}

// Detect language from transcript
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
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data || !data.reply) {
            throw new Error('Invalid response from server');
        }
        
        const aiResponse = data.reply;
        
        // Check for backend errors
        if (aiResponse.includes("can't answer") || aiResponse.includes("API key") || aiResponse.includes("Sorry")) {
            console.error('Backend error:', aiResponse);
            const errorMsg = 'Backend configuration issue. Make sure OPENAI_API_KEY is set in Render.';
            showErrorState(errorMsg);
            if (chatOpen) {
                addChatMessage('assistant', '⚠️ ' + errorMsg);
            }
            setTimeout(() => resetToIdle(), 3000);
            return;
        }
        
        // Add to chat if open
        if (chatOpen) {
            addChatMessage('assistant', aiResponse);
        }
        
        // Speak the response
        speakResponse(aiResponse, language);
        
    } catch (error) {
        console.error('AI API Error:', error);
        const errorMsg = `Error: ${error.message}. Check console for details.`;
        showErrorState(errorMsg);
        if (chatOpen) {
            addChatMessage('assistant', '❌ ' + errorMsg);
        }
        setTimeout(() => resetToIdle(), 3000);
    }
}

// Speak AI response using TTS
function speakResponse(text, language) {
    if ('speechSynthesis' in window) {
        speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = language === 'sw' ? 'sw-TZ' : 'en-US';
        utterance.rate = 0.9;
        utterance.pitch = 1;
        utterance.volume = 0.8;
        
        currentUtterance = utterance;
        isSpeaking = true;
        
        showSpeakingState();
        
        utterance.onstart = () => {
            startSpeakingAnimation();
        };
        
        utterance.onend = () => {
            isSpeaking = false;
            currentUtterance = null;
            stopSpeakingAnimation();
            setTimeout(() => resetToIdle(), 500);
        };
        
        utterance.onerror = (error) => {
            console.error('Speech synthesis error:', error);
            isSpeaking = false;
            stopSpeakingAnimation();
            resetToIdle();
        };
        
        speechSynthesis.speak(utterance);
    }
}

// Toggle AI Assistant (voice button)
function toggleAIAssistant() {
    if (!recognition) {
        showErrorState('Voice recognition not supported.');
        return;
    }
    
    if (isActive) {
        deactivateAssistant();
    } else {
        activateAssistant();
    }
}

// Toggle Chat Window (global function for onclick)
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

// Make sendChatMessage global
window.sendChatMessage = sendChatMessage;

// Activate assistant
function activateAssistant() {
    isActive = true;
    const button = document.getElementById('ai-voice-btn');
    const container = document.getElementById('ai-voice-assistant');
    
    if (!button || !container) return;
    
    container.classList.add('active', 'centered');
    button.classList.add('active');
    startListening();
}

// Deactivate assistant
function deactivateAssistant() {
    isActive = false;
    const button = document.getElementById('ai-voice-btn');
    const container = document.getElementById('ai-voice-assistant');
    
    if (!button || !container) return;
    
    if (isListening && recognition) {
        recognition.stop();
        isListening = false;
    }
    
    if (isSpeaking && currentUtterance) {
        speechSynthesis.cancel();
        isSpeaking = false;
    }
    
    container.classList.remove('active', 'centered');
    button.classList.remove('active', 'listening', 'speaking', 'processing', 'error');
    stopAllAnimations();
}

// Start listening
function startListening() {
    if (!recognition || isListening) return;
    
    try {
        isListening = true;
        const button = document.getElementById('ai-voice-btn');
        if (button) {
            button.classList.add('listening');
            button.classList.remove('speaking', 'processing', 'error');
        }
        recognition.start();
        startListeningAnimation();
    } catch (error) {
        console.error('Error starting recognition:', error);
        isListening = false;
        showErrorState('Could not start listening.');
    }
}

// Stop listening
function stopListening() {
    isListening = false;
    const button = document.getElementById('ai-voice-btn');
    if (button) {
        button.classList.remove('listening');
    }
    stopListeningAnimation();
}

// Show processing state
function showProcessingState() {
    const button = document.getElementById('ai-voice-btn');
    if (button) {
        button.classList.add('processing');
        button.classList.remove('listening', 'speaking', 'error');
    }
}

// Show speaking state
function showSpeakingState() {
    const button = document.getElementById('ai-voice-btn');
    if (button) {
        button.classList.add('speaking');
        button.classList.remove('listening', 'processing', 'error');
    }
}

// Show error state
function showErrorState(message) {
    const button = document.getElementById('ai-voice-btn');
    if (button) {
        button.classList.add('error');
        button.classList.remove('listening', 'speaking', 'processing');
    }
    console.error(message);
}

// Reset to idle
function resetToIdle() {
    const button = document.getElementById('ai-voice-btn');
    if (button) {
        button.classList.remove('listening', 'speaking', 'processing', 'error');
    }
    stopAllAnimations();
}

// Animations
function startListeningAnimation() {
    const container = document.getElementById('ai-voice-assistant');
    if (container) container.classList.add('listening-animation');
}

function stopListeningAnimation() {
    const container = document.getElementById('ai-voice-assistant');
    if (container) container.classList.remove('listening-animation');
}

function startSpeakingAnimation() {
    const container = document.getElementById('ai-voice-assistant');
    if (container) container.classList.add('speaking-animation');
}

function stopSpeakingAnimation() {
    const container = document.getElementById('ai-voice-assistant');
    if (container) container.classList.remove('speaking-animation');
}

function stopAllAnimations() {
    stopListeningAnimation();
    stopSpeakingAnimation();
}

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

// Send chat message
async function sendChatMessage() {
    const input = document.getElementById('ai-message-input');
    if (!input) return;
    
    const message = input.value.trim();
    if (!message) return;
    
    input.value = '';
    addChatMessage('user', message);
    
    // Show loading
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'ai-message ai-message-assistant';
    loadingDiv.id = 'loading-msg';
    loadingDiv.innerHTML = `
        <div class="ai-message-content">
            <div class="ai-typing-indicator">
                <span></span><span></span><span></span>
            </div>
        </div>
    `;
    document.getElementById('ai-chat-messages').appendChild(loadingDiv);
    document.getElementById('ai-chat-messages').scrollTop = document.getElementById('ai-chat-messages').scrollHeight;
    
    const detectedLang = detectLanguage(message);
    
    try {
        await sendToAI(message, detectedLang);
        const loadingMsg = document.getElementById('loading-msg');
        if (loadingMsg) loadingMsg.remove();
    } catch (error) {
        const loadingMsg = document.getElementById('loading-msg');
        if (loadingMsg) loadingMsg.remove();
        addChatMessage('assistant', '❌ Error: ' + error.message);
    }
}

// Handle Enter key (global for onkeypress)
window.handleChatInputKeyPress = function(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendChatMessage();
    }
};

// Toggle voice input from chat (global for onclick)
window.toggleVoiceInput = function() {
    if (!recognition) {
        alert('Voice input not supported in your browser.');
        return;
    }
    
    if (isListening) {
        recognition.stop();
        isListening = false;
        updateVoiceButton();
    } else {
        recognition.start();
        isListening = true;
        updateVoiceButton();
    }
};

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

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const speechSupported = initSpeechRecognition();
    
    if (!speechSupported) {
        const button = document.getElementById('ai-voice-btn');
        if (button) {
            button.style.opacity = '0.5';
            button.style.cursor = 'not-allowed';
            button.title = 'Voice recognition not supported';
        }
    }
    
    const button = document.getElementById('ai-voice-btn');
    if (button) {
        button.addEventListener('click', toggleAIAssistant);
    }
    
    const chatToggle = document.getElementById('ai-chat-toggle');
    if (chatToggle) {
        chatToggle.addEventListener('click', toggleChat);
    }
    
    // Welcome message
    setTimeout(() => {
        if (document.getElementById('ai-chat-messages')) {
            addChatMessage('assistant', 'Haya! Niaje? I\'m Victor\'s AI assistant. Ask me anything! 😊');
        }
    }, 500);
    
    // Close on outside click
    document.addEventListener('click', function(event) {
        const container = document.getElementById('ai-voice-assistant');
        const chatWindow = document.getElementById('ai-chat-window');
        
        if (isActive && container && !container.contains(event.target) && 
            (!chatWindow || !chatWindow.contains(event.target))) {
            deactivateAssistant();
        }
    });
});

