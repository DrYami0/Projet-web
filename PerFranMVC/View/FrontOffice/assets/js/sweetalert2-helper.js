/**
 * SweetAlert2 Helper Functions
 * Facilite l'utilisation de SweetAlert2 dans le projet
 */

// Attendre que SweetAlert2 soit chargé
if (typeof Swal === 'undefined') {
    console.warn('SweetAlert2 n\'est pas chargé. Veuillez inclure la bibliothèque avant ce fichier.');
}

/**
 * Affiche une alerte de succès
 */
function showSuccess(title, message, callback) {
    return Swal.fire({
        icon: 'success',
        title: title || 'Succès !',
        text: message || '',
        confirmButtonText: 'OK',
        confirmButtonColor: '#27ae60'
    }).then((result) => {
        if (callback && typeof callback === 'function') {
            callback(result);
        }
        return result;
    });
}

/**
 * Affiche une alerte d'erreur
 */
function showError(title, message, callback) {
    return Swal.fire({
        icon: 'error',
        title: title || 'Erreur !',
        text: message || '',
        confirmButtonText: 'OK',
        confirmButtonColor: '#e74c3c'
    }).then((result) => {
        if (callback && typeof callback === 'function') {
            callback(result);
        }
        return result;
    });
}

/**
 * Affiche une alerte d'avertissement
 */
function showWarning(title, message, callback) {
    return Swal.fire({
        icon: 'warning',
        title: title || 'Attention !',
        text: message || '',
        confirmButtonText: 'OK',
        confirmButtonColor: '#f39c12'
    }).then((result) => {
        if (callback && typeof callback === 'function') {
            callback(result);
        }
        return result;
    });
}

/**
 * Affiche une alerte d'information
 */
function showInfo(title, message, callback) {
    return Swal.fire({
        icon: 'info',
        title: title || 'Information',
        text: message || '',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3498db'
    }).then((result) => {
        if (callback && typeof callback === 'function') {
            callback(result);
        }
        return result;
    });
}

/**
 * Affiche une confirmation (remplace confirm())
 */
function showConfirm(title, message, confirmText, cancelText, callback) {
    return Swal.fire({
        title: title || 'Êtes-vous sûr ?',
        text: message || '',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmText || 'Oui',
        cancelButtonText: cancelText || 'Non'
    }).then((result) => {
        if (callback && typeof callback === 'function') {
            callback(result.isConfirmed);
        }
        return result.isConfirmed;
    });
}

/**
 * Affiche un prompt (remplace prompt())
 */
function showPrompt(title, message, inputPlaceholder, inputValue, callback) {
    return Swal.fire({
        title: title || 'Entrez une valeur',
        text: message || '',
        input: 'text',
        inputPlaceholder: inputPlaceholder || '',
        inputValue: inputValue || '',
        showCancelButton: true,
        confirmButtonColor: '#3498db',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Valider',
        cancelButtonText: 'Annuler',
        inputValidator: (value) => {
            if (!value || value.trim() === '') {
                return 'Veuillez entrer une valeur !';
            }
        }
    }).then((result) => {
        if (callback && typeof callback === 'function') {
            callback(result.isConfirmed ? result.value : null);
        }
        return result.isConfirmed ? result.value : null;
    });
}

/**
 * Affiche un loader/timer
 */
function showLoading(title) {
    return Swal.fire({
        title: title || 'Chargement...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

/**
 * Ferme toutes les alertes SweetAlert2
 */
function closeSwal() {
    Swal.close();
}

/**
 * Remplacer les alert() natifs par SweetAlert2
 */
function replaceNativeAlerts() {
    // Sauvegarder les fonctions natives
    window.nativeAlert = window.alert;
    window.nativeConfirm = window.confirm;
    window.nativePrompt = window.prompt;

    // Remplacer alert
    window.alert = function(message) {
        return showInfo('Information', message);
    };

    // Remplacer confirm (optionnel - peut causer des problèmes avec certains code)
    // window.confirm = function(message) {
    //     return showConfirm('Confirmation', message);
    // };
}

// Option pour activer le remplacement automatique (décommenter si nécessaire)
// replaceNativeAlerts();

