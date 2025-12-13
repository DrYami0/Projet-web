<?php
/**
 * QR Code Token Generator
 * Generates a unique token for QR code login
 */

session_start();
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

/**
 * Get the server's local network IP address
 * This allows phones on the same WiFi to access the server
 */
function getOverrideHost() {
    // Allow manual override via query (?host=192.168.1.20:8080) or env var QR_HOST
    $host = $_GET['host'] ?? getenv('QR_HOST') ?? '';
    $host = trim($host);
    if ($host === '') {
        return '';
    }
    // Basic validation: allow host[:port]
    if (preg_match('/^[a-zA-Z0-9.-]+(:\d{2,5})?$/', $host)) {
        return $host;
    }
    return '';
}

function getLocalIP() {
    $isLocal = static function ($host) {
        return in_array($host, ['127.0.0.1', '::1', 'localhost'], true);
    };

    // 0) Manual override via query param (?host=192.168.1.100) or env var
    $override = getOverrideHost();
    if ($override !== '') {
        return $override;
    }

    // 1) If accessed from non-localhost HTTP_HOST, use that (most reliable)
    if (!empty($_SERVER['HTTP_HOST']) && !$isLocal($_SERVER['HTTP_HOST'])) {
        // Extract just the host part (remove port if present for comparison)
        $host = $_SERVER['HTTP_HOST'];
        if (strpos($host, ':') !== false) {
            list($ip, $port) = explode(':', $host);
            // Only accept if it looks like an IP or hostname
            if (filter_var($ip, FILTER_VALIDATE_IP) || preg_match('/^[a-z0-9.-]+$/i', $ip)) {
                return $host;
            }
        } else if (filter_var($host, FILTER_VALIDATE_IP) || preg_match('/^[a-z0-9.-]+$/i', $host)) {
            return $host;
        }
    }

    // 2) Windows: Find ALL network IPs and return the best one
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $output = shell_exec('ipconfig 2>nul');
        if ($output) {
            $ips = [];
            
            // Find all IPv4 addresses
            if (preg_match_all('/IPv4[^\d]+([\d.]+)/i', $output, $matches)) {
                $ips = array_merge($ips, $matches[1]);
            }
            
            // Filter out Docker/WSL IPs (172.x.x.x range typically)
            $validIps = [];
            foreach ($ips as $ip) {
                // Skip Docker/WSL (172.17-31.x.x, 127.x.x.x)
                if (preg_match('/^127\./', $ip)) continue;
                if (preg_match('/^172\.(1[7-9]|2[0-9]|3[01])\./', $ip)) continue;
                
                $validIps[] = $ip;
            }
            
            // Priority order:
            // 1. 192.168.x.x (most common for home WiFi/hotspot)
            foreach ($validIps as $ip) {
                if (preg_match('/^192\.168\./', $ip)) {
                    return $ip;
                }
            }
            
            // 2. 10.x.x.x (corporate networks)
            foreach ($validIps as $ip) {
                if (preg_match('/^10\./', $ip)) {
                    return $ip;
                }
            }
            
            // 3. 172.0-16.x.x or 172.32+.x.x (other private ranges)
            foreach ($validIps as $ip) {
                if (preg_match('/^172\.(([0-9]|1[0-6])|3[2-9])\./', $ip)) {
                    return $ip;
                }
            }
            
            // 4. Any remaining valid IP
            if (!empty($validIps)) {
                return $validIps[0];
            }
        }
    }
    
    // 3) Linux/Mac: hostname -I or ifconfig
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        // Try hostname -I first
        $ip = trim(shell_exec("hostname -I 2>/dev/null | awk '{print $1}'"));
        if ($ip && $ip !== '') {
            return $ip;
        }
        
        // Fallback to ifconfig parsing
        $output = shell_exec('ifconfig 2>/dev/null');
        if ($output && preg_match('/inet\s+([\d.]+)/m', $output, $matches)) {
            // Skip localhost
            if ($matches[1] !== '127.0.0.1') {
                return $matches[1];
            }
        }
    }
    
    // 4) Last resort: try socket connection to determine local IP
    $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($sock) {
        @socket_connect($sock, "8.8.8.8", 53);
        $ip = '';
        @socket_getsockname($sock, $ip);
        @socket_close($sock);
        if ($ip && $ip !== '0.0.0.0' && $ip !== '') {
            return $ip;
        }
    }

    // 4b) Try gethostname resolution as a fallback
    $resolved = gethostbyname(gethostname());
    if ($resolved && $resolved !== '127.0.0.1' && filter_var($resolved, FILTER_VALIDATE_IP)) {
        return $resolved;
    }
    
    // 5) Final fallback - return localhost with port if on non-standard port
    $port = $_SERVER['SERVER_PORT'] ?? 80;
    if ($port != 80 && $port != 443) {
        return 'localhost:' . $port;
    }
    return 'localhost';
}

try {
    $pdo = config::getConnexion();
    
    // Generate unique token
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    // Store token in database
    $stmt = $pdo->prepare("INSERT INTO qr_login_tokens (token, expires_at, status) VALUES (:token, :expires_at, 'pending')");
    $stmt->execute([
        ':token' => $token,
        ':expires_at' => $expiresAt
    ]);

    // Get server host (LAN IP/hostname) for mobile access (auto-detected or overridden)
    $serverHost = getLocalIP();
    // Ensure server_host column exists (add if missing) in a MariaDB/MySQL compatible way
    try {
        $dbNameStmt = $pdo->query("SELECT DATABASE() AS dbname");
        $dbName = $dbNameStmt ? $dbNameStmt->fetchColumn() : null;
        $colCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'qr_login_tokens' AND COLUMN_NAME = 'server_host'");
        $colCheck->execute([':db' => $dbName]);
        $hasCol = (bool)$colCheck->fetchColumn();
        if (!$hasCol) {
            $pdo->exec("ALTER TABLE qr_login_tokens ADD COLUMN server_host VARCHAR(255) NULL");
        }
        // Save server host for this token so the mobile client can link back to the PC URL
        $update = $pdo->prepare("UPDATE qr_login_tokens SET server_host = :server_host WHERE token = :token");
        $update->execute([':server_host' => $serverHost, ':token' => $token]);
    } catch (Exception $e) {
        error_log('QR generate: failed to add/update server_host: ' . $e->getMessage());
        // continue; token generation is the critical path
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    
    // Normalize serverHost: if it looks like an IP only, keep as-is; if it is 'localhost' and a port is present, preserve port
    $qrLoginUrl = $scheme . '://' . $serverHost . '/projet-web/PerFranMVC/View/FrontOffice/qr-login-simple.php?token=' . $token;
    
    echo json_encode([
        'success' => true,
        'token' => $token,
        'qrUrl' => $qrLoginUrl,
        'serverHost' => $serverHost,
        'expiresAt' => $expiresAt
    ]);
    
} catch (Exception $e) {
    // Log full exception for debugging and return a user-friendly message
    error_log('QR generate exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la génération du QR code'
    ]);
}
