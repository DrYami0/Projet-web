<?php
session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/UserC.php';

if (!isset($_SESSION['uid'])) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit;
}

$userC = new UserC();
$user = $userC->findByUsername($_SESSION['uid']);

if (!$user) {
    session_destroy();
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit;
}

// Use real data from database (0 as fallback instead of fake numbers)
$user['streak'] = $user['streak'] ?? 0;
$user['wins'] = $user['wins'] ?? 0;
$user['losses'] = $user['losses'] ?? 0;
$user['gamesPlayed1'] = $user['gamesPlayed1'] ?? 0;
$user['gamesPlayed2'] = $user['gamesPlayed2'] ?? 0;
$user['gamesPlayed3'] = $user['gamesPlayed3'] ?? 0;
$user['totalScore1'] = $user['totalScore1'] ?? 0;
$user['totalScore2'] = $user['totalScore2'] ?? 0;
$user['totalScore3'] = $user['totalScore3'] ?? 0;

$totalGames = ($user['gamesPlayed1'] ?? 0) + ($user['gamesPlayed2'] ?? 0) + ($user['gamesPlayed3'] ?? 0);
$totalScore = ($user['totalScore1'] ?? 0) + ($user['totalScore2'] ?? 0) + ($user['totalScore3'] ?? 0);
$winRate = (($user['wins'] ?? 0) + ($user['losses'] ?? 0)) > 0 
    ? round((($user['wins'] ?? 0) / (($user['wins'] ?? 0) + ($user['losses'] ?? 0))) * 100, 1) 
    : 0;

