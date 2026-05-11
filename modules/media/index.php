<?php
// modules/media/index.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user = requireLogin();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/MediaController.php';

$controller = new MediaController();
$controller->index($user);

