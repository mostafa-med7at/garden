<?php
// modules/volunteer/voting.php — Fn 21: Communal Fund Voting
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Community Voting';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/VotingController.php';

$controller = new VotingController();
$controller->index($user);
