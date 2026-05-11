<?php
// modules/volunteer/broadcast.php — Fn 20: Emergency Broadcaster
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Emergency Broadcast';
$db        = getDB();
// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/BroadcastController.php';

$controller = new BroadcastController();
$controller->index($user);