$fullNameDisplay = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
$initials = strtoupper(substr($user['username'], 0, 2));
$avatarUrl = $user['avatar'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - PerfRan</title>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <style>:root{--bg:#0f172a;--text:#e2e8f0}body{font-family:Inter,-apple-system,sans-serif;background:var(--bg);color:var(--text);margin:0}.sidebar{width:280px;position:fixed;height:100vh;background:linear-gradient(180deg,#026875,#074149)}</style>
    <!-- Inline preview CSS to ensure immediate preview works even if external CSS is cached -->
    <style>
    /* Force avatar preview appearance */
    .avatar-upload-preview{ width:90px !important; height:90px !important; border-radius:50% !important; overflow:hidden !important; display:flex !important; align-items:center !important; justify-content:center !important; background:linear-gradient(135deg,#ec4899,#8b5cf6) !important; border:3px solid rgba(255,255,255,0.08) !important; }
    .avatar-upload-preview .avatar{ width:100% !important; height:100% !important; border-radius:50% !important; background-size:cover !important; background-position:center !important; }
    .avatar-upload-preview img, .sidebar .avatar img{ display:block !important; width:100% !important; height:100% !important; object-fit:cover !important; border-radius:50% !important; }
    .sidebar .avatar{ background-size:cover !important; background-position:center !important; background-repeat:no-repeat !important; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/css/user-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"></noscript>
</head>
<body>

<!-- Mobile Toggle -->
<button class="mobile-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="avatar-container">
        <div class="avatar">
            <?php if ($avatarUrl): ?>
                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar">
            <?php else: ?>
                <?= $initials ?>
            <?php endif; ?>
        </div>
        <div class="avatar-status"></div>
    </div>
    
    <div class="username"><?= htmlspecialchars($user['username']) ?></div>
    <div class="email"><?= htmlspecialchars($user['email']) ?></div>
    
    <div class="user-role">
        <?php if (($user['role'] ?? 0) == 1): ?>
            <span class="role-badge admin"><i class="fas fa-shield-alt"></i> Admin</span>
        <?php else: ?>
            <span class="role-badge user"><i class="fas fa-user"></i> Membre</span>
        <?php endif; ?>
    </div>

    <button id="themeToggle" class="theme-toggle">
        <i class="fas fa-sun"></i>
        <span>Thème clair</span>
    </button>
    
    <nav>
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/dashboard.php">
            <i class="fas fa-home"></i> Tableau de bord
        </a>
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/account-profile.php" class="active">
            <i class="fas fa-user"></i> Mon Profil
        </a>
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/account-payment-details.php">
            <i class="fas fa-credit-card"></i> Abonnement / Don
        </a>
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/account-settings.php">
            <i class="fas fa-cog"></i> Paramètres
        </a>
        
        <div class="nav-divider"></div>
        
        <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/account-delete.php">
            <i class="fas fa-user-slash"></i> Désactiver le compte
        </a>
        
        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] == 1): ?>
        <a href="<?= BASE_URL ?>PerFranMVC/View/BackOffice/admin-dashboard.php" class="admin-link">
            <i class="fas fa-user-shield"></i> Panneau Admin
        </a>
        <?php endif; ?>
        
        <a href="<?= BASE_URL ?>PerFranMVC/Controller/auth.php?action=logout" class="logout">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </nav>
</aside>

<!-- Main Content -->
<main class="main">
    <div class="container">
        
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title"><i class="fas fa-user" style="color: var(--accent);"></i> Mon Profil</h1>
                <div class="breadcrumb">
                    <a href="<?= BASE_URL ?>PerFranMVC/View/FrontOffice/dashboard.php">Tableau de bord</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Mon Profil</span>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message message-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="message message-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Profile Progress Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Progression du profil</h3>
                <span class="badge badge-success"><i class="fas fa-check"></i> Compte vérifié</span>
            </div>
            <div class="progress-container">
                <div class="progress-header">
                    <span>Profil complété</span>
                    <span style="color: var(--accent); font-weight: 600;">88%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress" style="width: 88%;"></div>
                </div>
            </div>
            <div class="badges">
                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Email vérifié</span>
                <span class="badge badge-info"><i class="fas fa-gamepad"></i> Joueur actif</span>
                <span class="badge badge-warning"><i class="fas fa-fire"></i> Série de <?= $user['streak'] ?? 0 ?> jours</span>
                <span class="badge badge-primary"><i class="fas fa-plus"></i> Ajouter un avatar</span>
            </div>
        </div>

        <!-- Personal Information Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-card"></i> Informations personnelles</h3>
            </div>
            
            <form method="post" action="<?= BASE_URL ?>PerFranMVC/Controller/update-profile.php" enctype="multipart/form-data">
                
                <!-- Avatar Upload -->
                <div class="avatar-upload">
                    <div class="avatar-upload-preview">
                        <?php if ($avatarUrl): ?>
                            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar">
                        <?php else: ?>
                            <?= $initials ?>
                        <?php endif; ?>
                    </div>
                    <label class="avatar-upload-btn">
                        <i class="fas fa-camera"></i> Changer d'avatar
                        <input type="file" name="avatar" accept="image/*">
                    </label>
                    <p style="color: var(--muted); font-size: 12px; margin-top: 8px;">JPG, PNG ou GIF. Max 2MB</p>
                </div>

                <div class="form-grid">
                    <div class="form-group" style="display:flex;gap:12px;">
                        <div style="flex:1;">
                            <label><i class="fas fa-user"></i> Prénom</label>
                            <input type="text" name="firstName" value="<?= htmlspecialchars($user['firstName'] ?? '') ?>" placeholder="Entrez votre prénom">
                        </div>
                        <div style="flex:1;">
                            <label><i class="fas fa-user"></i> Nom</label>
                            <input type="text" name="lastName" value="<?= htmlspecialchars($user['lastName'] ?? '') ?>" placeholder="Entrez votre nom de famille">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Téléphone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+216 12 345 678">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-at"></i> Nom d'utilisateur</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+">
                        <small style="font-size: 12px; margin-top: 4px; display: block; color: var(--muted);">Lettres, chiffres et _ uniquement (3-50 caractères)</small>
                    </div>
                    <div class="form-group">
                        <div class="face-card" style="border:1px solid #334155;background:#1e293b;padding:18px 16px;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,0.08);display:flex;flex-direction:column;gap:10px;align-items:flex-start;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <i class="fas fa-user-circle" style="font-size:22px;color:#38bdf8;"></i>
                                <span style="font-weight:600;font-size:16px;">Reconnaissance faciale</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:12px;">
                                <?php $faceEnabled = !empty($user['face_recognition_enabled']); ?>
                                    <button type="button" id="faceRecBtn" style="background:linear-gradient(90deg,#06b6d4,#7c3aed);color:#fff;border:none;padding:8px 14px;border-radius:8px;cursor:pointer;font-size:14px;">
                                        <?= $faceEnabled ? 'Mettre à jour mes données faciales' : 'Enregistrer mes données faciales' ?>
                                    </button>
                                    <?php if ($faceEnabled): ?>
                                        <span id="face_status" style="font-size:13px;color:#10b981;font-weight:600;">Enregistré</span>
                                    <?php else: ?>
                                        <span id="face_status" style="font-size:13px;color:#ef4444;font-weight:700;">Aucune donnée enregistrée</span>
                                    <?php endif; ?>
                            </div>
                            <small style="font-size: 13px; color: var(--muted);margin-bottom:4px;">En activant cette option, vos données faciales seront enregistrées pour une connexion rapide et sécurisée.</small>
                            <button type="button" id="deleteFaceDataBtn" style="padding:5px 12px;background:#e53e3e;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;transition:background 0.2s;">
                                <i class="fas fa-trash"></i> Supprimer mes données faciales
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Password Section -->
                <div style="margin-top: 32px; padding-top: 32px; border-top: 1px solid var(--border);">
                    <h4 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-lock" style="color: var(--accent);"></i>
                        Changer le mot de passe
                        <small style="color: var(--muted); font-weight: 400;">(laisser vide pour ne pas modifier)</small>
                    </h4>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i class="fas fa-key"></i> Nouveau mot de passe</label>
                            <input type="password" name="new_password" placeholder="••••••••">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-check-double"></i> Confirmer le mot de passe</label>
                            <input type="password" name="confirm_password" placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 32px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>

    </div>
</main>

<script>
// Face Recognition Modal for Saving Data (button-driven)
const faceRecBtn = document.getElementById('faceRecBtn');
const faceStatus = document.getElementById('face_status');
if (faceRecBtn) {
    faceRecBtn.addEventListener('click', function(e) {
        e.preventDefault();
            try {
                if (typeof window.showFaceRecModal === 'function') window.showFaceRecModal(); else showFaceRecModal();
        } catch (err) {
            console.error('showFaceRecModal error:', err);
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible d\'ouvrir le modal de capture. Voir la console pour les détails.',
                    confirmButtonColor: '#e11d48'
                });
            }
        }
    });
}

