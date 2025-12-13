// Game Encouragement System - Only for game pages
(function(){
    // Only activate on game pages
    if(!window.location.pathname.includes('game')) return;
    
    const correctMessages = [
        "Excellente réponse! 🎉",
        "C'est juste! 🌟",
        "Parfait! 🚀",
        "Bravo! 👏",
        "Magnifique! ⭐",
        "Vous maîtrisez! 💪",
        "Superbe! 🔥",
    ];
    
    const wrongMessages = [
        "Ce n'est pas grave, vous ferez mieux la prochaine fois! 💪",
        "N'abandonnez pas! Continuez! 🎯",
        "C'est une bonne tentative! Réessayez! 📚",
        "Vous apprenez à chaque erreur! 🌱",
        "Ne renoncez pas! Vous progressez! 📈",
        "Chaque erreur vous rapproche de la réussite! ⚡",
        "Gardez votre motivation! Vous pouvez le faire! 🏆",
    ];
    
    // Expose global function for game page to call
    window.showGameEncouragement = function(isCorrect){
        const messages = isCorrect ? correctMessages : wrongMessages;
        const message = messages[Math.floor(Math.random() * messages.length)];
        
        createEncouragementBubble(message, isCorrect);
    };
    
    function createEncouragementBubble(message, isCorrect){
        const container = document.getElementById('pf-game-encouragement-container') || createContainer();
        
        const bubble = document.createElement('div');
        bubble.style.cssText = `
            background: ${isCorrect ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #f97316 0%, #ea580c 100%)'};
            color: #fff;
            padding: 14px 18px;
            border-radius: 16px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            line-height: 1.5;
            word-wrap: break-word;
            max-width: 100%;
            box-sizing: border-box;
            box-shadow: ${isCorrect ? '0 4px 15px rgba(16, 185, 129, 0.4)' : '0 4px 15px rgba(249, 115, 22, 0.4)'};
            animation: game-pop 0.4s ease-out;
        `;
        
        bubble.innerHTML = `<span>${message}</span>`;
        container.appendChild(bubble);
        
        // Keep last 3 messages max
        const bubbles = container.querySelectorAll('div > div');
        if(bubbles.length > 3){
            bubbles[0].style.animation = 'game-fade 0.3s ease-out forwards';
            setTimeout(() => bubbles[0].remove(), 300);
        }
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            bubble.style.animation = 'game-fade 0.3s ease-out forwards';
            setTimeout(() => bubble.remove(), 300);
        }, 3000);
    }
    
    function createContainer(){
        const container = document.createElement('div');
        container.id = 'pf-game-encouragement-container';
        container.style.cssText = `
            position: fixed;
            right: 120px;
            bottom: 36px;
            z-index: 2147483646;
            max-width: 300px;
            pointer-events: none;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        `;
        document.body.appendChild(container);
        return container;
    }
    
    // Add animations to stylesheet
    if(!document.getElementById('pf-game-encouragement-styles')){
        const style = document.createElement('style');
        style.id = 'pf-game-encouragement-styles';
        style.innerHTML = `
            @keyframes game-pop {
                0% {
                    opacity: 0;
                    transform: scale(0.7) translateY(20px);
                }
                100% {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }
            
            @keyframes game-fade {
                0% {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
                100% {
                    opacity: 0;
                    transform: scale(0.8) translateY(10px);
                }
            }
        `;
        document.head.appendChild(style);
    }
})();
