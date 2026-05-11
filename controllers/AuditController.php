<?php
require_once __DIR__ . '/../core/Controller.php';

class AuditController extends Controller {
    public function index($user) {
        if ($user['role_name'] !== 'admin') { 
            setFlash('danger', 'Admin access required.'); 
            redirect('index.php'); 
        }

        $model = $this->model('AuditModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['change_role'])) {
                $targetId = (int)$_POST['user_id'];
                $newRole  = (int)$_POST['role_id'];
                
                if ($targetId === $user['id']) { 
                    setFlash('danger', "You can't change your own role."); 
                    header('Location: audit.php'); 
                    exit; 
                }
                
                $model->changeUserRole($targetId, $newRole);
                auditLog('role_changed', 'admin', 'users', $targetId, "Role changed to role_id=$newRole");
                setFlash('success', 'User role updated.'); 
                header('Location: audit.php'); 
                exit;
            }

            if (isset($_POST['toggle_active'])) {
                $targetId = (int)$_POST['user_id'];
                
                if ($targetId === $user['id']) { 
                    setFlash('danger', "Can't deactivate yourself."); 
                    header('Location: audit.php'); 
                    exit; 
                }
                
                $model->toggleUserActive($targetId);
                auditLog('user_status_toggled', 'admin', 'users', $targetId, 'Active status toggled');
                setFlash('success', 'User status updated.'); 
                header('Location: audit.php'); 
                exit;
            }
        }

        $filterAction = trim($_GET['action'] ?? '');
        $filterUser   = trim($_GET['user_id'] ?? '');
        $filterDate   = trim($_GET['date'] ?? '');
        $filterModule = trim($_GET['module'] ?? '');

        $auditLogs = $model->getAuditLogs($filterAction, $filterUser, $filterDate, $filterModule);
        $allUsers = $model->getAllUsersWithRoles();
        $allRoles = $model->getAllRoles();
        $stats = $model->getAuditStats();

        $this->view('land_audit', [
            'user' => $user,
            'auditLogs' => $auditLogs,
            'allUsers' => $allUsers,
            'allRoles' => $allRoles,
            'stats' => $stats,
            'filters' => [
                'action' => $filterAction,
                'user_id' => $filterUser,
                'date' => $filterDate,
                'module' => $filterModule
            ],
            'pageTitle' => 'Admin Panel — RBAC & Audit Trail'
        ]);
    }
}
