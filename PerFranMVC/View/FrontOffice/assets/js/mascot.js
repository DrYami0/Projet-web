/*
  Combined, robust front-end mascot script for PerfRan.
  Injects a visible owl button and a simple chat panel on every page.
*/
(function(){
  'use strict';
  try {
    if (document.getElementById('pf-owl-fallback')) return;

    // Create styles
    var style = document.createElement('style');
    style.id = 'pf-owl-styles';
    style.textContent = '\n' +
    '.pf-owl-root { position: fixed; right: 24px; bottom: 24px; z-index: 2147483647; font-family: Inter, Arial, sans-serif; pointer-events:auto; transform:none !important; }\n' +
    '.pf-owl-btn { width:56px; height:56px; border-radius:12px; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; background:transparent; padding:0; position:fixed; right:24px; bottom:24px; box-shadow:0 10px 28px rgba(0,0,0,0.55); }\n' +
    '.pf-owl-chat { position:fixed; right: 24px; bottom: 96px; width:360px; max-width:calc(100vw - 40px); height:420px; background:#0f1b2d; color:#e8f4f8; border-radius:12px; box-shadow:0 20px 50px rgba(0,0,0,.6); overflow:hidden; display:none; flex-direction:column; margin-bottom:0; }\n' +
    '.pf-owl-header { padding:12px 14px; background:linear-gradient(90deg,#00d4ff,#0099cc); font-weight:700; }\n' +
    '.pf-owl-board { flex:1; padding:12px; overflow-y:auto; }\n' +
    '.pf-owl-footer { padding:10px; border-top:1px solid rgba(255,255,255,0.04); display:flex; gap:8px; }\n' +
    '.pf-owl-input { flex:1; padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.06); background:#0b1624; color:#e8f4f8; }\n' +
    '.pf-owl-send { width:44px; height:36px; border-radius:8px; border:none; background:linear-gradient(180deg,#00d4ff,#6c63ff); color:#fff; cursor:pointer; }\n' +
    '.pf-owl-msg { margin-bottom:10px; display:flex; align-items:flex-end; }\n' +
    '.pf-owl-msg .bubble { padding:8px 12px; border-radius:12px; max-width:80%; word-break:break-word; }\n' +
    '.pf-owl-msg.bot { justify-content:flex-start } .pf-owl-msg.bot .bubble { background:#1f2b3a; color:#fff }\n' +
    '.pf-owl-msg.user { justify-content:flex-end } .pf-owl-msg.user .bubble { background:#6c63ff; color:#fff }\n' +
    '';
    document.head.appendChild(style);

    // Scoped illustration CSS for the owl button (kept separate for clarity)
    var owlStyle = document.createElement('style');
    owlStyle.id = 'pf-owl-illustration-styles';
    owlStyle.textContent = '\n' +
    '.pf-owl-btn{ background:transparent; border:none; padding:0; width:56px; height:56px; display:flex; align-items:center; justify-content:center; cursor:pointer; }\n' +
    '.pf-owl-btn:focus{ outline:2px solid rgba(0,150,255,0.25); border-radius:12px }\n' +
    '.pf-owl-illustration{ width:56px; height:56px; transform-origin: center bottom; transform: none; transition: transform .16s ease; pointer-events:none }\n' +
    '.pf-owl-btn:hover .pf-owl-illustration, .pf-owl-btn.open .pf-owl-illustration{ transform: scale(1.12); pointer-events:auto }\n' +
    '.pf-owl-illustration .owl{ position:relative; width:56px; height:56px }\n' +
    '.pf-owl-illustration .body-3, .pf-owl-illustration .body-2, .pf-owl-illustration .body-1, .pf-owl-illustration .head, .pf-owl-illustration .ear-l, .pf-owl-illustration .ear-r, .pf-owl-illustration .eye-l, .pf-owl-illustration .eye-r, .pf-owl-illustration .nose, .pf-owl-illustration .paw-l, .pf-owl-illustration .paw-r{ position:absolute }\n' +
    '.pf-owl-illustration .ear-l{ transform: rotate(-36deg); left:10px; top:6px; border-bottom:30px solid var(--pf-owl-medium,#9d8775); border-left:8px solid transparent; border-right:8px solid transparent }\n' +
    '.pf-owl-illustration .ear-r{ transform: rotate(36deg); right:10px; top:6px; border-bottom:30px solid var(--pf-owl-medium,#9d8775); border-left:8px solid transparent; border-right:8px solid transparent }\n' +
    '.pf-owl-illustration .head{ width:140px; height:125px; border-radius:50%; top:12px; left:20px; background:var(--pf-owl-medium,#9d8775) }\n' +
    '.pf-owl-illustration .eye-l, .pf-owl-illustration .eye-r{ width:10px; height:10px; background:#3b2314; border-radius:50%; top:18px }\n' +
    '.pf-owl-illustration .eye-l{ left:42px; box-shadow:0 0 0 4px #603813,0 0 0 10px var(--pf-owl-dark,#3b2314),0 0 0 14px #f4f4f4 }\n' +
    '.pf-owl-illustration .eye-r{ right:42px; box-shadow:0 0 0 4px #603813,0 0 0 10px var(--pf-owl-dark,#3b2314),0 0 0 14px #f4f4f4 }\n' +
    '.pf-owl-illustration .nose{ width:6px; height:10px; left:26px; top:28px; border-radius:0 0 4px 4px; background:var(--pf-owl-nose,#f37920) }\n' +
    '.pf-owl-illustration .body-1{ width:22px; height:56px; border-radius:50%; background:var(--pf-owl-light,#a69586); left:16px; top:18px }\n' +
    '.pf-owl-illustration .body-2{ width:32px; height:58px; border-radius:50%; left:12px; top:16px; background:var(--pf-owl-medium,#9d8775) }\n' +
    '.pf-owl-illustration .body-3{ width:44px; height:60px; border-radius:50%; left:6px; top:14px; background:var(--pf-owl-dark,#3b2314) }\n' +
    '.pf-owl-illustration .paw-l, .pf-owl-illustration .paw-r{ width:6px; height:12px; bottom:-6px; border-radius:6px; background:var(--pf-owl-nose,#f37920) }\n' +
    '.pf-owl-illustration .paw-l{ left:16px } .pf-owl-illustration .paw-r{ right:16px }\n' +
    '.pf-owl-model-wrap{ position:relative; min-width:140px; margin-right:8px }\n' +
    '.pf-owl-model-display{ display:inline-block; background:#0b1624; color:#e8f4f8; border-radius:8px; border:1px solid rgba(255,255,255,0.06); padding:6px 10px; cursor:pointer; font-size:14px; }\n' +
    '.pf-owl-model-menu{ position:absolute; right:0; top:calc(100% + 8px); min-width:160px; background:#0b1624; border:1px solid rgba(255,255,255,0.06); border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.6); z-index:9999991; }\n' +
    '.pf-owl-model-item{ padding:8px 12px; cursor:pointer; color:#e8f4f8; border-bottom:1px solid rgba(255,255,255,0.02); font-size:14px; }\n' +
    '.pf-owl-model-item:hover{ background:rgba(255,255,255,0.02) }\n' +
    '@keyframes pf-owl-bob{ 0%{ transform: translateY(0) } 50%{ transform: translateY(-6px) } 100%{ transform: translateY(0) } }\n' +
    '.pf-owl-root{ animation: pf-owl-bob 3.2s ease-in-out infinite; }\n' +
    '.pf-owl-model { width:132px; background:transparent; color:#e8f4f8; border-radius:8px; border:1px solid rgba(255,255,255,0.06); padding:6px 8px; margin-right:8px; }\n' +
    ''; 
    document.head.appendChild(owlStyle);
    // API Endpoint: allow override by global, defaults to relative /ai for proxied deployments
    var apiUrl = window.PERFRAN_AI_API_URL || ((window.location.origin || '') + '/ai');

    // Build DOM
    var root = document.createElement('div'); root.id = 'pf-owl-fallback'; root.className = 'pf-owl-root';

    var chat = document.createElement('div'); chat.className = 'pf-owl-chat'; chat.id = 'pf-owl-chat';
    var header = document.createElement('div'); header.className = 'pf-owl-header'; header.textContent = 'PerfRan Assistant';
    var board = document.createElement('div'); board.className = 'pf-owl-board'; board.id = 'pf-owl-board';
    var footer = document.createElement('div'); footer.className = 'pf-owl-footer';
    // Model selector (Gemini models) – create a custom dropdown that's fully styleable
    var modelSel = document.createElement('div'); modelSel.className = 'pf-owl-model-wrap'; modelSel.id = 'pf-owl-model-select-wrap';
    var modelDisplay = document.createElement('button'); modelDisplay.className = 'pf-owl-model-display'; modelDisplay.type = 'button'; modelDisplay.setAttribute('aria-haspopup','listbox');
    var modelMenu = document.createElement('div'); modelMenu.className = 'pf-owl-model-menu'; modelMenu.setAttribute('role','listbox'); modelMenu.style.display = 'none';
    // Models array (kept in order)
    var models = [
      {value:'gemini-2.5-flash', label:'Gemini 2.5 Flash'},
      {value:'gemini-2.5-pro', label:'Gemini 2.5 Pro'},
      {value:'gemini-2.0-flash', label:'Gemini 2.0 Flash'},
      {value:'gemini-flash-latest', label:'Gemini Flash Latest'},
      {value:'gemini-2.5-flash-lite', label:'Gemini 2.5 Flash Lite'},
      {value:'gemini-3-pro-preview', label:'Gemini 3 Pro (Preview)'}
    ];
    var selectedModelValue = models[0].value;
    modelDisplay.textContent = models[0].label + ' ▾';
    models.forEach(function(m){ var item = document.createElement('div'); item.className = 'pf-owl-model-item'; item.setAttribute('role','option'); item.dataset.value = m.value; item.textContent = m.label; modelMenu.appendChild(item); });
    modelSel.appendChild(modelDisplay); modelSel.appendChild(modelMenu);
    var input = document.createElement('input'); input.className = 'pf-owl-input'; input.id = 'pf-owl-input'; input.placeholder = 'Écris un message…';
    var send = document.createElement('button'); send.className = 'pf-owl-send'; send.id = 'pf-owl-send'; send.type = 'button'; send.textContent = '✈️';

    footer.appendChild(modelSel); footer.appendChild(input); footer.appendChild(send);
    chat.appendChild(header); chat.appendChild(board); chat.appendChild(footer);

    var btn = document.createElement('button'); btn.className = 'pf-owl-btn'; btn.id = 'pf-owl-btn'; btn.title = 'PerfRan Assistant'; btn.type = 'button'; btn.setAttribute('aria-label','PerfRan Assistant');
    btn.innerHTML = '\n' +
      '<div class="pf-owl-illustration">' +
        '<div class="owl">' +
          '<div class="body-3"></div>' +
          '<div class="body-2"></div>' +
          '<div class="body-1"></div>' +
          '<div class="head">' +
            '<div class="nose"></div>' +
            '<div class="ear-l"></div>' +
            '<div class="ear-r"></div>' +
            '<div class="eye-l"></div>' +
            '<div class="eye-r"></div>' +
          '</div>' +
          '<div class="paw-l"></div>' +
          '<div class="paw-r"></div>' +
        '</div>' +
      '</div>';

    // Inline safety: ensure correct visible size/style even if site CSS interferes
    btn.style.width = '56px'; btn.style.height = '56px'; btn.style.background = 'transparent'; btn.style.border = '0'; btn.style.padding = '0'; btn.style.display = 'flex'; btn.style.alignItems = 'center'; btn.style.justifyContent = 'center'; btn.style.cursor = 'pointer';

    // Add a high-specificity override style so the site doesn't override colors/shape
    var forceStyle = document.createElement('style'); forceStyle.id = 'pf-owl-force-styles';
    forceStyle.textContent = '#pf-owl-fallback.pf-owl-root .pf-owl-btn{ width:56px !important; height:56px !important; background:transparent !important; border:none !important; padding:0 !important; box-shadow:none !important; }\n' +
                 '#pf-owl-fallback.pf-owl-root .pf-owl-illustration{ width:56px !important; height:56px !important; transform:none !important; pointer-events:none !important; }\n' +
                 '#pf-owl-fallback.pf-owl-root .pf-owl-illustration .owl{ width:56px !important; height:56px !important; }\n' +
                 '#pf-owl-fallback.pf-owl-root .pf-owl-illustration .eye-l, #pf-owl-fallback.pf-owl-root .pf-owl-illustration .eye-r{ width:10px!important; height:10px!important }\n' +
                 '#pf-owl-fallback.pf-owl-root .pf-owl-illustration .nose{ width:6px!important; height:10px!important }\n' +
                 '#pf-owl-fallback.pf-owl-root .pf-owl-illustration .body-1{ width:22px!important; height:56px!important }\n' +
                 '#pf-owl-fallback.pf-owl-root .pf-owl-illustration .body-2{ width:32px!important; height:58px!important }\n' +
                 '#pf-owl-fallback.pf-owl-root .pf-owl-illustration .body-3{ width:44px!important; height:60px!important }\n' +
                 '#pf-owl-fallback.pf-owl-root .pf-owl-illustration .paw-l, #pf-owl-fallback.pf-owl-root .pf-owl-illustration .paw-r{ width:6px!important; height:12px!important }';
    if (!document.getElementById('pf-owl-force-styles')) document.head.appendChild(forceStyle);

    // Health check: ping the AI endpoint's /health route (resilient) so we don't post when it's offline
    (function(){
      var base = apiUrl.replace(/\/(?:ai|ai\/.*?)$/,'');
      var healthUrl = base + '/health';
      var t = function(){
        fetch(healthUrl).then(function(res){ if(res.ok){ serverHealthy = true; console.log('[mascot] AI server healthy'); } else { serverHealthy = false; console.warn('[mascot] AI health check failed', res.status); } }).catch(function(e){ serverHealthy = false; console.warn('[mascot] AI health check error', e); });
      };
      t();
      setInterval(t, 15000);
    })();

    root.appendChild(chat); root.appendChild(btn);
    // Install model menu interactivity
    (function(){
      var display = document.querySelector('#pf-owl-model-select-wrap .pf-owl-model-display');
      var menu = document.querySelector('#pf-owl-model-select-wrap .pf-owl-model-menu');
      var items = menu && Array.from(menu.children) || [];
      if(!display || !menu) return;
      display.addEventListener('click', function(e){ e.stopPropagation(); menu.style.display = menu.style.display === 'none' || menu.style.display === '' ? 'block':'none'; });
      items.forEach(function(it){ it.addEventListener('click', function(e){ e.stopPropagation(); var val = it.dataset.value; selectedModelValue = val; display.textContent = it.textContent + ' ▾'; menu.style.display = 'none'; }); });
      document.addEventListener('click', function(){ menu.style.display = 'none'; });
    })();
    document.body.appendChild(root);

    // Helpers
    function appendMsg(text, who){
      var m = document.createElement('div'); m.className = 'pf-owl-msg ' + (who==='user'?'user':'bot');
      var b = document.createElement('div'); b.className = 'bubble'; b.textContent = text;
      m.appendChild(b); board.appendChild(m); board.scrollTop = board.scrollHeight;
    }

    // Auto greet based on page
    var path = window.location.pathname || '';
    var greet = 'Salut! Je suis là pour t\'aider! 🦉';
    if (path.indexOf('index')!==-1 || path==='/') greet = "Bienvenue sur PerFran! 🎓 Clique sur moi pour découvrir comment améliorer ton français!";
    if (path.indexOf('dashboard')!==-1) greet = 'Bravo! Tu fais des progrès incroyables! 📊';
    if (path.indexOf('game1')!==-1) greet = 'Prêt à jouer? 🎮 Je suis là si tu as besoin d\'aide!';

    appendMsg(greet, 'bot');

    // Toggle
    var visible = false;
    function toggle(){ visible = !visible; chat.style.display = visible? 'flex' : 'none'; chat.setAttribute('aria-hidden', visible? 'false' : 'true'); btn.classList.toggle('open', visible); if(visible) input.focus(); }
    btn.addEventListener('click', function(e){ e.stopPropagation(); toggle(); });

    // Send behavior: try AI endpoint, otherwise fallback reply
    var isTyping = false;
    var serverHealthy = false;
    function showTyping(){ isTyping = true; var t = document.createElement('div'); t.id='pf-owl-typing'; t.className='pf-owl-msg bot'; t.innerHTML='<div class="bubble">PerfRan écrit...</div>'; board.appendChild(t); board.scrollTop = board.scrollHeight; }
    function hideTyping(){ isTyping = false; var t = document.getElementById('pf-owl-typing'); if(t) t.remove(); }

    function sendMessage(){
      var v = input.value && input.value.trim(); if(!v || isTyping) return; input.value=''; appendMsg(v,'user'); showTyping();
      if (!serverHealthy){ hideTyping(); appendMsg("(Version fallback) Désolé, pas de connexion au serveur d'IA.", 'bot'); return; }
      var selectedModel = typeof selectedModelValue !== 'undefined' ? selectedModelValue : 'gemini-2.5-flash';
      // Try POST to backend; if fail, show fallback
      console.log('[mascot] sending to', apiUrl, 'model:', selectedModel);
      fetch(apiUrl, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({message:v, model:selectedModel}) }).then(function(res){
        return res.json().then(function(data){ return {status: res.status, data: data}; });
      }).then(function(obj){ hideTyping(); var data = obj.data; if(obj.status >= 400){ console.warn('Mascot AI server responded with', obj.status, data); appendMsg("(Version fallback) Le serveur a répondu avec erreur: " + (data && data.error? data.error: obj.status), 'bot'); return; } if(data && (data.reply||data.answer)) appendMsg(data.reply||data.answer,'bot'); else appendMsg("(Version fallback) Je n\'ai pas de réponse intelligente pour le moment.",'bot'); }).catch(function(err){ hideTyping(); console.warn('Mascot AI fetch failed', err); appendMsg("(Version fallback) Désolé, pas de connexion au serveur d'IA.", 'bot'); });
    }

    send.addEventListener('click', sendMessage);
    input.addEventListener('keypress', function(e){ if(e.key==='Enter') sendMessage(); });

    // Accessibility: close chat on outside click
    document.addEventListener('click', function(e){ if(!root.contains(e.target) && visible){ toggle(); } });

    // Keep element on top if other scripts change z-index
    setInterval(function(){ var el = document.getElementById('pf-owl-fallback'); if(el) el.style.zIndex = '2147483647'; }, 3000);

    console.log('[owl-mascot] mascot injected');
  } catch(e){ try{ console.error('[owl-mascot] init error', e); }catch(_){} }
})();
