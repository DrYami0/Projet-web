// AI Mascot Assistant
class MascotAssistant {
    constructor() {
        this.apiUrl = 'http://localhost:3000/ai';
        this.chatWindow = null;
        this.messagesContainer = null;
        this.inputField = null;
        this.sendButton = null;
        this.isOpen = false;
        this.isTyping = false;
        this.init();
    }

    init() {
        this.createMascotUI();
        this.attachEventListeners();
    }

    createMascotUI() {
        const container = document.createElement('div');
        container.className = 'mascot-container';
        
        // Create owl SVG
        const owlSvg = `
            <svg width="80" height="80" viewBox="0 0 100 120" xmlns="http://www.w3.org/2000/svg">
                <!-- Owl Body -->
                <ellipse cx="50" cy="70" rx="35" ry="40" fill="#8B6F47"/>
                <!-- Owl Head -->
                <circle cx="50" cy="35" r="30" fill="#6B5437"/>
                <!-- Graduation Cap Base -->
                <ellipse cx="50" cy="25" rx="32" ry="8" fill="#1a1a1a"/>
                <!-- Cap Top -->
                <circle cx="50" cy="25" r="20" fill="#1a1a1a"/>
                <!-- Tassel String -->
                <line x1="70" y1="25" x2="75" y2="15" stroke="#FFD700" stroke-width="2"/>
                <!-- Tassel -->
                <circle cx="75" cy="15" r="3" fill="#FFD700"/>
                <!-- Eyes Background -->
                <circle cx="40" cy="35" r="12" fill="#FFF"/>
                <circle cx="60" cy="35" r="12" fill="#FFF"/>
                <!-- Eyes -->
                <circle cx="40" cy="35" r="8" fill="#FFB000"/>
                <circle cx="60" cy="35" r="8" fill="#FFB000"/>
                <!-- Pupils -->
                <circle cx="40" cy="35" r="4" fill="#000"/>
                <circle cx="60" cy="35" r="4" fill="#000"/>
                <!-- Beak -->
                <polygon points="50,40 45,48 55,48" fill="#FF8C00"/>
                <!-- Belly Pattern -->
                <ellipse cx="50" cy="75" rx="20" ry="25" fill="#A0826D"/>
                <circle cx="45" cy="70" r="3" fill="#6B5437"/>
                <circle cx="55" cy="70" r="3" fill="#6B5437"/>
                <circle cx="50" cy="77" r="3" fill="#6B5437"/>
                <circle cx="45" cy="83" r="3" fill="#6B5437"/>
                <circle cx="55" cy="83" r="3" fill="#6B5437"/>
                <!-- Wings -->
                <ellipse cx="20" cy="70" rx="12" ry="20" fill="#8B6F47" transform="rotate(-20 20 70)"/>
                <ellipse cx="80" cy="70" rx="12" ry="20" fill="#8B6F47" transform="rotate(20 80 70)"/>
                <!-- Feet -->
                <ellipse cx="42" cy="105" rx="8" ry="5" fill="#FF8C00"/>
                <ellipse cx="58" cy="105" rx="8" ry="5" fill="#FF8C00"/>
            </svg>
        `;
        
        container.innerHTML = `
            <div class="mascot-button" id="mascotButton">
                <div class="pulse-ring"></div>
                ${owlSvg}
            </div>
            <div class="chat-window" id="chatWindow">
                <div class="chat-header">
                    <div class="chat-header-info">
                        <div class="chat-avatar">
                            🦉
                        </div>
                        <div class="chat-header-text">
                            <h3>Assistant PerfRan</h3>
                            <p>En ligne</p>
                        </div>
                    </div>
                    <button class="close-chat" id="closeChat">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <div class="welcome-message">
                        🦉
                        <h4>Bonjour! 👋</h4>
                        <p>Je suis votre assistant virtuel PerfRan. Posez-moi vos questions sur l'apprentissage du français ou sur l'utilisation de la plateforme!</p>
                    </div>
                </div>
                <div class="typing-indicator" id="typingIndicator">
                    <div class="message-avatar">
                        🦉
                    </div>
                    <div class="typing-dots">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
                <div class="chat-input-container">
                    <div class="chat-input-wrapper">
                        <input 
                            type="text" 
                            class="chat-input" 
                            id="chatInput" 
                            placeholder="Posez votre question..."
                            maxlength="500"
                        >
                        <button class="send-button" id="sendButton">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(container);

        this.chatWindow = document.getElementById('chatWindow');
        this.messagesContainer = document.getElementById('chatMessages');
        this.inputField = document.getElementById('chatInput');
        this.sendButton = document.getElementById('sendButton');
    }

    attachEventListeners() {
        document.getElementById('mascotButton').addEventListener('click', () => this.toggleChat());
        document.getElementById('closeChat').addEventListener('click', () => this.toggleChat());
        this.sendButton.addEventListener('click', () => this.sendMessage());
        this.inputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        if (this.isOpen) {
            this.chatWindow.classList.add('active');
            this.inputField.focus();
        } else {
            this.chatWindow.classList.remove('active');
        }
    }

    async sendMessage() {
        const message = this.inputField.value.trim();
        if (!message || this.isTyping) return;

        // Add user message
        this.addMessage(message, 'user');
        this.inputField.value = '';
        this.inputField.focus();

        // Show typing indicator
        this.showTyping();

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();
            
            // Hide typing indicator
            this.hideTyping();

            if (data.reply) {
                this.addMessage(data.reply, 'bot');
            } else {
                throw new Error('Invalid response');
            }

        } catch (error) {
            console.error('Error:', error);
            this.hideTyping();
            this.addMessage(
                "Désolé, je ne peux pas répondre pour le moment. Assurez-vous que le serveur AI est démarré (npm start dans le dossier du projet).",
                'bot'
            );
        }
    }

    addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        
        const time = new Date().toLocaleTimeString('fr-FR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });

        messageDiv.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-${sender === 'user' ? 'user' : 'owl'}"></i>
            </div>
            <div class="message-content">
                <div class="message-bubble">${this.escapeHtml(text)}</div>
                <div class="message-time">${time}</div>
            </div>
        `;

        // Remove welcome message if exists
        const welcomeMsg = this.messagesContainer.querySelector('.welcome-message');
        if (welcomeMsg) {
            welcomeMsg.remove();
        }

        this.messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    showTyping() {
        this.isTyping = true;
        this.sendButton.disabled = true;
        document.getElementById('typingIndicator').classList.add('active');
        this.scrollToBottom();
    }

    hideTyping() {
        this.isTyping = false;
        this.sendButton.disabled = false;
        document.getElementById('typingIndicator').classList.remove('active');
    }

    scrollToBottom() {
        setTimeout(() => {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }, 100);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/\n/g, '<br>');
    }
}

// Initialize mascot when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new MascotAssistant();
    });
} else {
    new MascotAssistant();
}
