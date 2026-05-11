<?php
require_once __DIR__ . '/../core/Controller.php';

class PlotController extends Controller {
    public function index($user) {
        $model = $this->model('PlotModel');
        
        $plots = $model->getAllPlots();
        
        $maxX = 1; $maxY = 1;
        foreach ($plots as $p) {
            if ($p['grid_x'] > $maxX) $maxX = $p['grid_x'];
            if ($p['grid_y'] > $maxY) $maxY = $p['grid_y'];
        }
        $gridCols = max($maxX, 6);
        $gridRows = max($maxY, 5);

        $gridMap = [];
        foreach ($plots as $p) {
            if ($p['grid_x'] && $p['grid_y']) {
                $gridMap[$p['grid_y']][$p['grid_x']] = $p;
            }
        }

        $onWaitlist = $user ? $model->isUserOnWaitlist($user['id']) : false;

        $calcResult = null;
        if ($user && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calc_fee'])) {
            $plotId = (int)$_POST['plot_id'];
            $plot = $model->getPlotById($plotId);
            if ($plot) {
                $calcResult = calculateRentalFee(
                    $plot['area_sqm'],
                    $plot['soil_quality'],
                    $user['membership'] ?? $user['membership_status'] ?? 'standard'
                );
                $calcResult['plot_code']   = $plot['plot_code'];
                $calcResult['area_sqm']    = $plot['area_sqm'];
                $calcResult['soil_quality']= $plot['soil_quality'];
                $calcResult['plot_id']     = $plot['id'];
            }
        }

        $this->view('land_plots', [
            'user' => $user,
            'plots' => $plots,
            'gridCols' => $gridCols,
            'gridRows' => $gridRows,
            'gridMap' => $gridMap,
            'onWaitlist' => $onWaitlist,
            'calcResult' => $calcResult,
            'pageTitle' => 'Garden Plot Map'
        ]);
    }

    public function detail($user) {
        $model = $this->model('PlotModel');
        $id = (int)($_GET['id'] ?? 0);
        $plot = $model->getPlotDetail($id);

        if (!$plot) { 
            setFlash('danger','Plot not found.'); 
            redirect('modules/land/plots.php'); 
        }

        $soilEvents = $model->getSoilEvents($id);
        $pestReports = $model->getPestReports($id);
        $inspections = $model->getInspections($id);

        $this->view('land_plot_detail', [
            'user' => $user,
            'id' => $id,
            'plot' => $plot,
            'soilEvents' => $soilEvents,
            'pestReports' => $pestReports,
            'inspections' => $inspections,
            'pageTitle' => 'Plot Details'
        ]);
    }

    public function create($user) {
        if (!hasPermission('land','create')) {
            setFlash('danger','Permission denied.');
            redirect('modules/land/plots.php');
        }

        $model = $this->model('PlotModel');
        $existingPlots = $model->getExistingGridPlots();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code     = strtoupper(trim($_POST['plot_code']    ?? ''));
            $sunlight = $_POST['sunlight_level'] ?? 'full';
            $soil     = $_POST['soil_quality']   ?? 'standard';
            $area     = (float)($_POST['area_sqm']  ?? 0);
            $gridX    = (int)($_POST['grid_x']   ?? 0);
            $gridY    = (int)($_POST['grid_y']   ?? 0);

            if (!$code)        $errors[] = 'Plot code is required.';
            if ($gridX < 1)   $errors[] = 'Grid column (X) must be ≥ 1.';
            if ($gridY < 1)   $errors[] = 'Grid row (Y) must be ≥ 1.';
            if ($area  <= 0)  $errors[] = 'Area must be greater than 0.';

            if (!$errors && $model->isPlotCodeTaken($code)) {
                $errors[] = "Plot code '$code' already exists.";
            }
            if (!$errors && $model->isGridCellTaken($gridX, $gridY)) {
                $errors[] = "Grid cell ($gridX, $gridY) is already taken by another plot.";
            }

            if (!$errors) {
                $plotId = $model->createPlot($code, $area, $sunlight, $soil, $gridX, $gridY);
                auditLog('plot_created','land','plots',$plotId,"Code: $code, {$area}m², grid($gridX,$gridY)");
                setFlash('success',"Plot $code added at grid position ($gridX, $gridY). Area: {$area}m²");
                header('Location: plots.php'); 
                exit;
            }
        }

        $pickerCols = 10;
        $pickerRows = 8;
        $takenCells = [];
        foreach ($existingPlots as $ep) {
            $takenCells[$ep['grid_y']][$ep['grid_x']] = $ep;
        }

        $this->view('land_plot_create', [
            'user' => $user,
            'errors' => $errors,
            'pickerCols' => $pickerCols,
            'pickerRows' => $pickerRows,
            'takenCells' => $takenCells,
            'pageTitle' => 'Add New Plot'
        ]);
    }

    public function pingSensor() {
        header('Content-Type: application/json');
        
        $plotId = (int)($_GET['id'] ?? 0);
        
        if (!$plotId) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Plot ID']);
            exit;
        }
        
        // Simulate fetching live sensor data from IoT device
        $moisture = rand(30, 80);
        $temp = rand(15, 30);
        
        echo json_encode([
            'status' => 'success',
            'message' => "Sensor OK: Moisture {$moisture}%, Temp {$temp}°C"
        ]);
        exit;
    }
}
