<?php
// modules/resources/seeds.php — Fn 9: Seed Viability & Expiry Tracker
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/SeedController.php';

$controller = new SeedController();
$controller->index($user);

