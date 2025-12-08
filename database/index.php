<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the requested URL
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// Remove the script name from the request
if (strpos($request, $scriptName) === 0) {
    $request = substr($request, strlen($scriptName));
}

// Remove any trailing slashes
$request = rtrim($request, '/');
if (empty($request)) {
    $request = '/';
}


// Route the request
switch ($request) {
    case '/backoffice':
    case '/backoffice/':
        $file = __DIR__ . '/PerFranMVC/View/BackOffice/quiz_list.php';
        if (file_exists($file)) {
            require $file;
        } else {
            die("File not found: " . htmlspecialchars($file));
        }
        break;
    
    case '/backoffice/quiz/add':
        $file = __DIR__ . '/PerFranMVC/View/BackOffice/quiz_add.php';
        if (file_exists($file)) {
            require $file;
        } else {
            die("File not found: " . htmlspecialchars($file));
        }
        break;
        
    case '/backoffice/quiz/edit':
        $id = $_GET['id'] ?? 0;
        $file = __DIR__ . '/PerFranMVC/View/BackOffice/quiz_edit.php';
        if (file_exists($file)) {
            require $file;
        } else {
            die("File not found: " . htmlspecialchars($file));
        }
        break;
        
    case '/backoffice/blanks':
        $file = __DIR__ . '/PerFranMVC/View/BackOffice/blank_list.php';
        if (file_exists($file)) {
            require $file;
        } else {
            die("File not found: " . htmlspecialchars($file));
        }
        break;
        
    default:
        // Try to serve the file directly if it exists
        $file = __DIR__ . $request;
        if (file_exists($file) && !is_dir($file)) {
            return false;
        }
        
        // 404 Not Found
        http_response_code(404);
        echo '404 Not Found - ' . htmlspecialchars($request);
        break;
}
