<?php
/**
 * Email Handler for Quiz Suggestions
 * Sends quiz suggestions to admin via email with approval/rejection links
 * Uses PHP's built-in mail() function with XAMPP sendmail
 */

class QuizSuggestionEmailer
{
    private $config;
    private $pendingDir;
    
    public function __construct()
    {
        $this->config = require __DIR__ . '/email_config.php';
        $this->pendingDir = __DIR__ . '/data/pending_suggestions/';
        
        // Clean up expired suggestions (older than 7 days)
        $this->cleanupExpiredSuggestions();
    }
    
    /**
     * Send quiz suggestion email to admin
     * 
     * @param array $data Quiz data (paragraph, difficulty, intruder_words)
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendSuggestionEmail(array $data): array
    {
        try {
            // Generate unique token
            $token = bin2hex(random_bytes(32));
            
            // Store suggestion data
            $suggestionData = [
                'paragraph' => $data['paragraph'],
                'difficulty' => $data['difficulty'],
                'intruder_words' => $data['intruder_words'] ?? '',
                'has_intruders' => isset($data['has_intruders']),
                'submitted_at' => date('Y-m-d H:i:s'),
                'token' => $token
            ];
            
            $filePath = $this->pendingDir . $token . '.json';
            file_put_contents($filePath, json_encode($suggestionData, JSON_PRETTY_PRINT));
            
            // Prepare email
            $to = $this->config['admin_email'];
            $subject = '🎯 Nouvelle suggestion de quiz reçue';
            $message = $this->buildEmailBody($suggestionData);
            $altMessage = $this->buildEmailBodyPlainText($suggestionData);
            
            // Headers for HTML email
            $headers = array(
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $this->config['from_name'] . ' <' . $this->config['from_email'] . '>',
                'Reply-To: ' . $this->config['from_email'],
                'X-Mailer: PHP/' . phpversion()
            );
            
            // Send email using PHP mail() function
            $result = mail($to, $subject, $message, implode("\r\n", $headers));
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Votre suggestion a été envoyée avec succès ! L\'administrateur la traitera prochainement.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de l\'email. Vérifiez la configuration du serveur.'
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur système : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Build HTML email body
     */
    private function buildEmailBody(array $data): string
    {
        $paragraph = htmlspecialchars($data['paragraph']);
        $difficulty = htmlspecialchars($data['difficulty']);
        $token = $data['token'];
        
        // Extract blanks (words between [])
        preg_match_all('/\[([^\]]+)\]/', $data['paragraph'], $matches);
        $correctAnswers = $matches[1];
        
        // Create quiz preview (replace [word] with blanks)
        $quizPreview = preg_replace('/\[([^\]]+)\]/', '<span style="display: inline-block; min-width: 80px; border-bottom: 2px solid #3498db; margin: 0 4px;">______</span>', $paragraph);
        
        // Build correct answers list
        $answersHtml = '';
        foreach ($correctAnswers as $index => $answer) {
            $num = $index + 1;
            $answersHtml .= "<li style=\"margin: 8px 0; font-size: 16px;\"><strong>Blank $num:</strong> <span style=\"color: #27ae60; font-weight: 600;\">$answer</span></li>";
        }
        
        // Build intruders list
        $intrudersHtml = '';
        if (!empty($data['intruder_words'])) {
            $intruders = array_map('trim', explode(',', $data['intruder_words']));
            foreach ($intruders as $intruder) {
                if (!empty($intruder)) {
                    $intrudersHtml .= "<li style=\"margin: 8px 0; font-size: 16px; color: #e74c3c; font-weight: 600;\">$intruder</li>";
                }
            }
        }
        
        // Difficulty badge color
        $difficultyColors = [
            'easy' => '#27ae60',
            'medium' => '#f39c12',
            'hard' => '#e74c3c'
        ];
        $difficultyColor = $difficultyColors[$data['difficulty']] ?? '#95a5a6';
        
        // Approval URLs
        $approveUrl = $this->config['base_url'] . '/Mail/handle_approval.php?action=approve&token=' . $token;
        $rejectUrl = $this->config['base_url'] . '/Mail/handle_approval.php?action=reject&token=' . $token;
        
        $html = "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f7fa;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f5f7fa; padding: 40px 20px;'>
                <tr>
                    <td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);'>
                            
                            <!-- Header -->
                            <tr>
                                <td style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                                    <h1 style='margin: 0; color: white; font-size: 28px; font-weight: 700;'>🎯 Nouvelle Suggestion de Quiz</h1>
                                    <p style='margin: 10px 0 0 0; color: rgba(255,255,255,0.9); font-size: 16px;'>Un joueur a proposé un nouveau quizz</p>
                                </td>
                            </tr>
                            
                            <!-- Content -->
                            <tr>
                                <td style='padding: 40px;'>
                                    
                                    <!-- Difficulty Badge -->
                                    <div style='margin-bottom: 30px;'>
                                        <span style='display: inline-block; background-color: $difficultyColor; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; text-transform: uppercase;'>
                                            Difficulté: $difficulty
                                        </span>
                                    </div>
                                    
