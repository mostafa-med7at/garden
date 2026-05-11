<?php
require_once __DIR__ . '/../core/Controller.php';

class LeaseController extends Controller {
    public function index($user) {
        $model = $this->model('LeaseModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['run_expiry']) && $user['role_name'] === 'admin') {
                $expired = $model->expireOverdueLeases();
                auditLog('lease_expiry_run', 'land', 'leases', null, "$expired leases expired");
                setFlash('info', "$expired lease(s) marked expired and plots freed.");
                header('Location: leases.php'); 
                exit;
            }

            if (isset($_POST['renew'])) {
                $leaseId = (int)$_POST['lease_id'];
                $method  = $_POST['payment_method'];
                
                $lease = $model->getLeaseByIdAndUser($leaseId, $user['id']);
                if ($lease) {
                    $fee    = calculateRentalFee($lease['area_sqm'], $lease['soil_quality'], $user['membership']);
                    $newEnd = date('Y-m-d', strtotime($lease['end_date'] . ' +1 year'));
                    
                    $model->renewLease($leaseId, $user['id'], $fee['total_fee'], $method, $newEnd);
                    
                    auditLog('lease_renewed', 'land', 'leases', $leaseId, "Renewed to $newEnd, £{$fee['total_fee']}");
                    setFlash('success', "Lease renewed to $newEnd. Payment of £{$fee['total_fee']} recorded.");
                }
                header('Location: leases.php'); 
                exit;
            }

            if (isset($_POST['terminate']) && $user['role_name'] === 'admin') {
                $leaseId = (int)$_POST['lease_id'];
                $model->terminateLease($leaseId);
                auditLog('lease_terminated', 'land', 'leases', $leaseId, 'Admin terminated lease');
                setFlash('warning', 'Lease terminated. Plot is now available.');
                header('Location: leases.php'); 
                exit;
            }
        }

        if ($user['role_name'] === 'admin' || $user['role_name'] === 'warden') {
            $leases = $model->getAllLeases();
        } else {
            $leases = $model->getUserLeases($user['id']);
        }

        $this->view('land_leases', [
            'user' => $user,
            'leases' => $leases,
            'pageTitle' => 'Lease Management'
        ]);
    }

    public function create($user) {
        $model = $this->model('LeaseModel');
        $plotId = (int)($_GET['plot_id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $plotId  = (int)$_POST['plot_id'];
            $method  = $_POST['payment_method'];
            $years   = max(1, (int)($_POST['duration_years'] ?? 1));
            $userId  = $user['role_name'] === 'admin' ? (int)$_POST['member_id'] : $user['id'];

            $plot = $model->getPlotByIdAvailable($plotId);

            if (!$plot) { 
                setFlash('danger','Plot not available.'); 
                header('Location: lease_create.php'); 
                exit; 
            }

            $member = $model->getUserById($userId);
            if (!$member) {
                setFlash('danger', 'Invalid member.');
                header('Location: lease_create.php');
                exit;
            }

            $fee      = calculateRentalFee($plot['area_sqm'], $plot['soil_quality'], $member['membership_status']);
            $start    = date('Y-m-d');
            $end      = date('Y-m-d', strtotime("+$years year"));
            $total    = $fee['total_fee'] * $years;

            $leaseId = $model->createLease(
                $plotId, $userId, $start, $end, 
                $fee['base_fee'], $fee['multiplier'], $fee['discount_pct']/100, 
                $total, $method
            );

            auditLog('lease_created','land','leases',$leaseId,"Plot {$plot['plot_code']} rented to user $userId");
            setFlash('success',"Plot {$plot['plot_code']} rented successfully! £".number_format($total,2)." payment recorded.");
            header('Location: leases.php'); 
            exit;
        }

        $available = $model->getAvailablePlots();
        $members = [];
        if ($user['role_name'] === 'admin') {
            $members = $model->getMembersForAdmin();
        }

        $this->view('land_lease_create', [
            'user' => $user,
            'available' => $available,
            'members' => $members,
            'plotId' => $plotId,
            'pageTitle' => 'Rent a Plot'
        ]);
    }
}
