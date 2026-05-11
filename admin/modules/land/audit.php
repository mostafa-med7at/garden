<?php
// modules/land/audit.php — Fn 29: RBAC Management, Fn 30: System Audit Trail
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Admin Panel — RBAC & Audit Trail';
$db        = getDB();
// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/AuditController.php';

$controller = new AuditController();
$controller->index($user);
