/*
  Combined, robust front-end mascot script for PerfRan.
  Injects a visible owl button and a simple chat panel on every page.
  NOTE: Only CSS and model-selector UI behavior were adjusted so the owl is visible,
  positioned in the corner, and the model selector behaves like a modern dropdown.
  All API, chatbox, and logic remain intact.
*/
(function(){
  'use strict';
  try {
    // Early return if the mascot is already loaded — prevents duplicate injections.
    if (window.__perfRanMascotLoaded || document.getElementById('pf-owl-fallback')) { console.warn('[mascot.js] mascot already loaded'); return; }
    // Mark loaded so any future includes won't initialize the UI again
    window.__perfRanMascotLoaded = true;

    // Core styles have been externalized to `PerFranMVC/View/FrontOffice/assets/css/mascot.css`.
    // The script no longer injects large style blocks at runtime to improve load performance.

    // Illustration and model menu styles moved to `PerFranMVC/View/FrontOffice/assets/css/mascot.css`.
    // The script no longer injects these style blocks at runtime.

    // Any remaining template-specific illustration CSS is in `PerFranMVC/View/FrontOffice/assets/css/mascot.css`.

    // API Endpoint: allow override by global; otherwise probe candidates.
    // The .env sets PORT=3001 (Node AI server) - if running locally we try that port first.
    var DEFAULT_AI_PORT = '3001';
    var candidates = [];
    if (window.PERFRAN_AI_API_URL) candidates.push(window.PERFRAN_AI_API_URL);
    // If site is host local, try the Node server port from .env
    try { if ((window.location.hostname === 'localhost') || (window.location.hostname === '127.0.0.1')) candidates.push(window.location.protocol + '//' + window.location.hostname + ':' + DEFAULT_AI_PORT + '/ai'); } catch(e){ /* ignore */ }
    // fallback to same origin /ai for proxied setups
    candidates.push((window.location.origin || '') + '/ai');
    // start with fallback; we will probe candidates below
    var apiUrl = candidates[candidates.length - 1];

    // Build DOM
    var root = document.createElement('div'); root.id = 'pf-owl-fallback'; root.className = 'pf-owl-root';

    var chat = document.createElement('div'); chat.className = 'pf-owl-chat'; chat.id = 'pf-owl-chat';
    var header = document.createElement('div'); header.className = 'pf-owl-header'; header.textContent = 'PerfRan Assistant';
    var board = document.createElement('div'); board.className = 'pf-owl-board'; board.id = 'pf-owl-board';
    var footer = document.createElement('div'); footer.className = 'pf-owl-footer';
    // Model selector (Gemini models) – create a custom dropdown that's fully styleable
    // simple HTML escaper for labels
    function escapeHtml(s){ return String(s).replace(/[&<>\"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#39;'}[c]; }); }

    // Model selector (integrated from user template)
    var models = [
      {value:'gemini-2.5-flash', label:'Gemini 2.5 Flash'},
      {value:'gemini-2.5-pro', label:'Gemini 2.5 Pro'},
      {value:'gemini-2.0-flash', label:'Gemini 2.0 Flash'},
      {value:'gemini-flash-latest', label:'Gemini Flash Latest'},
      {value:'gemini-2.5-flash-lite', label:'Gemini 2.5 Flash Lite'},
      {value:'gemini-3-pro-preview', label:'Gemini 3 Pro (Preview)'}
    ];
    var modelDescriptions = {
      'gemini-2.5-flash': 'Génération rapide et économique pour des réponses courtes et dynamiques.',
      'gemini-2.5-pro': 'Modèle équilibré : bon raisonnement et cohérence pour les tâches complexes.',
      'gemini-2.0-flash': 'Modèle rapide historique avec latence réduite pour interactions instantanées.',
      'gemini-flash-latest': 'Dernière génération Flash, optimisée pour la conversation et la réactivité.',
      'gemini-2.5-flash-lite': 'Version allégée du Flash, adaptée aux environnements contraints.',
      'gemini-3-pro-preview': 'Modèle haute capacité (preview) pour créativité et raisonnement avancé.'
    };
    var selectedModelValue = models[0].value;
    var deepThinking = false;
    var deepThinkingEnabled = false;
    var searchModeEnabled = false; // when true, request web search + citations from backend

    var modelSel = document.createElement('div'); modelSel.className = 'pf-owl-model-wrap'; modelSel.id = 'pf-owl-model-select-wrap';
    var modelDisplay = document.createElement('button');
    modelDisplay.type = 'button';
    modelDisplay.id = 'model-select-trigger';
    modelDisplay.className = 'pf-owl-model-display inline-flex items-center justify-center gap-2 whitespace-nowrap font-medium cursor-pointer rounded-full';
    modelDisplay.setAttribute('aria-label', 'Model select');
    modelDisplay.setAttribute('aria-haspopup', 'listbox');
    modelDisplay.setAttribute('aria-expanded', 'false');
    // modelDisplay: keep only the label + chevron; lamp icon moved into the Deep Thinking toggle
    modelDisplay.innerHTML = '<div class="pf-model-label-wrap"><div class="flex flex-col items-start"><span class="pf-model-label">' + escapeHtml(models[0].label) + '</span></div></div>' +
      '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="stroke-[2] size-4 text-primary transition-transform rotate-180"><path d="M6 9L12 15L18 9" stroke="currentColor" stroke-linecap="square"></path></svg>';
    var modelMenu = document.createElement('div'); modelMenu.className = 'pf-owl-model-menu'; modelMenu.setAttribute('role','listbox'); modelMenu.id = 'pf-owl-model-menu';

    function buildModelMenu(){
      modelMenu.innerHTML = '';
      // Make menu clickable without bubbling to document (prevents accidental close)
      modelMenu.addEventListener('click', function(ev){ ev.stopPropagation(); });

      // Build static model list (no search/filter input — removed by request)
      models.forEach(function(m){
        var item = document.createElement('div');
        item.className = 'pf-owl-model-item' + (m.value===selectedModelValue ? ' selected' : '');
        item.dataset.value = m.value;
        // Accessibility: expose as option and make focusable
        item.setAttribute('role','option');
        item.tabIndex = 0;
        var desc = modelDescriptions[m.value] || '';
        item.innerHTML = '<div class="pf-model-name">' + escapeHtml(m.label) + '</div>' +
                         (desc? '<div class="pf-model-desc">' + escapeHtml(desc) + '</div>' : '');
        item.addEventListener('click', function(ev){ ev.stopPropagation(); selectedModelValue = this.dataset.value; updateSelectedModel(); closeModelMenu(); });
        // Keyboard support: Enter/Space to select
        item.addEventListener('keydown', function(ev){ if(ev.key === 'Enter' || ev.key === ' '){ ev.preventDefault(); this.click(); } });
        modelMenu.appendChild(item);
      });

      var divider = document.createElement('div');
      divider.style.borderTop = '1px solid rgba(255,255,255,0.06)';
      divider.style.margin = '4px 0';
      modelMenu.appendChild(divider);

      // Search mode toggle: ask the backend to perform web search + include citations
      var searchToggleWrap = document.createElement('div');
      searchToggleWrap.className = 'pf-owl-model-item pf-model-deep-toggle';
      var searchCheckbox = document.createElement('input');
      searchCheckbox.type = 'checkbox';
      searchCheckbox.id = 'pf-search-mode';
      searchCheckbox.className = 'pf-model-deep-toggle__input';
      // Accessibility: expose as switch
      searchCheckbox.setAttribute('role','switch');
      try{ searchCheckbox.setAttribute('aria-checked', searchCheckbox.checked ? 'true' : 'false'); }catch(_){ }
      var searchLabel = document.createElement('label');
      searchLabel.setAttribute('for','pf-search-mode');
      searchLabel.className = 'pf-model-deep-toggle--label';
      searchLabel.innerHTML = '<span class="pf-model-deep-toggle__text">Mode Recherche</span>';
      searchToggleWrap.appendChild(searchCheckbox);
      searchToggleWrap.appendChild(searchLabel);
      // prevent the menu from closing when interacting with toggle
      searchCheckbox.addEventListener('click', function(ev){ ev.stopPropagation(); });
      searchLabel.addEventListener('click', function(ev){ ev.stopPropagation(); });
      // initialize from localStorage
      try{ var storedSearch = localStorage.getItem('pf-search-mode'); if(storedSearch !== null) searchCheckbox.checked = (storedSearch === '1'); }catch(_){}
      searchModeEnabled = !!searchCheckbox.checked;
      searchCheckbox.addEventListener('change', function(){ searchModeEnabled = !!this.checked; try{ localStorage.setItem('pf-search-mode', this.checked ? '1' : '0'); }catch(_){} try{ this.setAttribute('aria-checked', this.checked ? 'true' : 'false'); }catch(_){ } console.log('[mascot] Search Mode:', searchModeEnabled); });
      modelMenu.appendChild(searchToggleWrap);

      var toggleWrap = document.createElement('div');
      toggleWrap.className = 'pf-owl-model-item pf-model-deep-toggle';
      var checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.id = 'pf-deep-thinking';
      checkbox.className = 'pf-model-deep-toggle__input';
      // Accessibility: expose as switch
      checkbox.setAttribute('role','switch');
      try{ checkbox.setAttribute('aria-checked', checkbox.checked ? 'true' : 'false'); }catch(_){ }
      var label = document.createElement('label');
      label.setAttribute('for','pf-deep-thinking');
      label.className = 'pf-model-deep-toggle--label';
      // add a small lamp icon next to the label to indicate "deep thinking"
      label.innerHTML = '<span class="pf-model-deep-toggle__icon" aria-hidden="true">' +
        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 21h6v-1a3 3 0 00-6 0v1z" fill="currentColor" opacity="0.08"/><path d="M12 2a6 6 0 00-4 10.9V15a2 2 0 002 2h4a2 2 0 002-2v-2.1A6 6 0 0012 2z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
        '</span><span class="pf-model-deep-toggle__text">Deep Thinking</span>';
      toggleWrap.appendChild(checkbox);
      toggleWrap.appendChild(label);
      // Prevent clicks on the toggle from closing the menu
      checkbox.addEventListener('click', function(ev){ ev.stopPropagation(); });
      label.addEventListener('click', function(ev){ ev.stopPropagation(); });
      modelMenu.appendChild(toggleWrap);

      // initialize from localStorage when available
      try{
        var stored = localStorage.getItem('pf-deep-thinking');
        if(stored !== null){
          checkbox.checked = (stored === '1');
        }
      }catch(_){ }
      deepThinking = !!checkbox.checked;
      deepThinkingEnabled = !!checkbox.checked;
      console.log('[mascot] Deep Thinking initialized:', deepThinkingEnabled);

      checkbox.addEventListener('change', function(){
        deepThinking = this.checked;
        deepThinkingEnabled = !!this.checked;
        try{ localStorage.setItem('pf-deep-thinking', this.checked ? '1' : '0'); }catch(_){ }
        try{ this.setAttribute('aria-checked', this.checked ? 'true' : 'false'); }catch(_){ }
        console.log('[mascot] Deep Thinking:', deepThinking);
      });
    }

    function updateSelectedModel(){
      // Prefer the element in the document, but fall back to the local `modelDisplay` variable
      var triggerEl = document.getElementById('model-select-trigger') || modelDisplay;
      var labelSpan = triggerEl ? triggerEl.querySelector('.pf-model-label') : null;
      var sel = models.find(function(m){ return m.value === selectedModelValue; });
      if(labelSpan && sel) labelSpan.textContent = sel.label; else console.warn('[mascot] updateSelectedModel: labelSpan or sel missing', !!labelSpan, !!sel);
      Array.from(modelMenu.children).forEach(function(it){
            if(it.dataset && it.dataset.value){
              var selected = (it.dataset.value === selectedModelValue);
              it.classList.toggle('selected', selected);
              try{ it.setAttribute('aria-selected', selected ? 'true' : 'false'); }catch(_){ }
            }
      });
    }

    function openModelMenu(){
      modelMenu.classList.add('pf-owl-model-open'); modelDisplay.setAttribute('aria-expanded','true');
      try{
        // Compute trigger rect and viewport space. We'll render the menu as a fixed element
        // attached to document.body so it is never clipped by the chat container overflow.
        var trigRect = modelDisplay.getBoundingClientRect();
        var viewportBelow = window.innerHeight - trigRect.bottom;
        var viewportAbove = trigRect.top;
        var desiredMax = 260; // cap the menu height

        // Prefer downward when there's reasonable space below; open upward only if below is limited.
        var openUp = (viewportBelow < 120 && viewportAbove > viewportBelow);

        // Move the menu into document.body so it won't be clipped by the chat container
        if(modelMenu.parentNode !== document.body){
          document.body.appendChild(modelMenu);
          modelMenu.style.position = 'fixed';
          // set left but clamp to viewport so menu stays visible
          var desiredMinWidth = Math.max(trigRect.width, 160);
          var leftPos = Math.min(trigRect.left, Math.max(8, window.innerWidth - desiredMinWidth - 8));
          modelMenu.style.left = leftPos + 'px';
          modelMenu.style.minWidth = desiredMinWidth + 'px';
          // ensure menu appears above the chat root (root uses a very large z-index)
          try{
            var rootZ = parseInt(window.getComputedStyle(root).zIndex, 10) || 2147483648;
            modelMenu.style.zIndex = (rootZ + 1) + '';
          }catch(_){ modelMenu.style.zIndex = '2147483649'; }
        }

        if(!openUp){
          modelMenu.style.top = (Math.round(trigRect.bottom + 8)) + 'px';
          modelMenu.style.bottom = 'auto';
          // clamp to viewport space below but never exceed desiredMax
          var maxBelow = Math.max(60, Math.min(desiredMax, Math.max(0, viewportBelow - 12)));
          modelMenu.style.maxHeight = maxBelow + 'px';
          modelMenu.classList.remove('open-up');
        } else {
          // open above: position bottom relative to viewport
          modelMenu.style.bottom = (Math.round(window.innerHeight - trigRect.top + 8)) + 'px';
          modelMenu.style.top = 'auto';
          var maxAbove = Math.max(60, Math.min(desiredMax, Math.max(0, viewportAbove - 12)));
          modelMenu.style.maxHeight = maxAbove + 'px';
          modelMenu.classList.add('open-up');
        }

        // Ensure menu remains internally scrollable if taller than allowed
        modelMenu.style.overflow = 'auto';
      }catch(e){ /* ignore positioning errors */ }
    }
    function closeModelMenu(){
      modelMenu.classList.remove('pf-owl-model-open'); modelDisplay.setAttribute('aria-expanded','false');
      // Reset inline positioning and move the menu back into the model selector wrapper
      modelMenu.style.top = ''; modelMenu.style.bottom = ''; modelMenu.style.left = ''; modelMenu.style.minWidth = ''; modelMenu.style.maxHeight = ''; modelMenu.style.overflow = '';
      modelMenu.classList.remove('open-up');
      if(modelMenu.parentNode !== modelSel){
        modelSel.appendChild(modelMenu);
        modelMenu.style.position = '';
      }
    }

    modelDisplay.addEventListener('click', function(e){
      e.stopPropagation();
      if(modelMenu.classList.contains('pf-owl-model-open')) closeModelMenu(); else openModelMenu();
    });

    // Keyboard support for opening/closing model menu
    modelDisplay.addEventListener('keydown', function(e){
      if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); e.stopPropagation(); if(modelMenu.classList.contains('pf-owl-model-open')) closeModelMenu(); else openModelMenu(); }
    });

    document.addEventListener('click', function(e){
      if(!modelMenu.contains(e.target) && !modelDisplay.contains(e.target)) closeModelMenu();
    });

    function setThinking(isThinking){
      // Apply the "typing" state to the active bot message only.
      try{
        var typingEl = document.getElementById('pf-owl-typing');
        if(!typingEl){
          var botMsgs = board.querySelectorAll('.pf-owl-msg.bot');
          if(botMsgs && botMsgs.length) typingEl = botMsgs[botMsgs.length - 1];
        }
        if(typingEl){
          if(isThinking) typingEl.classList.add('typing'); else typingEl.classList.remove('typing');
        }
      }catch(_){ }
    }

    modelSel.appendChild(modelDisplay); modelSel.appendChild(modelMenu);
    // build menu UI and sync selection
    try{ buildModelMenu(); updateSelectedModel(); }catch(e){ console.warn('[mascot] buildModelMenu failed', e); }
    // Allow model menu to remain positioned if window resized while open
    window.addEventListener('resize', function(){
      if(modelMenu.classList.contains('pf-owl-model-open')){
        try{ closeModelMenu(); openModelMenu(); }catch(_){ }
      }
    });

    // File upload support (drag/drop and attach button)
    function sendFile(file){
      try{
        // show a thumbnail preview immediately in the chat for confirmation
        appendImageMsg(file, 'user');
        appendMsg('Fichier en cours d\'envoi...', 'user');
        var fd = new FormData(); fd.append('file', file); fd.append('filename', file.name); fd.append('mimetype', file.type);
        fd.append('model', selectedModelValue);
        fd.append('deepThinking', deepThinkingEnabled ? '1' : '0');
        fd.append('search', searchModeEnabled ? '1' : '0');
        var uploadUrl = (window.PERFRAN_IMAGE_UPLOAD_URL || (apiUrl + '/upload'));
        fetch(uploadUrl, { method: 'POST', body: fd }).then(function(res){
          // try to parse JSON, but tolerate non-JSON responses
          return res.json().catch(function(){ return { status: res.status }; });
        }).then(function(data){
          // If upload endpoint returns a reply text, show it
          if(data && (data.reply || data.answer)){
            appendMsg(data.reply || data.answer, 'bot');
            return;
          }
          // If upload endpoint returned a file URL, forward it to the AI endpoint for contextual reply
          var fileUrl = data && (data.url || data.fileUrl || data.file_url || data.path);
          if(fileUrl){
            // show the uploaded image as assistant receipt (small preview)
            appendImageMsg(fileUrl, 'user', true);
            // Inform the user we're processing the image
            appendMsg('Image envoyée — j\'analyse...', 'bot');
            // Call the AI API with the image reference so the backend can process it
            fetch(apiUrl, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ image: fileUrl, model: selectedModelValue, deepThinking: !!deepThinkingEnabled, search: !!searchModeEnabled }) }).then(function(r){
              return r.json().then(function(d){ return { status: r.status, data: d }; }).catch(function(){ return { status: r.status }; });
            }).then(function(obj){
              if(obj && obj.data && (obj.data.reply || obj.data.answer)){
                appendMsg(obj.data.reply || obj.data.answer, 'bot');
                if(obj.data.sources && Array.isArray(obj.data.sources)) appendSources(obj.data.sources);
              } else {
                // Backend didn't return a contextual reply — gentle fallback
                appendMsg("Je ne peux pas encore analyser les images, mais je peux répondre à vos questions !", 'bot');
              }
            }).catch(function(err){
              console.warn('AI processing of uploaded image failed', err);
              appendMsg("Je ne peux pas encore analyser les images, mais je peux répondre à vos questions !", 'bot');
            });
            return;
          }
          // No useful data returned from upload endpoint: try sending the image data directly to AI endpoint
          try{
            var reader = new FileReader();
            reader.onload = function(ev){
              var dataUrl = ev.target.result;
              // Send base64 data to AI endpoint in case backend accepts it
              fetch(apiUrl, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ image_base64: dataUrl, filename: file.name, model: selectedModelValue, deepThinking: !!deepThinkingEnabled, search: !!searchModeEnabled }) }).then(function(r){
                return r.json().then(function(d){ return { status: r.status, data: d }; }).catch(function(){ return { status: r.status }; });
              }).then(function(obj){
                if(obj && obj.data && (obj.data.reply || obj.data.answer)){
                  appendMsg(obj.data.reply || obj.data.answer, 'bot');
                  if(obj.data.sources && Array.isArray(obj.data.sources)) appendSources(obj.data.sources);
                } else {
                  appendMsg("Je ne peux pas encore analyser les images, mais je peux répondre à vos questions !", 'bot');
                }
              }).catch(function(err){
                console.warn('AI processing of uploaded image (base64) failed', err);
                appendMsg("Je ne peux pas encore analyser les images, mais je peux répondre à vos questions !", 'bot');
              });
            };
            reader.readAsDataURL(file);
          }catch(e){
            appendMsg("Je ne peux pas encore analyser les images, mais je peux répondre à vos questions !", 'bot');
          }
        }).catch(function(err){
          console.warn('File upload failed', err);
          appendMsg('Échec de l\'envoi du fichier.', 'bot');
        });
      }catch(e){ console.warn('sendFile failed', e); }
    }

    // Board drag/drop handlers
    board.addEventListener('dragenter', function(e){ e.preventDefault(); e.stopPropagation(); board.classList.add('pf-drop-active'); });
    board.addEventListener('dragover', function(e){ e.preventDefault(); e.dataTransfer.dropEffect = 'copy'; });
    board.addEventListener('dragleave', function(e){ e.preventDefault(); e.stopPropagation(); board.classList.remove('pf-drop-active'); });
    board.addEventListener('drop', function(e){
      e.preventDefault(); e.stopPropagation(); board.classList.remove('pf-drop-active');
      var files = (e.dataTransfer && e.dataTransfer.files) || [];
      if(files && files.length){
        Array.from(files).forEach(function(f){ sendFile(f); });
      }
    });
    // Input wrapper: allows placing the attach icon inside the input as a trailing adornment
    var inputWrap = document.createElement('div'); inputWrap.className = 'pf-owl-input-wrap';
    var input = document.createElement('input'); input.className = 'pf-owl-input'; input.id = 'pf-owl-input'; input.placeholder = 'Écris un message…';

    // Send icon options (two SVGs) — you can pick which to use by changing the default below
    var sendIcons = {
      option1: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M2 21L23 12L2 3L8 12L2 21Z" fill="currentColor"/></svg>',
      option2: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M22 2L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" fill="currentColor" opacity="0.08"/></svg>'
    };

    var send = document.createElement('button'); send.className = 'pf-owl-send'; send.id = 'pf-owl-send'; send.type = 'button'; send.setAttribute('aria-label','Envoyer message');
    // default icon: option2 (clean arrow/paper-plane variant) — swapped per request
    send.innerHTML = sendIcons.option2;
    // ensure svg scales correctly inside button
    try{ var s = send.querySelector('svg'); if(s){ s.setAttribute('width','16'); s.setAttribute('height','16'); s.style.display='block'; } }catch(_){ }

    // Attach button + hidden file input
    var attach = document.createElement('button'); attach.className = 'pf-owl-attach'; attach.type = 'button'; attach.title = 'Joindre un fichier'; attach.setAttribute('aria-label','Joindre un fichier');
    attach.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    var fileInputEl = document.createElement('input'); fileInputEl.type = 'file'; fileInputEl.multiple = true; fileInputEl.accept = 'image/*,application/pdf'; fileInputEl.style.display = 'none';
    fileInputEl.addEventListener('change', function(){ var files = this.files || []; if(files && files.length){ Array.from(files).forEach(function(f){ sendFile(f); }); } this.value = ''; });
    attach.addEventListener('click', function(e){ e.stopPropagation(); fileInputEl.click(); });

    // place input and attach together so attach appears inside the input on the right
    inputWrap.appendChild(input);
    inputWrap.appendChild(attach);
    footer.appendChild(modelSel); footer.appendChild(inputWrap); footer.appendChild(send);
    // keep hidden file input in DOM for selection
    root.appendChild(fileInputEl);
    chat.appendChild(header); chat.appendChild(board); chat.appendChild(footer);

    var btn = document.createElement('button'); btn.className = 'pf-owl-btn'; btn.id = 'pf-owl-btn'; btn.title = 'PerfRan Assistant'; btn.type = 'button'; btn.setAttribute('aria-label','PerfRan Assistant');
    btn.innerHTML = '\n' +
      '<div class="pf-owl-illustration">' +
        '<div class="owl">' +
          '<div class="body-3"></div>' +
          '<div class="body-2"></div>' +
          '<div class="body-1"></div>' +
          '<div class="head">' +
            '<div class="gradcap-anim">' +
              '<div class="gradcap">' +
                '<div class="gradcap-top"></div>' +
                '<div class="gradcap-base"></div>' +
                '<div class="gradcap-tassel gradcap-tassel-anim"></div>' +
              '</div>' +
            '</div>' +
            '<div class="nose"></div>' +
            '<div class="ear-l"></div>' +
            '<div class="ear-r"></div>' +
            '<div class="eye-l"></div>' +
            '<div class="eye-r"></div>' +
          '</div>' +
          '<div class="paw-l paw-l-left"></div>' +
          '<div class="paw-l paw-l-center"></div>' +
          '<div class="paw-l paw-l-right"></div>' +
          '<div class="paw-r paw-r-left"></div>' +
          '<div class="paw-r paw-r-center"></div>' +
          '<div class="paw-r paw-r-right"></div>' +
        '</div>' +
      '</div>';

    // Visual sizing and transforms are provided by external CSS (`mascot.css`).

    // High-specificity overrides are now placed in `PerFranMVC/View/FrontOffice/assets/css/mascot.css`.
    // This prevents the script from injecting forceful style tags at runtime.

    // Force widget inside viewport and clamp on resize
    function clampWidgetToViewport(){
      try{
        if(!root) return;
          // Positioning/clamping handled in external CSS. Keep function as a no-op placeholder.
      }catch(e){ console.warn('[mascot] clampWidgetToViewport failed', e); }
    }
    // Apply immediately and on resize/orientation change
    clampWidgetToViewport();
    window.addEventListener('resize', clampWidgetToViewport);

    // Helper: check /health availability with timeout
    function probeHealth(baseUrl){
      // Always use the port from PERFRAN_AI_API_URL if set
      var url = (window.PERFRAN_AI_API_URL || baseUrl);
      var m = url.match(/^(https?:\/\/[^\/]+)(?:\/.*)?$/);
      var host = m ? m[1] : window.location.origin;
      var healthUrl = host + '/health';
      return new Promise(function(resolve){
        try{
          var controller = new AbortController(); var signal = controller.signal;
          var timeout = setTimeout(function(){ controller.abort(); resolve(false); }, 3000);
          fetch(healthUrl, { method: 'GET', signal: signal }).then(function(res){ clearTimeout(timeout); resolve(!!res && res.ok); }).catch(function(){ clearTimeout(timeout); resolve(false); });
        } catch(e){ resolve(false); }
      });
    }

    // Determine the API URL by probing each candidate sequentially (first healthy wins)
    (function determineApi(){
      var i = 0;
      function next(){
        if(i >= candidates.length){ // none healthy - keep fallback
          console.warn('[mascot] No AI candidate healthy; using default', apiUrl);
          startHealthCheck();
          return;
        }
        var candidate = candidates[i++];
        probeHealth(candidate).then(function(ok){
          if(ok){ apiUrl = candidate; console.log('[mascot] using AI endpoint', apiUrl); startHealthCheck(); }
          else { next(); }
        });
      }
      next();
    })();

    // Health check: ping the AI endpoint's /health route (resilient) so we don't post when it's offline
    function startHealthCheck(){
      // Always use the port from PERFRAN_AI_API_URL if set
      var url = (window.PERFRAN_AI_API_URL || apiUrl);
      var m = url.match(/^(https?:\/\/[^\/]+)(?:\/.*)?$/);
      var host = m ? m[1] : window.location.origin;
      var healthUrl = host + '/health';
      var t = function(){
        fetch(healthUrl).then(function(res){ if(res.ok){ serverHealthy = true; console.log('[mascot] AI server healthy: ' + healthUrl); } else { serverHealthy = false; console.warn('[mascot] AI health check failed', res.status, healthUrl); } }).catch(function(e){ serverHealthy = false; console.warn('[mascot] AI health check error', e, healthUrl); });
      };
      t();
      setInterval(t, 15000);
    }

    root.appendChild(chat); root.appendChild(btn);
    // Model menu behavior integrated above (using template's buildModelMenu/updateSelectedModel)
    document.body.appendChild(root);

    // Ensure the external mascot CSS is loaded (try both common paths)
    try{
      var cssPaths = [
        'PerFranMVC/View/FrontOffice/assets/css/mascot.css',
        'PerFranMVC/View/FrontOffice/assets/css/mascot.css',
        '/PerFranMVC/View/FrontOffice/assets/css/mascot.css',
        '/PerFranMVC/View/FrontOffice/assets/css/mascot.css'
      ];
      cssPaths.forEach(function(p){
        try{
          if(!document.querySelector('link[href="'+p+'"]')){
            var l = document.createElement('link'); l.rel = 'stylesheet'; l.href = p; document.head.appendChild(l);
          }
        }catch(_){ }
      });
    }catch(e){ console.warn('[mascot] could not ensure external CSS', e); }

    // Ensure the visible illustration (which may be positioned by CSS) forwards clicks
    // to the actual toggle button. This makes the owl itself fully clickable even
    // if site CSS repositions the illustration outside the button element.
    try{
      var ill = document.querySelector('#pf-owl-fallback .pf-owl-illustration');
      if(ill){
        ill.addEventListener('click', function(e){ e.stopPropagation(); var b = document.getElementById('pf-owl-btn'); if(b) b.click(); });
      }
    }catch(e){ console.warn('[mascot] illustration click forward failed', e); }

    // Model menu interactivity and stacking are controlled by `mascot.css`.
    // Debug: log clicks on model display
    var pfOwlModelDisplay = document.getElementById('model-select-trigger') || document.querySelector('.pf-owl-model-display');
    if (pfOwlModelDisplay) {
      pfOwlModelDisplay.addEventListener('click', function() {
        console.log('[mascot.js] Model selector clicked');
      });
    }

    // Helpers
    function appendMsg(text, who){
      var m = document.createElement('div'); m.className = 'pf-owl-msg ' + (who==='user'?'user':'bot');
      var b = document.createElement('div'); b.className = 'bubble'; b.textContent = text;
      m.appendChild(b); board.appendChild(m);
      try{ m.scrollIntoView({ block: 'end', behavior: 'smooth' }); }catch(e){ board.scrollTop = board.scrollHeight; }
      return m;
    }

    // Append an image message. `fileOrUrl` may be a File object or a URL string.
    function appendImageMsg(fileOrUrl, who, isUploadedUrl){
      try{
        var m = document.createElement('div'); m.className = 'pf-owl-msg ' + (who==='user'?'user':'bot');
        var b = document.createElement('div'); b.className = 'bubble pf-owl-image-bubble';
        var img = document.createElement('img'); img.className = 'pf-owl-thumb';
        if(typeof fileOrUrl === 'string'){
          img.src = fileOrUrl;
          img.alt = 'Image envoyée';
          b.appendChild(img);
          m.appendChild(b);
          board.appendChild(m);
          try{ m.scrollIntoView({ block: 'end' }); }catch(e){ board.scrollTop = board.scrollHeight; }
          return m;
        }
        // File object: create a data URL preview
        var reader = new FileReader();
        reader.onload = function(ev){ img.src = ev.target.result; img.alt = fileOrUrl.name || 'Image envoyée'; b.appendChild(img); m.appendChild(b); board.appendChild(m); try{ m.scrollIntoView({ block: 'end' }); }catch(e){ board.scrollTop = board.scrollHeight; } };
        reader.readAsDataURL(fileOrUrl);
        return m;
      }catch(e){ console.warn('appendImageMsg failed', e); }
    }

    function appendSources(sources){
      if(!sources || !sources.length) return;
      try{
        var m = document.createElement('div'); m.className = 'pf-owl-msg bot pf-owl-sources';
        var b = document.createElement('div'); b.className = 'bubble';
        var html = '<div class="pf-sources-title">Sources :</div><ul class="pf-sources-list">';
        sources.forEach(function(s){
          var url = (typeof s === 'string') ? s : (s.url || s.link || '');
          var label = (s && s.title) ? s.title : url;
          if(url){ html += '<li><a href="'+escapeHtml(url)+'" target="_blank" rel="noopener">'+escapeHtml(label)+'</a></li>'; }
        });
        html += '</ul>';
        b.innerHTML = html; m.appendChild(b); board.appendChild(m);
        try{ m.scrollIntoView({ block: 'end' }); }catch(e){ board.scrollTop = board.scrollHeight; }
      }catch(e){ console.warn('appendSources failed', e); }
    }


    // Auto greet and explain based on page
    var path = window.location.pathname || '';
    var greet = 'Salut! Je suis là pour t\'aider! 🦉';
    var explanation = '';
    if (path.indexOf('index')!==-1 || path==='/') {
      greet = "Bienvenue sur PerFran! 🎓 Clique sur moi pour découvrir comment améliorer ton français!";
      explanation = "Ceci est la page d'accueil. Ici, tu peux accéder à toutes les fonctionnalités principales du site pour apprendre le français en t'amusant.";
    }
    if (path.indexOf('dashboard')!==-1) {
      greet = 'Bravo! Tu fais des progrès incroyables! 📊';
      explanation = "Voici ton tableau de bord. Tu peux suivre tes progrès, voir tes scores et accéder à tes jeux récents.";
    }
    if (path.indexOf('game1')!==-1) {
      greet = 'Prêt à jouer? 🎮 Je suis là si tu as besoin d\'aide!';
      explanation = "Bienvenue dans le jeu de grammaire ! Réponds aux questions pour améliorer ton français. Si tu as besoin d'un indice ou d'une explication, clique sur moi !";
    }
    appendMsg(greet, 'bot');
    if (explanation) setTimeout(function(){ appendMsg(explanation, 'bot'); }, 1200);
    // Hide/Show Owl Button
    var toggleOwlBtn = document.createElement('button');
    toggleOwlBtn.className = 'pf-owl-toggle-btn';
    toggleOwlBtn.type = 'button';
    // SVG icons for eye and eye-slash
    // Eye SVG (open)
    var eyeIcon = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#00d4ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>';
    // Eye-slash SVG (closed) - visually correct, thin, and balanced (Font Awesome style)
    var eyeSlashIcon = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#00d4ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.81 21.81 0 0 1 5.06-6.06"/><path d="M1 1l22 22"/><path d="M10.84 10.84A3 3 0 0 0 12 15a3 3 0 0 0 2.12-5.12"/></svg>';
    toggleOwlBtn.innerHTML = eyeSlashIcon;
    toggleOwlBtn.style.position = 'fixed';
    toggleOwlBtn.style.right = '12px';
    toggleOwlBtn.style.bottom = '120px';
    toggleOwlBtn.style.zIndex = '2147483649';
    toggleOwlBtn.style.background = 'linear-gradient(135deg, #00d4ff 0%, #6c63ff 100%)';
    toggleOwlBtn.style.color = '#fff';
    toggleOwlBtn.style.border = 'none';
    toggleOwlBtn.style.borderRadius = '50%';
    toggleOwlBtn.style.padding = '6px';
    toggleOwlBtn.style.width = '40px';
    toggleOwlBtn.style.height = '40px';
    toggleOwlBtn.style.display = 'flex';
    toggleOwlBtn.style.alignItems = 'center';
    toggleOwlBtn.style.justifyContent = 'center';
    toggleOwlBtn.style.boxShadow = '0 0 12px 2px #00d4ff, 0 2px 8px rgba(108,99,255,0.18)';
    toggleOwlBtn.style.cursor = 'pointer';
    toggleOwlBtn.style.transition = 'box-shadow 0.3s, border 0.3s';
    toggleOwlBtn.style.outline = 'none';
    // Add animated colorwave border
    toggleOwlBtn.style.border = '1.5px solid transparent';
    toggleOwlBtn.style.backgroundClip = 'padding-box, border-box';
    toggleOwlBtn.style.boxSizing = 'border-box';
    document.body.appendChild(toggleOwlBtn);

    var mascotVisible = true;
    toggleOwlBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      mascotVisible = !mascotVisible;
      root.style.display = mascotVisible ? '' : 'none';
      toggleOwlBtn.innerHTML = mascotVisible ? eyeSlashIcon : eyeIcon;
    });

    // Toggle
    var visible = false;
    function toggle(){ visible = !visible; if(visible) chat.classList.add('open'); else chat.classList.remove('open'); chat.setAttribute('aria-hidden', visible? 'false' : 'true'); btn.classList.toggle('open', visible); if(visible) input.focus(); }
    btn.addEventListener('click', function(e){ e.stopPropagation(); toggle(); });

    // Send behavior: try AI endpoint, otherwise fallback reply
    var isTyping = false;
    var serverHealthy = false;
    function showTyping(){
      isTyping = true;
      var t = document.createElement('div'); t.id='pf-owl-typing'; t.className='pf-owl-msg bot'; t.innerHTML='<div class="bubble">PerfRan réfléchit...</div>';
      board.appendChild(t);
      try{ t.scrollIntoView({ block: 'end' }); }catch(e){ board.scrollTop = board.scrollHeight; }
      // Add typing class to the active bot bubble only
      try{ setThinking(true); }catch(e){}
    }
    function hideTyping(){
      isTyping = false;
      try{ setThinking(false); }catch(e){}
      var t = document.getElementById('pf-owl-typing'); if(t) t.remove();
    }

    function sendMessage(){
      var v = input.value && input.value.trim(); if(!v || isTyping) return; input.value=''; appendMsg(v,'user'); showTyping();
      if (!serverHealthy){ hideTyping(); appendMsg("(Version fallback) Désolé, pas de connexion au serveur d'IA.", 'bot'); return; }
      var selectedModel = typeof selectedModelValue !== 'undefined' ? selectedModelValue : 'gemini-2.5-flash';
      // Try POST to backend; if fail, show fallback
      console.log('[mascot] sending to', apiUrl, 'model:', selectedModel);
      fetch(apiUrl, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({message:v, model:selectedModel, deepThinking: !!deepThinkingEnabled, search: !!searchModeEnabled}) }).then(function(res){
        return res.json().then(function(data){ return {status: res.status, data: data}; });
      }).then(function(obj){
        hideTyping(); var data = obj.data;
        if(obj.status >= 400){ console.warn('Mascot AI server responded with', obj.status, data); appendMsg("(Version fallback) Le serveur a répondu avec erreur: " + (data && data.error? data.error: obj.status), 'bot'); return; }
        if(data && (data.reply||data.answer)){
          appendMsg(data.reply||data.answer,'bot');
          if(data.sources && Array.isArray(data.sources)) appendSources(data.sources);
        } else appendMsg("(Version fallback) Je n\'ai pas de réponse intelligente pour le moment.",'bot');
      }).catch(function(err){ hideTyping(); console.warn('Mascot AI fetch failed', err); appendMsg("(Version fallback) Désolé, pas de connexion au serveur d'IA.", 'bot'); });
    }

    send.addEventListener('click', sendMessage);
    input.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); sendMessage(); } });

    // Accessibility: close chat on outside click
    document.addEventListener('click', function(e){
      // Ignore clicks on the toggle button or model menu/trigger so they don't close the chat
      try{
        if(typeof toggleOwlBtn !== 'undefined' && toggleOwlBtn && (toggleOwlBtn === e.target || toggleOwlBtn.contains(e.target))) return;
        if(modelMenu && (modelMenu === e.target || modelMenu.contains(e.target))) return;
        if(modelDisplay && (modelDisplay === e.target || modelDisplay.contains(e.target))) return;
      }catch(_){ }
      if(!root.contains(e.target) && visible){ toggle(); }
    });

    // Stacking/z-index should be handled by CSS; no periodic inline style changes.

    // Enhancement animations and optional visual tweaks live in `PerFranMVC/View/FrontOffice/assets/css/mascot.css`.

    // Auto-cheer on game pages (French messages + animation)
    try{
      if(/game/i.test(path)){
        // Slight delay so DOM is settled
        setTimeout(function(){
          try{
            var owlEl = document.querySelector('#pf-owl-fallback .pf-owl-illustration .owl');
            if(owlEl) owlEl.classList.add('cheer');
            // Cheer message in French
            appendMsg('Allez, tu peux le faire ! 🎉 Courage — continue comme ça !', 'bot');
            // stop cheering after 12s
            setTimeout(function(){ if(owlEl) owlEl.classList.remove('cheer'); }, 12000);
          }catch(e){ console.warn('mascot cheer failed', e); }
        }, 700);
      }
    }catch(e){ /* ignore */ }

    // Model menu and wave effect styles moved to `PerFranMVC/View/FrontOffice/assets/css/mascot.css`.

    console.log('[mascot.js] mascot injected');
  } catch(e){ try{ console.error('[owl-mascot] init error', e); }catch(_){} }
})();
