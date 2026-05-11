<?php
require_once __DIR__ . '/../core/Controller.php';

class IncidentController extends Controller {
    public function index($user) {
        $model = $this->model('IncidentModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Fn 22: Gate access
            if (isset($_POST['gate_access'])) {
                $code       = trim($_POST['gate_code']);
                $accessType = $_POST['access_type'] ?? 'entry';
                
                $valid = $model->logGateAccess($code, $accessType);
                
                if ($valid) { 
                    setFlash('success','✅ Gate access GRANTED.'); 
                } else { 
                    setFlash('danger','❌ Invalid gate code. Access DENIED. Attempt logged.'); 
                }
                header('Location: incidents.php'); 
                exit;
            }

            // Fn 23: Report incident
            if (isset($_POST['report_incident'])) {
                $severity = $_POST['severity'];
                $status   = ($severity === 'critical') ? 'in_process' : 'open';
                
                $incId = $model->reportIncident(
                    $user['id'], 
                    trim($_POST['title']), 
                    trim($_POST['description']), 
                    trim($_POST['location']), 
                    $severity, 
                    $status
                );
                
                auditLog('incident_reported','volunteer','incidents',$incId,"Severity: $severity");
                if ($severity==='critical') {
                    auditLog('critical_alert','volunteer','incidents',$incId,'CRITICAL — auto-escalated to IN_PROCESS, all admins alerted');
                    setFlash('danger',"🚨 CRITICAL incident reported! Auto-escalated. Admins have been alerted.");
                } else {
                    setFlash('warning','Incident reported. Status: OPEN. Admin will review.');
                }
                header('Location: incidents.php'); 
                exit;
            }

            // Update incident status (admin)
            if (isset($_POST['update_incident']) && $user['role_name']==='admin') {
                $incId  = (int)$_POST['incident_id'];
                $status = $_POST['new_status'];
                
                $model->updateIncidentStatus($incId, $status, $user['id']);
                
                auditLog('incident_updated','volunteer','incidents',$incId,"Status → $status");
                setFlash('success',"Incident status updated to: $status"); 
                header('Location: incidents.php'); 
                exit;
            }
        }

        $incidents = $model->getAllIncidents();
        $accessLogs = $user['role_name'] === 'admin' ? $model->getAccessLogs(30) : [];

        $this->view('volunteer_incidents', [
            'user' => $user,
            'incidents' => $incidents,
            'accessLogs' => $accessLogs,
            'pageTitle' => 'Security & Incidents'
        ]);
    }
}
