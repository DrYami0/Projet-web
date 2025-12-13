// Global Theme Toggle Script
(function(){
    const saved = localStorage.getItem('pref-theme') || 'dark';
    const html = document.documentElement;
    const htmlBody = document.body;
    
    function setTheme(t){
        // Set on both html and body for compatibility
        html.setAttribute('data-theme', t);
        html.setAttribute('data-bs-theme', t);
        if(htmlBody) htmlBody.setAttribute('data-theme', t);
        
        // Force CSS variable update
        if(t === 'light'){
            html.style.setProperty('--bg', '#f1f5f9');
            html.style.setProperty('--bg-secondary', '#e2e8f0');
            html.style.setProperty('--card', '#ffffff');
            html.style.setProperty('--text', '#0f172a');
            html.style.setProperty('--text-secondary', '#334155');
            html.style.setProperty('--input-bg', '#ffffff');
        } else {
            html.style.setProperty('--bg', '#0f172a');
            html.style.setProperty('--bg-secondary', '#1a2332');
            html.style.setProperty('--card', '#1e293b');
            html.style.setProperty('--text', '#e2e8f0');
            html.style.setProperty('--text-secondary', '#cbd5e1');
            html.style.setProperty('--input-bg', '#334155');
        }
        
        const btn = document.getElementById('themeToggle');
        if(btn){
            btn.innerHTML = t === 'dark' 
                ? '<i class="fas fa-sun"></i><span>Thème clair</span>' 
                : '<i class="fas fa-moon"></i><span>Thème sombre</span>';
        }
    }
    
    // Apply saved theme immediately (before DOMContentLoaded)
    setTheme(saved);
    
    // Setup click handler
    function setupThemeToggle(){
        const btn = document.getElementById('themeToggle');
        if(!btn) return;
        
        btn.addEventListener('click', function(){
            const current = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            setTheme(current);
            localStorage.setItem('pref-theme', current);
        });
    }
    
    // Handle both early and late DOM content
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', setupThemeToggle);
    } else {
        setupThemeToggle();
    }
})();