window.showFaceRecModal = function() {
    let modal = document.getElementById('faceRecSaveModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'faceRecSaveModal';
        modal.style = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:9999;';
        modal.innerHTML = `
            <div style="background:#fff;padding:28px 22px;border-radius:16px;max-width:400px;box-shadow:0 8px 32px rgba(0,0,0,0.18);text-align:center;">
                <h2 style="font-size:1.2em;margin-bottom:10px;color:#0ea5e9;"><i class='fas fa-camera'></i> Enregistrer vos données faciales</h2>
                <video id="faceRecSaveVideo" width="320" height="240" autoplay playsinline style="border-radius:12px;background:#eee;"></video>
                <div id="faceRecSaveStatus" style="margin:18px 0 0 0;font-size:15px;color:#667eea;"></div>
                <button id="faceRecSaveCaptureBtn" type="button" disabled style="margin-top:18px;background:#38bdf8;color:#fff;padding:8px 24px;border-radius:8px;border:none;cursor:pointer;font-size:1em;">Capturer et enregistrer</button>
                <button id="faceRecSaveCloseBtn" type="button" style="margin-top:12px;background:#64748b;color:#fff;padding:6px 18px;border-radius:8px;border:none;cursor:pointer;font-size:0.95em;">Annuler</button>
            </div>
        `;
        document.body.appendChild(modal);
        // ensure capture button is disabled until video is ready
        const _cbtn = document.getElementById('faceRecSaveCaptureBtn');
        if (_cbtn) _cbtn.disabled = true;
    } else {
        modal.style.display = 'flex';
    }
    let video = document.getElementById('faceRecSaveVideo');
    let status = document.getElementById('faceRecSaveStatus');
    let stream = null;
    status.textContent = 'Initialisation de la caméra...';
    console.log('Requesting camera via getUserMedia');
    (async () => {
        try {
            const constraints = { video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' } };
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            // ensure playback starts
            try {
                await video.play();
            } catch (err) {
                console.warn('Video play() failed:', err);
            }
            // Wait for the video to have dimensions
            const waitForReady = (resolve, reject) => {
                let attempts = 0;
                const iv = setInterval(() => {
                    attempts++;
                    if (video.videoWidth && video.videoHeight) {
                        clearInterval(iv);
                        resolve();
                    } else if (attempts > 30) { // ~3s
                        clearInterval(iv);
                        reject(new Error('Video did not report dimensions'));
                    }
                }, 100);
            };
            await new Promise(waitForReady);
            status.textContent = 'Caméra active : regardez l\'objectif puis cliquez sur "Capturer et enregistrer"';
            document.getElementById('faceRecSaveCaptureBtn').disabled = false;
        } catch (err) {
            status.textContent = 'Impossible d\'accéder à la caméra.';
            console.error('getUserMedia failed or was denied by user:', err);
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Impossible d\'accéder à la caméra',
                    text: 'Autorisez l\'accès à la caméra ou vérifiez les paramètres du navigateur. (Console: ' + (err && err.message ? err.message : 'no message') + ')',
                    confirmButtonColor: '#e11d48'
                });
            }
            // disable capture to avoid empty snapshots
            const cbtn = document.getElementById('faceRecSaveCaptureBtn');
            if (cbtn) cbtn.disabled = true;
        }
    })();
    document.getElementById('faceRecSaveCloseBtn').onclick = function() {
        try {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            if (video) {
                try { video.pause(); } catch(e){}
                try { video.srcObject = null; } catch(e){}
            }
        } catch(e) { console.warn('Error stopping stream on close:', e); }
        // remove modal from DOM to free resources
        try { modal.remove(); } catch(e) { modal.style.display = 'none'; }
    };
    document.getElementById('faceRecSaveCaptureBtn').onclick = async function() {
        status.textContent = 'Traitement...';
        console.log('Capture button clicked, video size:', video.videoWidth, video.videoHeight);
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = canvas.toDataURL('image/png').replace(/^data:image\/png;base64,/, '');
        try {
            const response = await fetch('<?= BASE_URL ?>PerFranMVC/Controller/update-profile.php?action=save_face_data', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'image=' + encodeURIComponent(imageData)
            });
            let result;
            try {
                result = await response.json();
            } catch (jsonErr) {
                throw new Error('Réponse du serveur invalide.');
            }
            if (result.success) {
                if (stream) stream.getTracks().forEach(track => track.stop());
                modal.style.display = 'none';
                // Update UI status
                if (faceStatus) {
                    faceStatus.textContent = 'Enregistré';
                    faceStatus.style.color = '#10b981';
                }
                if (faceRecBtn) {
                    faceRecBtn.textContent = 'Mettre à jour mes données faciales';
                    faceRecBtn.disabled = false;
                    faceRecBtn.style.opacity = 1;
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: 'Données faciales enregistrées avec succès.',
                    confirmButtonColor: '#38bdf8',
                    timer: 2000,
                    timerProgressBar: true
                });
            } else {
                if (stream) stream.getTracks().forEach(track => track.stop());
                modal.style.display = 'none';
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: result.error || 'Échec de l\'enregistrement. Essayez à nouveau.',
                    confirmButtonColor: '#e11d48'
                });
            }
        } catch (err) {
            if (stream) stream.getTracks().forEach(track => track.stop());
            modal.style.display = 'none';
            Swal.fire({
                icon: 'error',
                title: 'Erreur serveur ou réseau',
                text: err.message || 'Impossible de contacter le serveur.',
                confirmButtonColor: '#e11d48'
            });
        }
    };
}
// Theme Toggle Setup (script include moved below to avoid breaking inline JS)

