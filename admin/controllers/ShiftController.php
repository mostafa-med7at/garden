<?php
require_once __DIR__ . '/../core/Controller.php';

class ShiftController extends Controller {
    public function index($user) {
        $model = $this->model('ShiftModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['create_shift']) && $user['role_name']==='admin') {
                $assignTo = (int)$_POST['assigned_to'];
                $shiftId = $model->createShift(trim($_POST['title']), $_POST['shift_date'], $_POST['start_time'], $_POST['end_time'], $assignTo);
                
                auditLog('shift_created','volunteer','shifts', $shiftId, $_POST['title']);
                setFlash('success','Shift created.'); 
                header('Location: shifts.php'); 
                exit;
            }

            if (isset($_POST['request_swap'])) {
                $shiftId  = (int)$_POST['shift_id'];
                $targetId = (int)$_POST['target_member'];
                $expires  = date('Y-m-d H:i:s', strtotime('+48 hours'));
                
                if ($model->checkPendingSwap($shiftId, $user['id'])) {
                    setFlash('warning','Swap request already pending for this shift.');
                } else {
                    $model->requestSwap($shiftId, $user['id'], $targetId, $expires);
                    auditLog('swap_requested','volunteer','shift_swap_requests',null,"Shift $shiftId: user {$user['id']} → $targetId");
                    setFlash('info','Swap request sent. They have 48 hours to respond.');
                }
                header('Location: shifts.php'); 
                exit;
            }

            if (isset($_POST['respond_swap'])) {
                $reqId    = (int)$_POST['request_id'];
                $response = $_POST['response'];
                
                $req = $model->getSwapRequest($reqId, $user['id']);
                if ($req) {
                    $model->respondToSwap($reqId, $response);
                    
                    if ($response === 'accepted') {
                        $model->swapShiftAssignment($req['shift_id'], $user['id']);
                        auditLog('swap_accepted','volunteer','shifts',$req['shift_id'],"Shift reassigned to user {$user['id']}");
                        setFlash('success','Swap accepted! The shift is now yours.');
                    } else {
                        setFlash('info','Swap declined. The original member retains the shift.');
                    }
                }
                header('Location: shifts.php'); 
                exit;
            }
        }

        $shifts = $model->getAllShifts();
        $members = $model->getActiveMembers();
        $mySwapReqs = $model->getIncomingSwapRequests($user['id']);

        $this->view('volunteer_shifts', [
            'user' => $user,
            'shifts' => $shifts,
            'members' => $members,
            'mySwapReqs' => $mySwapReqs,
            'pageTitle' => 'Shift Schedule'
        ]);
    }
}
