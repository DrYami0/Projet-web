<?php
class PunishmentValidation {
    
    public static function validateBanReason($banReason) {
        $banReason = trim($banReason ?? '');
        
        if (empty($banReason)) {
            return 'La raison du ban est obligatoire.';
        }
        if (strlen($banReason) < 10) {
            return 'La raison doit contenir au moins 10 caractères.';
        }
        if (strlen($banReason) > 500) {
            return 'La raison ne peut pas dépasser 500 caractères.';
        }
        
        return null;
    }
    
    public static function validateBanDuration($banDuration, $banType) {
        $banDuration = (int)($banDuration ?? 0);
        
        if ($banType !== 'permanent' && $banDuration <= 0) {
            return 'La durée du ban est obligatoire pour les bans temporaires.';
        }
        
        if ($banDuration > 0 && ($banDuration < 1 || $banDuration > 365)) {
            return 'La durée doit être entre 1 et 365 jours.';
        }
        
        return null;
    }
    
    public static function validateAll($banReason, $banDuration, $banType) {
        $reasonError = self::validateBanReason($banReason);
        if ($reasonError) {
            return $reasonError;
        }
        
        $durationError = self::validateBanDuration($banDuration, $banType);
        if ($durationError) {
            return $durationError;
        }
        
        return null;
    }
}
?>