</script>

<script>
// Setup mascot loading with delay
window.PERFRAN_AI_API_URL = '<?= PERFRAN_AI_API_URL ?>';
(function(){
    function loadMascot(){
        if(window.__perfRanMascotLoaded) return;
        window.__perfRanMascotLoaded = true;
        var s = document.createElement('script');
        s.src = '<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/js/mascot.js';
        s.async = true;
        document.body.appendChild(s);
    }
    if(document.readyState === 'complete'){
        setTimeout(loadMascot, 1000);
    } else {
        window.addEventListener('load', function(){ setTimeout(loadMascot, 1000); });
    }
})();

// Mobile sidebar toggle
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('show');
}

// Avatar preview: robust handler with logging and fallback
document.addEventListener('DOMContentLoaded', function(){
    const avatarInput = document.querySelector('input[name="avatar"]');
    console.log('avatar-preview: init, input found=', !!avatarInput);
    if (!avatarInput) return;

    function applyPreviewSrc(src){
        try {
            const prev = document.querySelector('.avatar-upload-preview');
            if (prev) prev.innerHTML = `<div class="avatar has-preview" style="background-image:url('${src}');background-size:cover;background-position:center;"></div>`;
        } catch (err) { console.error('upload-preview update failed', err); }

        // Update sidebar avatar using background-image to avoid DOM replacement conflicts
        const sidebarAvatar = document.querySelector('.sidebar .avatar');
        if (sidebarAvatar) {
            try {
                // set background image inline so it remains even if innerHTML changes elsewhere
                sidebarAvatar.style.backgroundImage = `url('${src}')`;
                sidebarAvatar.style.backgroundSize = 'cover';
                sidebarAvatar.style.backgroundPosition = 'center';
                // ensure initials/text removed
                if (sidebarAvatar.textContent.trim().length && !sidebarAvatar.querySelector('img')) sidebarAvatar.textContent = '';
                // if an <img> exists, also update it
                const img = sidebarAvatar.querySelector('img');
                if (img) img.src = src;
            } catch (err) { console.error('sidebar avatar update failed', err); }
        }
    }

    avatarInput.addEventListener('change', function(e){
        console.log('avatar-preview: change event', e);
        const file = e.target.files && e.target.files[0];
        if (!file) { console.log('avatar-preview: no file selected'); return; }
        console.log('avatar-preview: file', file.name, file.type, file.size);

        // Try object URL first
        try {
            const url = URL.createObjectURL(file);
            applyPreviewSrc(url);
            // revoke after a delay
            setTimeout(()=>{ try{ URL.revokeObjectURL(url); }catch(e){} }, 30000);
            return;
        } catch (err) { console.warn('avatar-preview: createObjectURL failed, falling back to FileReader', err); }

        // Fallback to FileReader
        try {
            const reader = new FileReader();
            reader.onload = function(ev){ applyPreviewSrc(ev.target.result); };
            reader.onerror = function(ev){ console.error('avatar-preview: FileReader error', ev); };
            reader.readAsDataURL(file);
        } catch (err) { console.error('avatar-preview: FileReader fallback failed', err); }
    });
});

