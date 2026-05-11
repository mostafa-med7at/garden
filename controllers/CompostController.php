<?php
require_once __DIR__ . '/../core/Controller.php';

class CompostController extends Controller {
    public function index($user) {
        $model = $this->model('CompostModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_compost'])) {
            $amount = max(0, (float)$_POST['amount_kg']);
            $notes  = trim($_POST['notes'] ?? '');
            
            $model->logContribution($user['id'], $amount, $notes);
            auditLog('compost_logged','land','compost_contributions',null,"$amount kg by user {$user['id']}");
            
            setFlash('success', $amount == 0 ? 'Zero contribution recorded.' : "Logged {$amount} kg of green waste. Thanks!");
            header('Location: compost.php'); 
            exit;
        }

        $myTotal = $model->getMyTotal($user['id']);
        $leaderboard = $model->getLeaderboard(10);
        $history = $model->getMyHistory($user['id'], 20);
        $grandTotal = $model->getGrandTotal();

        $this->view('land_compost', [
            'user' => $user,
            'myTotal' => $myTotal,
            'leaderboard' => $leaderboard,
            'history' => $history,
            'grandTotal' => $grandTotal,
            'pageTitle' => 'Compost Tracker'
        ]);
    }
}
