<?php
require_once __DIR__ . '/../core/Controller.php';

class PenaltyController extends Controller {
    public function index($user) {
        $model = $this->model('PenaltyModel');

        // Resolve existing penalty
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve']) && $user['role_name'] === 'admin') {
            $penId = (int)$_POST['penalty_id'];
            $type  = $_POST['penalty_type'];
            $hours = (float)($_POST['service_hours'] ?? 0);

            $model->resolvePenalty($penId, $type, $hours);

            if ($type === 'community_service_hours') {
                $uid   = $model->getUserIdFromPenalty($penId);
                $month = date('Y-m');
                $model->addServiceHours($uid, $hours, 'Community service (tool penalty)', $month);
            }

            auditLog('penalty_resolved', 'resources', 'tool_penalties', $penId, "Type: $type");
            setFlash('success', 'Penalty resolved.');
            header('Location: penalties.php');
            exit;
        }

        // Issue manual penalty (admin only)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_penalty']) && $user['role_name'] === 'admin') {
            $targetUser  = (int)$_POST['target_user_id'];
            $fineAmount  = max(0, (float)$_POST['fine_amount']);
            $svcHours    = max(0, (float)($_POST['service_hours_manual'] ?? 0));
            $reason      = trim($_POST['reason']);

            if ($targetUser && $reason) {
                $penId = $model->addManualPenalty($targetUser, $fineAmount, $svcHours, $reason);
                auditLog('manual_penalty_issued', 'resources', 'tool_penalties', $penId,
                         "User #$targetUser — £$fineAmount — $reason");
                setFlash('success', 'Manual penalty issued successfully.');
            } else {
                setFlash('danger', 'Please select a member and provide a reason.');
            }
            header('Location: penalties.php');
            exit;
        }

        $penalties   = ($user['role_name'] === 'admin')
            ? $model->getAllPenalties()
            : $model->getUserPenalties($user['id']);

        $activeUsers = ($user['role_name'] === 'admin') ? $model->getActiveUsers() : [];

        $this->view('resources_penalties', [
            'user'        => $user,
            'penalties'   => $penalties,
            'activeUsers' => $activeUsers,
            'pageTitle'   => 'Tool Penalties'
        ]);
    }
}
