<?php
require_once __DIR__ . '/../core/Controller.php';

class WaitlistController extends Controller {
    public function index($user) {
        $model = $this->model('WaitlistModel');

        // Handle POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['join'])) {
                if ($model->isUserOnWaitlist($user['id'])) {
                    setFlash('warning', 'You are already on the waitlist.');
                } else {
                    $residency = (int)($user['residency_months'] ?? 0);
                    $points    = (int)($user['community_points'] ?? $user['points'] ?? 0);
                    $score     = calculatePriorityScore($points, $residency);
                    $lastId    = $model->joinWaitlist($user['id'], $score);
                    auditLog('waitlist_joined', 'land', 'waitlist', (int)$lastId);
                    setFlash('success', 'You joined the waitlist! Priority score: ' . $score);
                }
                header('Location: waitlist.php'); exit;
            }

            if (isset($_POST['leave'])) {
                if ($model->isUserOnWaitlist($user['id'])) {
                    $model->leaveWaitlist($user['id']);
                    auditLog('waitlist_left', 'land', 'waitlist', $user['id']);
                    setFlash('info', 'You have been removed from the waitlist.');
                }
                header('Location: waitlist.php'); exit;
            }

            if (isset($_POST['notify_top']) && $user['role_name'] === 'admin') {
                $top = $model->getTopWaitingMember();
                if ($top) {
                    $model->notifyTopMember($top['id']);
                    setFlash('success', "Notified {$top['full_name']} ({$top['email']}) — highest priority.");
                } else {
                    setFlash('info', 'No members currently waiting.');
                }
                header('Location: waitlist.php'); exit;
            }

            if (isset($_POST['respond'])) {
                $response = in_array($_POST['respond'], ['accepted','declined']) ? $_POST['respond'] : 'declined';
                $model->respond($user['id'], $response);
                setFlash($response === 'accepted' ? 'success' : 'info',
                    $response === 'accepted' ? 'Great! An admin will assign you a plot shortly.' : 'You declined. The next member will be notified.');
                header('Location: waitlist.php'); exit;
            }
        }

        // Fetch data for view
        $waitlistItems = $model->getAllWaitlistItems();
        $myEntry = null;
        $myPos = null;
        foreach ($waitlistItems as $i => $item) {
            if ($item['user_id'] == $user['id']) { 
                $myEntry = $item; 
                $myPos = $i + 1; 
                break; 
            }
        }
        $availableCount = $model->getAvailablePlotsCount();

        $this->view('land_waitlist', [
            'user' => $user,
            'waitlistItems' => $waitlistItems,
            'myEntry' => $myEntry,
            'myPos' => $myPos,
            'availableCount' => $availableCount,
            'pageTitle' => 'Plot Waitlist'
        ]);
    }
}