// Delete face recognition data
const delBtn = document.getElementById('deleteFaceDataBtn');
if (delBtn) {
    delBtn.addEventListener('click', function() {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: 'Voulez-vous supprimer vos données faciales et désactiver la connexion par reconnaissance faciale ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= BASE_URL ?>PerFranMVC/Controller/delete-face-data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Supprimé',
                        text: 'Vos données faciales ont été supprimées. La connexion par reconnaissance faciale est désactivée.',
                        confirmButtonColor: '#38bdf8',
                        timer: 2000,
                        timerProgressBar: true
                    });
                    document.getElementById('enable_face_recognition').checked = false;
                    document.getElementById('enable_face_recognition').disabled = true;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: data.error || 'Impossible de supprimer les données faciales.',
                        confirmButtonColor: '#e11d48'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur de connexion au serveur.',
                    confirmButtonColor: '#e11d48'
                });
            });
        }
    });
});
</script>

</body>
</html>
<?php ob_end_flush(); ?>
        </div>
    </div>
</div>

<script>
// Owl widget behavior
// Remove any other legacy/injected pf-owl-btn elements (keeps our .our-owl)
document.querySelectorAll('#pf-owl-btn:not(.our-owl)').forEach(el => el.remove());

(function(){
    const btn = document.getElementById('pf-owl-btn');
    const chat = document.getElementById('pf-owl-chat');
    const board = document.getElementById('pf-owl-board');
    const modelDisplay = document.getElementById('pf-owl-model-display');
    const modelMenu = document.getElementById('pf-owl-model-menu');

    if (!btn) { console.debug('pf-owl: no toggle button present'); return; }
    if (!chat || !board) { console.warn('pf-owl: missing chat or board elements'); return; }

    function toggleChat(show) {
        const isShown = chat.classList.contains('show');
        if (typeof show === 'boolean') {
            if (show && !isShown) chat.classList.add('show');
            if (!show && isShown) chat.classList.remove('show');
        } else {
            chat.classList.toggle('show');
        }
        chat.setAttribute('aria-hidden', !chat.classList.contains('show'));
    }

    btn.addEventListener('click', function(e){
        e.stopPropagation();
        toggleChat();
    });

    // Close on outside click
    document.addEventListener('click', function(e){
        if (!chat.contains(e.target) && !btn.contains(e.target)) {
            toggleChat(false);
            modelMenu.classList.remove('show');
        }
    });

    // Model menu toggle
    if (modelDisplay && modelMenu) {
        modelDisplay.addEventListener('click', function(e){
            e.stopPropagation();
            modelMenu.classList.toggle('show');
        });
    }

    // Model selection
    modelMenu.addEventListener('click', function(e){
        const item = e.target.closest('.pf-owl-model-item');
        if (!item) return;
        modelMenu.querySelectorAll('.pf-owl-model-item').forEach(i=>i.classList.remove('selected'));
        item.classList.add('selected');
        modelDisplay.textContent = item.textContent + ' ▾';
        modelMenu.classList.remove('show');
        // For now just update UI; integration with backend can be added later
        const selected = item.getAttribute('data-value');
        const msg = document.createElement('div');
        msg.className = 'pf-owl-msg bot';
        msg.innerHTML = '<div class="bubble">Modèle sélectionné: ' + item.textContent + '</div>';
        board.appendChild(msg);
        board.scrollTop = board.scrollHeight;
    });

    // Send message (UI only)
    const pfSend = document.getElementById('pf-owl-send');
    const pfInput = document.getElementById('pf-owl-input');
    if (pfSend && pfInput) {
        pfSend.addEventListener('click', function(){
            const v = pfInput.value.trim();
            if (!v) return;
            const out = document.createElement('div');
            out.className = 'pf-owl-msg user';
            out.innerHTML = '<div class="bubble" style="background:#0ea5e9;color:#042">' + escapeHtml(v) + '</div>';
            board.appendChild(out);
            pfInput.value = '';
            board.scrollTop = board.scrollHeight;
            // simple bot reply
            setTimeout(()=>{
                const reply = document.createElement('div');
                reply.className = 'pf-owl-msg bot';
                reply.innerHTML = '<div class="bubble">Super! Je peux t\'aider avec la grammaire, la conjugaison ou le vocabulaire. Essaie "Donne-moi un exercice".</div>';
                board.appendChild(reply);
                board.scrollTop = board.scrollHeight;
            }, 700);
        });
        // keyboard enter to send
        pfInput.addEventListener('keydown', function(e){
            if (e.key === 'Enter') { e.preventDefault(); pfSend.click(); }
        });
    }

    // keyboard enter to send
    document.getElementById('pf-owl-input').addEventListener('keydown', function(e){
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('pf-owl-send').click(); }
    });

    function escapeHtml(s){ return s.replace(/[&<>\"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#39;'}[c]; }); }

})();
</script>

<script>
// Post-init binding and diagnostics: ensure face and delete buttons respond
document.addEventListener('DOMContentLoaded', function() {
    try {
        console.log('account-profile: post-init bindings');
        const faceRecBtn = document.getElementById('faceRecBtn');
        const deleteBtn = document.getElementById('deleteFaceDataBtn');
        console.log('Elements found:', { faceRecBtn: !!faceRecBtn, deleteBtn: !!deleteBtn });

        if (faceRecBtn) {
            // Avoid duplicate double-binding by using a namespaced property
            if (!faceRecBtn.__bound) {
                faceRecBtn.addEventListener('click', function(e){
                    e.preventDefault();
                    try { if (typeof window.showFaceRecModal === 'function') window.showFaceRecModal(); else showFaceRecModal(); } catch (err) { console.error('showFaceRecModal error (post):', err); if (window.Swal) Swal.fire({icon:'error', title:'Erreur', text:'Impossible d\'ouvrir la capture. Voir la console.'}); else alert('Impossible d\'ouvrir la capture. Voir la console.'); }
                });
                faceRecBtn.__bound = true;
            }
        }

        if (deleteBtn) {
            if (!deleteBtn.__bound) {
                deleteBtn.addEventListener('click', function(e){
                    e.preventDefault();
                    const doDelete = function(){
                        fetch('<?= BASE_URL ?>PerFranMVC/Controller/delete-face-data.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
                            .then(r=>r.json()).then(data=>{
                                if (data.success) {
                                    if (window.Swal) Swal.fire({icon:'success', title:'Supprimé', text:'Vos données faciales ont été supprimées.', confirmButtonColor:'#38bdf8', timer:1500});
                                    else alert('Supprimé: vos données faciales ont été supprimées.');
                                    const fs = document.getElementById('face_status'); if (fs) { fs.textContent='Aucune donnée enregistrée'; fs.style.color='#ef4444'; }
                                } else {
                                    if (window.Swal) Swal.fire({icon:'error', title:'Erreur', text: data.error || 'Impossible de supprimer les données faciales.'});
                                    else alert(data.error || 'Impossible de supprimer les données faciales.');
                                }
                            }).catch(err=>{ console.error('delete-face-data fetch error:', err); if (window.Swal) Swal.fire({icon:'error', title:'Erreur', text:'Erreur réseau lors de la suppression.'}); else alert('Erreur réseau lors de la suppression.'); });
                    };

                    if (window.Swal) {
                        Swal.fire({ title: 'Êtes-vous sûr ?', text: 'Voulez-vous supprimer vos données faciales ?', icon:'warning', showCancelButton:true, confirmButtonColor:'#e11d48', cancelButtonColor:'#64748b', confirmButtonText:'Oui, supprimer' }).then(res=>{ if (res.isConfirmed) doDelete(); });
                    } else {
                        if (confirm('Voulez-vous supprimer vos données faciales ?')) doDelete();
                    }
                });
                deleteBtn.__bound = true;
            }
        }

    } catch (e) { console.error('post-init binding failed:', e); }
});
</script>
