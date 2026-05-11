<?php
// core/View.php
class View {
    public static function render($viewPath, $data = []) {
        extract($data);
        require_once __DIR__ . '/../views/' . $viewPath . '.php';
    }
}
