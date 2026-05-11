<?php
// modules/marketplace/advice.php — Fn 27: P2P Advice Exchange
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Advice Board';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/AdviceController.php';

$controller = new AdviceController();
$controller->index($user);