                                    <!-- Quiz Preview -->
                                    <div style='background-color: #f8f9fa; border-left: 4px solid #3498db; padding: 20px; margin-bottom: 30px; border-radius: 6px;'>
                                        <h3 style='margin: 0 0 15px 0; color: #2c3e50; font-size: 18px;'>📝 Aperçu du Quiz:</h3>
                                        <p style='font-size: 16px; line-height: 1.8; color: #34495e; margin: 0;'>$quizPreview</p>
                                    </div>
                                    
                                    <!-- Correct Answers -->
                                    <div style='background-color: #e8f8f5; border-left: 4px solid #27ae60; padding: 20px; margin-bottom: 30px; border-radius: 6px;'>
                                        <h3 style='margin: 0 0 15px 0; color: #2c3e50; font-size: 18px;'>✅ Réponses Correctes:</h3>
                                        <ul style='margin: 0; padding-left: 20px;'>
                                            $answersHtml
                                        </ul>
                                    </div>
                                    
                                    " . (!empty($intrudersHtml) ? "
                                    <!-- Intruder Words -->
                                    <div style='background-color: #fef5e7; border-left: 4px solid #f39c12; padding: 20px; margin-bottom: 30px; border-radius: 6px;'>
                                        <h3 style='margin: 0 0 15px 0; color: #2c3e50; font-size: 18px;'>⚠️ Mots Intrus:</h3>
                                        <ul style='margin: 0; padding-left: 20px;'>
                                            $intrudersHtml
                                        </ul>
                                    </div>
                                    " : "") . "
                                    
                                    <!-- Action Buttons -->
                                    <div style='text-align: center; margin-top: 40px; padding-top: 30px; border-top: 2px solid #ecf0f1;'>
                                        <h3 style='margin: 0 0 20px 0; color: #2c3e50; font-size: 20px;'>Que souhaitez-vous faire ?</h3>
                                        
                                        <table width='100%' cellpadding='0' cellspacing='0'>
                                            <tr>
                                                <td align='center' style='padding: 10px;'>
                                                    <a href='$approveUrl' style='display: inline-block; background-color: #27ae60; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 600; box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);'>
                                                        ✅ Approuver
                                                    </a>
                                                </td>
                                                <td align='center' style='padding: 10px;'>
                                                    <a href='$rejectUrl' style='display: inline-block; background-color: #e74c3c; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 600; box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);'>
                                                        ❌ Rejeter
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <p style='margin-top: 30px; text-align: center; color: #95a5a6; font-size: 13px;'>
                                        Cette suggestion a été soumise le " . $data['submitted_at'] . "
                                    </p>
                                    
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style='background-color: #2c3e50; padding: 20px; text-align: center;'>
                                    <p style='margin: 0; color: #ecf0f1; font-size: 14px;'>
                                        PerFran Quiz System - Système de gestion de quiz
                                    </p>
                                </td>
                            </tr>
                            
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
        
        return $html;
    }
    
    /**
     * Build plain text email body (fallback)
     */
    private function buildEmailBodyPlainText(array $data): string
    {
        $paragraph = $data['paragraph'];
        $difficulty = $data['difficulty'];
        $token = $data['token'];
        
        // Extract blanks
        preg_match_all('/\[([^\]]+)\]/', $data['paragraph'], $matches);
        $correctAnswers = $matches[1];
        
        $text = "NOUVELLE SUGGESTION DE QUIZ\n";
        $text .= "==========================\n\n";
        $text .= "Difficulté: " . strtoupper($difficulty) . "\n\n";
        $text .= "TEXTE DU QUIZ:\n";
        $text .= $paragraph . "\n\n";
        $text .= "RÉPONSES CORRECTES:\n";
        foreach ($correctAnswers as $index => $answer) {
            $num = $index + 1;
            $text .= "  Blank $num: $answer\n";
        }
        
        if (!empty($data['intruder_words'])) {
            $text .= "\nMOTS INTRUS:\n";
            $intruders = array_map('trim', explode(',', $data['intruder_words']));
            foreach ($intruders as $intruder) {
                if (!empty($intruder)) {
                    $text .= "  - $intruder\n";
                }
            }
        }
        
        $approveUrl = $this->config['base_url'] . '/Mail/handle_approval.php?action=approve&token=' . $token;
        $rejectUrl = $this->config['base_url'] . '/Mail/handle_approval.php?action=reject&token=' . $token;
        
        $text .= "\n\nACTIONS:\n";
        $text .= "Approuver: $approveUrl\n";
        $text .= "Rejeter: $rejectUrl\n";
        $text .= "\nSoumis le: " . $data['submitted_at'] . "\n";
        
        return $text;
    }
    
    /**
     * Clean up suggestions older than 7 days
     */
    private function cleanupExpiredSuggestions(): void
    {
        $files = glob($this->pendingDir . '*.json');
        $expiryTime = time() - (7 * 24 * 60 * 60); // 7 days
        
        foreach ($files as $file) {
            if (filemtime($file) < $expiryTime) {
                unlink($file);
            }
        }
    }
}
