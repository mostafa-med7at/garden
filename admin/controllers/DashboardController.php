<?php
require_once __DIR__ . '/../core/Controller.php';

class DashboardController extends Controller {
    public function index($user) {
        $model = $this->model('DashboardModel');
        
        $stats = $model->getQuickStats();
        $myLease = $model->getMyLease($user['id'], $user['role_name']);
        $broadcasts = $model->getRecentBroadcasts();

        $this->view('dashboard', [
            'user' => $user,
            'stats' => $stats,
            'myLease' => $myLease,
            'broadcasts' => $broadcasts,
            'pageTitle' => 'Dashboard'
        ]);
    }
}
