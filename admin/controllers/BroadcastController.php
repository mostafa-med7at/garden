<?php
require_once __DIR__ . '/../core/Controller.php';

class BroadcastController extends Controller {
    public function index($user) {
        if ($user['role_name'] !== 'admin') { 
            setFlash('danger','Admin only.'); 
            redirect('index.php'); 
        }

        $model = $this->model('BroadcastModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['broadcast'])) {
            $title       = trim($_POST['title']);
            $message     = trim($_POST['message']);
            $siteStatus  = $_POST['site_status'];
            $affectedRaw = trim($_POST['affected_plots'] ?? '');
            $affected    = $affectedRaw ? json_encode(array_map('trim', explode(',', $affectedRaw))) : null;

            if ($_POST['is_false_alarm'] ?? '' === '1') {
                $broadId = $model->logFalseAlarm($user['id'], $title, $message, $siteStatus);
                auditLog('broadcast_false_alarm','volunteer','broadcasts', $broadId,'Cancelled before send');
                setFlash('info','Marked as false alarm. No members notified.');
            } else {
                $broadId = $model->sendBroadcast($user['id'], $title, $message, $affected, $siteStatus);
                
                if ($siteStatus === 'closed' || $siteStatus === 'emergency') {
                    if ($affectedRaw) {
                        $codes = array_map('trim', explode(',', $affectedRaw));
                        $model->updatePlotStatus($codes, 'maintenance');
                    }
                }
                
                auditLog('broadcast_sent','volunteer','broadcasts',$broadId,"Status: $siteStatus");
                
                $memberCount = $model->getActiveMemberCount();
                setFlash('success',"Broadcast sent to $memberCount members. Site status set to: $siteStatus");
            }
            header('Location: broadcast.php'); 
            exit;
        }

        $broadcasts = $model->getRecentBroadcasts(20);

        $this->view('volunteer_broadcast', [
            'user' => $user,
            'broadcasts' => $broadcasts,
            'pageTitle' => 'Emergency Broadcast'
        ]);
    }
}
