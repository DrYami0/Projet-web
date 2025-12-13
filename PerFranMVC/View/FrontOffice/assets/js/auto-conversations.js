// Auto-Conversations System for Mascot
(function(){
    // Array of motivational and encouraging messages
    const autoMessages = [
        "Vous faites un excellent travail! 🌟",
        "Continuez comme ça! 💪",
        "Bravo pour vos efforts! 🎉",
        "Vous progressez vraiment bien! 📈",
        "C'est impressionnant ce que vous faites! 🚀",
        "Vous êtes sur la bonne voie! ✨",
        "Magnifique travail! 👏",
        "Gardez cette motivation! 🔥",
        "Vous êtes fantastique! ⭐",
        "Chaque jour vous vous améliorez! 📚",
        "Vos efforts portent leurs fruits! 🌱",
        "Vous êtes une inspiration! 💫",
        "C'est super ce que vous apprenez! 🎓",
        "Vous avez du potentiel énorme! 💎",
        "Bravo d'être persévérant! 🏆",
    ];

    const gameEncouragements = [
        "Allez-y, vous pouvez gagner! 🎮",
        "Concentrez-vous, vous êtes proche! 🎯",
        "Excellent jeu! Continuez! 🎪",
        "Vous maîtrisez vraiment bien! 🎨",
        "Encore un peu et c'est gagné! ⚡",
        "Votre technique s'améliore! 🥇",
        "Vous jouez comme un pro! 🏅",
        "Quelle performance! 🌟",
        "C'est un excellent score! 💯",
        "Vous êtes un champion! 👑",
    ];

    function createAutoConversationBox(){
        // Create container for auto-conversations
        const container = document.createElement('div');
        container.id = 'pf-auto-convo-container';
        container.style.cssText = `
            position: fixed;
            right: 120px;
            bottom: 36px;
            z-index: 2147483647;
            max-width: 320px;
            min-width: 200px;
            pointer-events: none;
        `;
        document.body.appendChild(container);
        
        return container;
    }

    function showAutoMessage(message, isGame = false){
        const container = document.getElementById('pf-auto-convo-container') || createAutoConversationBox();
        
        // Create message bubble
        const bubble = document.createElement('div');
        bubble.style.cssText = `
            background: linear-gradient(135deg, #00d4ff 0%, #6c63ff 100%);
            color: #fff;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
            line-height: 1.4;
            word-wrap: break-word;
            white-space: normal;
            max-width: 100%;
            box-sizing: border-box;
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
            animation: pf-auto-pop 0.3s ease-out, pf-colorwave 2.5s linear infinite;
            animation-delay: 0s, 0.3s;
        `;
        
        // Add text with emoji support
        bubble.innerHTML = `<span style="word-break: break-word;">${message}</span>`;
        
        container.appendChild(bubble);
        
        // Remove old messages if there are too many
        const bubbles = container.querySelectorAll('div > div');
        if(bubbles.length > 5){
            bubbles[0].style.animation = 'pf-auto-fade 0.3s ease-out forwards';
            setTimeout(() => bubbles[0].remove(), 300);
        }
        
        // Auto-remove after 4 seconds
        setTimeout(() => {
            bubble.style.animation = 'pf-auto-fade 0.3s ease-out forwards';
            setTimeout(() => bubble.remove(), 300);
        }, 4000);
    }

    function startAutoConversations(){
        // Check if we're on a game page
        const isGamePage = window.location.pathname.includes('game1') || 
                          window.location.pathname.includes('DisplayGames') ||
                          window.location.pathname.includes('EditGame');
        
        const messageList = isGamePage ? gameEncouragements : autoMessages;
        
        // Show initial message after 2 seconds
        setTimeout(() => {
            const randomMsg = messageList[Math.floor(Math.random() * messageList.length)];
            showAutoMessage(randomMsg, isGamePage);
        }, 2000);
        
        // Show random messages every 8-15 seconds
        setInterval(() => {
            // Random chance (50%) to show a message
            if(Math.random() > 0.5){
                const randomMsg = messageList[Math.floor(Math.random() * messageList.length)];
                showAutoMessage(randomMsg, isGamePage);
            }
        }, 8000 + Math.random() * 7000);
    }

    // Add animations to stylesheet if not already present
    if(!document.getElementById('pf-auto-convo-styles')){
        const style = document.createElement('style');
        style.id = 'pf-auto-convo-styles';
        style.innerHTML = `
            @keyframes pf-auto-pop {
                0% {
                    opacity: 0;
                    transform: scale(0.6) translateY(20px);
                }
                100% {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }
            
            @keyframes pf-auto-fade {
                0% {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
                100% {
                    opacity: 0;
                    transform: scale(0.8) translateY(10px);
                }
            }
            
            @keyframes pf-colorwave {
                0% {
                    border-color: #00d4ff;
                    box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
                }
                25% {
                    border-color: #6c63ff;
                    box-shadow: 0 4px 12px rgba(108, 99, 255, 0.3);
                }
                50% {
                    border-color: #00ffb8;
                    box-shadow: 0 4px 12px rgba(0, 255, 184, 0.3);
                }
                75% {
                    border-color: #6c63ff;
                    box-shadow: 0 4px 12px rgba(108, 99, 255, 0.3);
                }
                100% {
                    border-color: #00d4ff;
                    box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Start showing auto-conversations when DOM is ready
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', startAutoConversations);
    } else {
        startAutoConversations();
    }
})();
