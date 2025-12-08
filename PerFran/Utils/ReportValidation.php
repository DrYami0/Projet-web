<?php
class ReportValidation {
    
    public static function validateDescription($description) {
        $description = trim($description ?? '');
        
        if (empty($description)) {
            return 'La description est obligatoire et ne peut pas être vide.';
        }
        if (strlen($description) < 10) {
            return 'La description doit contenir au moins 10 caractères.';
        }
        if (strlen($description) > 1000) {
            return 'La description ne peut pas dépasser 1000 caractères.';
        }
        
        return null;
    }
    
    public static function validateNomj($nomj) {
        $nomj = trim($nomj ?? '');
        
        if (empty($nomj)) {
            return 'Le nom du joueur est obligatoire.';
        }
        if (strlen($nomj) < 2) {
            return 'Le nom du joueur doit contenir au moins 2 caractères.';
        }
        
        return null;
    }
    
    public static function validateAll($description, $nomj) {
        $descError = self::validateDescription($description);
        if ($descError) {
            return $descError;
        }
        $nomjError = self::validateNomj($nomj);
        if ($nomjError) {
            return $nomjError;
        }
        
        return null;
    }
}
?>
