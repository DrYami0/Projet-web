<?php

require_once __DIR__ . '/../../config/config.php';

class Controller {
    protected $viewPath;

    public function __construct() {
        $this->viewPath = VIEW_PATH;
    }

    protected function render($view, $data = []) {
        extract($data);
        $viewFile = $this->viewPath . '/' . $view . '.php';

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new Exception("View file $viewFile not found");
        }
    }

    protected function renderWithLayout($view, $data = [], $layout = 'main') {
        $data['content'] = $this->getViewContent($view, $data);
        $this->render('layouts/' . $layout, $data);
    }

    protected function getViewContent($view, $data = []) {
        extract($data);
        ob_start();
        $viewFile = $this->viewPath . '/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new Exception("View file $viewFile not found");
        }
        
        return ob_get_clean();
    }

    protected function redirect($url) {
        header('Location: ' . BASE_URL . ltrim($url, '/'));
        exit;
    }
}
