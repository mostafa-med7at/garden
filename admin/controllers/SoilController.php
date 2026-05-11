<?php
require_once __DIR__ . '/../core/Controller.php';

class SoilController extends Controller {
    public function index($user) {
        $model = $this->model('SoilModel');

        $myPlots = $model->getMyPlots($user['id']);

        if (in_array($user['role_name'], ['admin','warden'])) {
            $allPlots = $model->getAllPlots();
        } else {
            $allPlots = $myPlots;
        }

        $selectedPlotId = (int)($_GET['plot_id'] ?? ($allPlots[0]['id'] ?? 0));

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
            $plotId      = (int)$_POST['plot_id'];
            $eventType   = $_POST['event_type'];
            $fertType    = trim($_POST['fertilizer_type'] ?? '');
            $ph          = $_POST['ph_level'] !== '' ? (float)$_POST['ph_level'] : null;
            $crop        = trim($_POST['crop_name'] ?? '');
            $notes       = trim($_POST['notes'] ?? '');
            $atRisk      = isSoilAtRisk($ph) ? 1 : 0;

            $eventId = $model->addSoilEvent($plotId, $user['id'], $eventType, $fertType, $ph, $crop, $notes, $atRisk);
            auditLog('soil_event_added', 'land', 'soil_events', $eventId, "Plot $plotId: $eventType");

            if ($atRisk) {
                setFlash('warning', "⚠️ Soil pH $ph is outside the safe range (" . SOIL_PH_MIN . "–" . SOIL_PH_MAX . "). Consider amending your soil.");
            } else {
                setFlash('success', 'Soil event recorded successfully.');
            }
            header("Location: soil.php?plot_id=$plotId");
            exit;
        }

        $events = [];
        if ($selectedPlotId) {
            $events = $model->getEventsByPlot($selectedPlotId);
        }

        $this->view('land_soil', [
            'user' => $user,
            'allPlots' => $allPlots,
            'selectedPlotId' => $selectedPlotId,
            'events' => $events,
            'pageTitle' => 'Soil Health Tracker'
        ]);
    }
}
