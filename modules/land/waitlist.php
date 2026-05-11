<?php
// modules/land/waitlist.php — Fn 4: Plot Waitlist & Priority
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();   // must be logged in to interact
$pageTitle = 'Plot Waitlist';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/WaitlistController.php';

$controller = new WaitlistController();
$controller->index($user);
