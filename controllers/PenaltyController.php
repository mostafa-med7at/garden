<?php
require_once __DIR__ . '/../core/Controller.php';

class PenaltyController extends Controller {
    public function index($user) {
        $model = $this->model('PenaltyModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve']) && $user['role_name']==='admin') {
            $penId    = (int)$_POST['penalty_id'];
            $type     = $_POST['penalty_type'];
            $hours    = (float)($_POST['service_hours'] ?? 0);
            
            $model->resolvePenalty($penId, $type, $hours);
            
            if ($type === 'community_service_hours') {
                $uid = $model->getUserIdFromPenalty($penId);
                $month = date('Y-m');
                $model->addServiceHours($uid, $hours, 'Community service (tool penalty)', $month);
            }
            
            auditLog('penalty_resolved', 'resources', 'tool_penalties', $penId, "Type: $type");
            setFlash('success', 'Penalty resolved.');
            header('Location: penalties.php'); 
            exit;
        }

        if ($user['role_name'] === 'admin') {
            $penalties = $model->getAllPenalties();
        } else {
            $penalties = $model->getUserPenalties($user['id']);
        }

        $this->view('resources_penalties', [
            'user' => $user,
            'penalties' => $penalties,
            'pageTitle' => 'Tool Penalties'
        ]);
    }
}
