<?php
require_once __DIR__ . '/../core/Controller.php';

class PestReportController extends Controller {
    public function index($user) {
        $model = $this->model('PestReportModel');

        $myPlot = $model->getMyPlot($user['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['report_pest'])) {
                $plotId    = (int)$_POST['plot_id'];
                $pestType  = trim($_POST['pest_type']);
                $severity  = $_POST['severity'];
                $desc      = trim($_POST['description']);
                $transmit  = isset($_POST['is_transmissible']) ? 1 : 0;

                $reportId = $model->createReport($plotId, $user['id'], $pestType, $severity, $transmit, $desc);
                auditLog('pest_reported', 'land', 'pest_reports', $reportId, "$pestType on plot $plotId");

                if ($transmit) {
                    $code = $model->getPlotCode($plotId);
                    $prefix = substr($code, 0, 1);
                    $neighborList = $model->getNeighborPlots($plotId, $prefix);

                    foreach ($neighborList as $n) {
                        auditLog('pest_neighbor_alert', 'land', 'pest_reports', $reportId,
                            "Alert sent to {$n['full_name']} (plot {$n['plot_code']}) re transmissible {$pestType}");
                    }

                    $cnt = count($neighborList);
                    setFlash('warning', "⚠️ Transmissible pest reported. $cnt neighboring plot owner(s) have been notified.");
                } else {
                    setFlash('success', 'Pest/disease report submitted.');
                }
                header('Location: pest_report.php'); 
                exit;
            }

            if (isset($_POST['submit_inspection']) && in_array($user['role_name'],['admin','warden'])) {
                $plotId  = (int)$_POST['insp_plot_id'];
                $notes   = trim($_POST['insp_notes']);
                $result  = $_POST['insp_result'];
                $violDet = trim($_POST['violation_details'] ?? '');
                $penalty = (float)($_POST['penalty_applied'] ?? 0);

                $photoPaths = [];
                if (!empty($_FILES['photos']['name'][0])) {
                    $uploadDir = APP_ROOT . '/assets/uploads/inspections/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
                        if ($_FILES['photos']['error'][$i] === 0) {
                            $ext  = pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION);
                            $name = 'insp_' . time() . '_' . $i . '.' . $ext;
                            move_uploaded_file($tmp, $uploadDir . $name);
                            $photoPaths[] = 'assets/uploads/inspections/' . $name;
                        }
                    }
                }

                $inspId = $model->createInspection($plotId, $user['id'], $notes, $photoPaths, $result, $violDet, $penalty);

                $compStatus = ['pass'=>'compliant','warning'=>'warning','fail'=>'violation'][$result] ?? 'compliant';
                $model->updateCompliance($plotId, $compStatus);

                auditLog('inspection_completed', 'land', 'inspections', $inspId, "Plot $plotId: $result");
                setFlash('success', 'Inspection recorded. Plot compliance updated to: ' . $compStatus);
                header('Location: pest_report.php'); 
                exit;
            }
        }

        $allPlots = $model->getAllPlots();
        $reports = $model->getAllReports();
        $inspections = $model->getAllInspections();

        $this->view('land_pest_report', [
            'user' => $user,
            'myPlot' => $myPlot,
            'allPlots' => $allPlots,
            'reports' => $reports,
            'inspections' => $inspections,
            'pageTitle' => 'Pest & Disease Reports'
        ]);
    }
}
