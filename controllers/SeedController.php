<?php
// controllers/SeedController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/SeedModel.php';

class SeedController extends Controller {

    private $model;

    public function __construct() {
        $this->model = new SeedModel();
    }

    public function index($user) {
        // Handle POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Add seed
            if (isset($_POST['add_seed']) && hasPermission('resources','create')) {
                $seedId = $this->model->addSeed(
                    trim($_POST['name']), trim($_POST['variety']), (int)$_POST['quantity_packets'],
                    $_POST['stored_date'], (int)$_POST['expiry_months'],
                    trim($_POST['allergen_category'] ?? ''), trim($_POST['parent_plant_notes'] ?? ''), $user['id']
                );
                auditLog('seed_added','resources','seeds',$seedId,$_POST['name']);
                setFlash('success','Seed batch added to the bank.');
                header('Location: seeds.php'); exit;
            }

            // Run viability check
            if (isset($_POST['run_check'])) {
                $updated = $this->model->runViabilityCheck();
                setFlash('info',"Viability check complete. $updated seed batch(es) updated.");
                header('Location: seeds.php'); exit;
            }

            // Pass germination test
            if (isset($_POST['pass_germ']) && hasPermission('resources','edit')) {
                $seedId = (int)$_POST['seed_id'];
                $this->model->passGermination($seedId);
                auditLog('seed_germ_passed','resources','seeds',$seedId,'Passed germination test');
                setFlash('success','Seed marked as passed germination — recommend immediate planting.');
                header('Location: seeds.php'); exit;
            }

            // Withdraw from seed bank
            if (isset($_POST['withdraw']) && hasPermission('resources','edit')) {
                $seedId = (int)$_POST['seed_id'];
                $qty    = (int)$_POST['withdraw_qty'];
                
                if ($this->model->withdrawSeeds($seedId, $qty, $user['id'])) {
                    auditLog('seed_withdrawn','resources','seeds',$seedId,"$qty packets by user {$user['id']}");
                    setFlash('success',"Withdrew $qty packet(s). You earned ".($qty*2)." seed credits!");
                } else {
                    setFlash('danger','Not enough packets in stock.');
                }
                header('Location: seeds.php'); exit;
            }
        }

        // Fetch view data
        $seeds = $this->model->getAllSeeds();
        $pageTitle = 'Seed Bank';
        
        $this->view('resources_seeds', [
            'user' => $user,
            'pageTitle' => $pageTitle,
            'seeds' => $seeds
        ]);
    }
}
