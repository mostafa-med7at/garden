<?php
// controllers/ReportController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ReportModel.php';

class ReportController extends Controller {

    private $model;

    public function __construct() {
        $this->model = new ReportModel();
    }

    public function index($user) {
        if (!in_array($user['role_name'], ['admin', 'warden'])) {
            header('Location: /garden/index.php');
            exit;
        }

        $report = $_GET['report'] ?? '';
        $from   = $_GET['from'] ?? date('Y-m-01');
        $to     = $_GET['to']   ?? date('Y-m-d');
        
        $data = [];
        if ($report === 'members') {
            $data = $this->model->getMembersReport();
        } elseif ($report === 'leases') {
            $data = $this->model->getLeasesReport($from, $to);
        } elseif ($report === 'billing') {
            $data = $this->model->getBillingReport($from, $to);
        } elseif ($report === 'tools') {
            $data = $this->model->getToolsReport();
        } elseif ($report === 'incidents') {
            $data = $this->model->getIncidentsReport($from, $to);
        } elseif ($report === 'volunteers') {
            $data = $this->model->getVolunteersReport($from, $to);
        } elseif ($report === 'waitlist') {
            $data = $this->model->getWaitlistReport();
        } elseif ($report === 'audit') {
            $data = $this->model->getAuditReport($from, $to);
        }

        $this->view('reports_index', [
            'user' => $user,
            'pageTitle' => 'Reports',
            'report' => $report,
            'from' => $from,
            'to' => $to,
            'data' => $data
        ]);
    }

    public function export($user) {
        if (!in_array($user['role_name'], ['admin', 'warden'])) {
            header('Location: /garden/index.php');
            exit;
        }

        $report = $_GET['report'] ?? '';
        $from   = $_GET['from'] ?? date('Y-m-01');
        $to     = $_GET['to']   ?? date('Y-m-d');
        
        $rows = [];
        $headers = [];
        $filename = "report_{$report}_" . date('Ymd') . '.csv';

        switch ($report) {
            case 'members':
                $headers = ['ID','Full Name','Email','Phone','Role','Membership','Community Points','Karma Points','Status','Joined'];
                $rows = $this->model->getMembersExport();
                break;
            case 'leases':
                $headers = ['Plot','Leaseholder','Email','Area m²','Start','End','Base Fee £','Total Fee £','Status'];
                $rows = $this->model->getLeasesExport($from, $to);
                break;
            case 'billing':
                $headers = ['Date','Member','Email','Plot','Amount £','Method','Status','Notes'];
                $rows = $this->model->getBillingExport($from, $to);
                break;
            case 'tools':
                $headers = ['Tool','Status','Usage Hours','Threshold Hours','Total Reservations','Completed','Overdue','Needs Maintenance'];
                $rows = $this->model->getToolsExport();
                break;
            case 'incidents':
                $headers = ['Date','Title','Location','Severity','Reported By','Status','Resolved By','Resolved At'];
                $rows = $this->model->getIncidentsExport($from, $to);
                break;
            case 'volunteers':
                $headers = ['Member','Email','Total Hours','Entries','Status vs Requirement'];
                $rows = $this->model->getVolunteersExport($from, $to, MONTHLY_SERVICE_HOURS);
                break;
            case 'waitlist':
                $headers = ['Position','Member','Email','Priority Score','Community Points','Residency Months','Status','Joined'];
                $rows = $this->model->getWaitlistExport();
                break;
            case 'audit':
                $headers = ['Time','User','Action','Module','Target Table','Target ID','Description','IP'];
                $rows = $this->model->getAuditExport($from, $to);
                break;
            default:
                header('Location: index.php');
                exit;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [APP_NAME . ' — ' . ucfirst($report) . ' Report', 'Period: ' . $from . ' to ' . $to, 'Generated: ' . date('d M Y H:i')]);
        fputcsv($out, []);
        fputcsv($out, $headers);

        foreach ($rows as $row) {
            fputcsv($out, array_map(fn($v) => $v ?? '', $row));
        }

        fputcsv($out, []);
        fputcsv($out, ['Total rows: ' . count($rows)]);
        fclose($out);
        exit;
    }
}
