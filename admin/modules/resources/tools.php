<?php
// modules/resources/tools.php — Fn 10-16: Tool State Machine, Usage Trigger, Reservations, Damage, Inventory, Media, Penalties
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Tool Library';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/ToolController.php';

$controller = new ToolController();
$controller->index($user);
