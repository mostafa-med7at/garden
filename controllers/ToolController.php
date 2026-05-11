<?php
require_once __DIR__ . '/../core/Controller.php';

class ToolController extends Controller {
    public function index($user) {
        $model = $this->model('ToolModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['change_state']) && hasPermission('resources','edit')) {
                $toolId   = (int)$_POST['tool_id'];
                $newState = $_POST['new_state'];
                $notes    = trim($_POST['state_notes'] ?? '');
                $old      = $model->getToolStatus($toolId);
                
                $model->updateToolStatus($toolId, $newState);
                $model->logToolStateChange($toolId, $user['id'], $old, $newState, $notes);
                
                auditLog('tool_state_changed','resources','tools',$toolId,"$old → $newState");
                setFlash('success',"Tool state updated to: $newState");
                header('Location: tools.php'); exit;
            }

            if (isset($_POST['reserve'])) {
                $toolId    = (int)$_POST['tool_id'];
                $slotDate  = $_POST['slot_date'];
                $slotStart = $_POST['slot_start'];
                $slotEnd   = $_POST['slot_end'];
                
                if ($model->checkReservationConflict($toolId, $slotDate, $slotStart, $slotEnd)) {
                    setFlash('danger','That time slot is already booked. Choose another slot.');
                } else {
                    $due = $slotDate . ' ' . $slotEnd;
                    $resId = $model->createReservation($toolId, $user['id'], $slotDate, $slotStart, $slotEnd, $due);
                    $model->updateToolStatus($toolId, 'checked_out');
                    $model->logToolStateChange($toolId, $user['id'], 'available', 'checked_out', 'Reserved by member');
                    
                    auditLog('tool_reserved','resources','tool_reservations', $resId, "Tool $toolId reserved for $slotDate");
                    setFlash('success','Tool reserved successfully!');
                }
                header('Location: tools.php'); exit;
            }

            if (isset($_POST['cancel_reservation'])) {
                $resId = (int)$_POST['reservation_id'];
                $res = $model->getReservation($resId, $user['id']);
                if ($res) {
                    $model->cancelReservation($resId);
                    $model->updateToolStatus($res['tool_id'], 'available');
                    $model->logToolStateChange($res['tool_id'], $user['id'], 'checked_out', 'available', 'Reservation cancelled');
                    auditLog('reservation_cancelled', 'resources', 'tool_reservations', $resId, "Cancelled reservation $resId");
                    setFlash('success', 'Reservation cancelled.');
                }
                header('Location: tools.php'); exit;
            }

            if (isset($_POST['reschedule_reservation'])) {
                $resId     = (int)$_POST['reservation_id'];
                $slotDate  = $_POST['slot_date'];
                $slotStart = $_POST['slot_start'];
                $slotEnd   = $_POST['slot_end'];
                
                $res = $model->getReservation($resId, $user['id']);
                if ($res) {
                    if ($model->checkReservationConflict($res['tool_id'], $slotDate, $slotStart, $slotEnd, $resId)) {
                        setFlash('danger', 'That time slot is already booked. Choose another slot.');
                    } else {
                        $due = $slotDate . ' ' . $slotEnd;
                        $model->rescheduleReservation($resId, $slotDate, $slotStart, $slotEnd, $due);
                        auditLog('reservation_rescheduled', 'resources', 'tool_reservations', $resId, "Rescheduled to $slotDate");
                        setFlash('success', 'Reservation rescheduled successfully!');
                    }
                }
                header('Location: tools.php'); exit;
            }

            if (isset($_POST['return_tool'])) {
                $resId = (int)$_POST['reservation_id'];
                $res = $model->getReservation($resId, $user['id']);
                if ($res) {
                    $now = date('Y-m-d H:i:s');
                    $penalty = calculateLatePenalty($res['due_date'], $now);
                    
                    $model->returnTool($resId, $res['tool_id']);
                    $model->logToolStateChange($res['tool_id'], $user['id'], 'checked_out', 'available', 'Returned');
                    
                    $tool = $model->getToolById($res['tool_id']);
                    if ($tool['total_usage_hours'] >= $tool['maintenance_threshold_hours']) {
                        $model->markToolMaintenance($res['tool_id']);
                    }
                    
                    if ($penalty['days_late'] > 0) {
                        $model->addPenalty($resId, $user['id'], $penalty['days_late'], $penalty['fine_amount'], $penalty['service_hours']);
                        auditLog('tool_returned_late','resources','tool_penalties',null,"{$penalty['days_late']} days late — £{$penalty['fine_amount']}");
                        setFlash('warning',"Tool returned {$penalty['days_late']} day(s) late. Fine: £{$penalty['fine_amount']}");
                    } else {
                        auditLog('tool_returned','resources','tool_reservations',$resId,'On time');
                        setFlash('success','Tool returned on time. Thank you!');
                    }
                }
                header('Location: tools.php'); exit;
            }

            if (isset($_POST['report_damage'])) {
                $toolId = (int)$_POST['damage_tool_id'];
                $desc   = trim($_POST['damage_desc']);
                
                $repId = $model->reportDamage($toolId, $user['id'], $desc);
                $model->updateToolStatus($toolId, 'in_repair');
                $model->logToolStateChange($toolId, $user['id'], 'checked_out', 'in_repair', 'Damage reported');
                
                auditLog('damage_reported','resources','damage_reports',$repId,"Tool $toolId damage report");
                setFlash('warning','Damage report submitted. Admin will review.');
                header('Location: tools.php'); exit;
            }

            if (isset($_POST['review_damage']) && $user['role_name']==='admin') {
                $repId   = (int)$_POST['report_id'];
                $type    = $_POST['damage_type'];
                $fee     = (float)($_POST['repair_fee'] ?? 0);
                $exempt  = ($type==='natural_wear') ? 1 : 0;
                
                $model->reviewDamage($repId, $type, $fee, $exempt, $user['id']);
                $toolId2 = $model->getDamageReportToolId($repId);
                $model->updateToolStatus($toolId2, 'available');
                
                auditLog('damage_reviewed','resources','damage_reports',$repId,"$type, fee: $fee");
                setFlash('success','Damage report reviewed. Tool returned to available.');
                header('Location: tools.php'); exit;
            }

            if (isset($_POST['add_tool']) && $user['role_name']==='admin') {
                $mediaLinks = trim($_POST['media_links'] ?? '');

                if (isset($_FILES['tool_image']) && $_FILES['tool_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../assets/uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    $fileName = time() . '_' . basename($_FILES['tool_image']['name']);
                    if (move_uploaded_file($_FILES['tool_image']['tmp_name'], $uploadDir . $fileName)) {
                        $mediaLinks = 'assets/uploads/' . $fileName; 
                    }
                }

                $newId = $model->addTool(trim($_POST['tool_name']), trim($_POST['tool_desc']), (float)$_POST['threshold'], $mediaLinks);
                auditLog('tool_added','resources','tools',$newId,$_POST['tool_name']);
                setFlash('success','Tool added to library.');
                header('Location: tools.php'); exit;
            }
        }

        $tools = $model->getAllTools();
        $myRes = $model->getMyReservations($user['id']);
        $penalties = $model->getPendingPenalties($user['id']);
        $damageRep = $model->getAllDamageReports();

        $this->view('resources_tools', [
            'user' => $user,
            'tools' => $tools,
            'myRes' => $myRes,
            'penalties' => $penalties,
            'damageRep' => $damageRep,
            'pageTitle' => 'Tool Library'
        ]);
    }
}
