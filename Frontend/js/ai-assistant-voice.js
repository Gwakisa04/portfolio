// ===== AI VOICE ASSISTANT - Voice-Only Implementation =====
// Senior Frontend Engineer & UI/UX Expert Design

const AI_API_URL = 'https://ai-assistant-z3fp.onrender.com/api/chat';
const DEFAULT_PROJECT = 'portfolio';

// State Management
let isListening = false;
let isSpeaking = false;
let isActive = false;
let recognition = null;
let currentUtterance = null;
let animationFrame = null;

// Language Detection (simple keyword-based)
const SWAHILI_KEYWORDS = ['niaje', 'mambo', 'sawa', 'asante', 'karibu', 'haya', 'eh', 'pole', 'hapana', 'ndiyo'];
const ENGLISH_KEYWORDS = ['hello', 'hi', 'hey', 'what', 'who', 'tell', 'about', 'how', 'when', 'where'];

// Initialize Speech Recognition
function initSpeechRecognition() {
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US'; // Start with English, will detect language
        
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
    
    // Send to AI backend
    sendToAI(transcript, detectedLang);
}

// Handle speech recognition errors
function handleSpeechError(event) {
    console.error('Speech recognition error:', event.error);
    stopListening();
    showErrorState('Voice recognition failed. Please try again.');
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
    
    // If Swahili keywords found, return Swahili, else English
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
        
        const data = await response.json();
        const aiResponse = data.reply || "Sorry, I can't answer right now.";
        
        // Speak the response in detected language
        speakResponse(aiResponse, language);
        
    } catch (error) {
        console.error('AI API Error:', error);
        showErrorState('Connection error. Please try again.');
        setTimeout(() => resetToIdle(), 2000);
    }
}

// Speak AI response using TTS
function speakResponse(text, language) {
    if ('speechSynthesis' in window) {
        // Cancel any ongoing speech
        speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = language === 'sw' ? 'sw-TZ' : 'en-US';
        utterance.rate = 0.9;
        utterance.pitch = 1;
        utterance.volume = 0.8;
        
        currentUtterance = utterance;
        isSpeaking = true;
        
        // Show speaking state
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
    } else {
        // Fallback if TTS not available
        showErrorState('Voice output not supported.');
        setTimeout(() => resetToIdle(), 2000);
    }
}

// Toggle AI Assistant
function toggleAIAssistant() {
    if (!recognition) {
        showErrorState('Voice recognition not supported in your browser.');
        return;
    }
    
    if (isActive) {
        // Deactivate
        deactivateAssistant();
    } else {
        // Activate
        activateAssistant();
    }
}

// Activate assistant - move to center, start listening
function activateAssistant() {
    isActive = true;
    const button = document.getElementById('ai-voice-btn');
    const container = document.getElementById('ai-voice-assistant');
    
    if (!button || !container) return;
    
    // Move to center
    container.classList.add('active', 'centered');
    button.classList.add('active');
    
    // Start listening
    startListening();
}

// Deactivate assistant - return to corner
function deactivateAssistant() {
    isActive = false;
    const button = document.getElementById('ai-voice-btn');
    const container = document.getElementById('ai-voice-assistant');
    
    if (!button || !container) return;
    
    // Stop any ongoing processes
    if (isListening && recognition) {
        recognition.stop();
        isListening = false;
    }
    
    if (isSpeaking && currentUtterance) {
        speechSynthesis.cancel();
        isSpeaking = false;
    }
    
    // Return to corner
    container.classList.remove('active', 'centered');
    button.classList.remove('active', 'listening', 'speaking', 'processing', 'error');
    
    // Stop animations
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
    // You could show a toast notification here
    console.error(message);
}

// Reset to idle state
function resetToIdle() {
    const button = document.getElementById('ai-voice-btn');
    if (button) {
        button.classList.remove('listening', 'speaking', 'processing', 'error');
    }
    stopAllAnimations();
}

// Start listening animation (pulsating waves)
function startListeningAnimation() {
    const container = document.getElementById('ai-voice-assistant');
    if (!container) return;
    
    container.classList.add('listening-animation');
}

// Stop listening animation
function stopListeningAnimation() {
    const container = document.getElementById('ai-voice-assistant');
    if (container) {
        container.classList.remove('listening-animation');
    }
}

// Start speaking animation (floating waves)
function startSpeakingAnimation() {
    const container = document.getElementById('ai-voice-assistant');
    if (!container) return;
    
    container.classList.add('speaking-animation');
}

// Stop speaking animation
function stopSpeakingAnimation() {
    const container = document.getElementById('ai-voice-assistant');
    if (container) {
        container.classList.remove('speaking-animation');
    }
}

// Stop all animations
function stopAllAnimations() {
    stopListeningAnimation();
    stopSpeakingAnimation();
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize speech recognition
    const speechSupported = initSpeechRecognition();
    
    if (!speechSupported) {
        const button = document.getElementById('ai-voice-btn');
        if (button) {
            button.style.opacity = '0.5';
            button.style.cursor = 'not-allowed';
            button.title = 'Voice recognition not supported in your browser';
        }
    }
    
    // Add click handler
    const button = document.getElementById('ai-voice-btn');
    if (button) {
        button.addEventListener('click', toggleAIAssistant);
    }
    
    // Close on outside click when active
    document.addEventListener('click', function(event) {
        const container = document.getElementById('ai-voice-assistant');
        if (isActive && container && !container.contains(event.target)) {
            deactivateAssistant();
        }
    });
});

