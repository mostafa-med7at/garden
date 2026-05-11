<?php
require_once __DIR__ . '/../core/Controller.php';

class ConsumableController extends Controller {
    public function index($user) {
        $model = $this->model('ConsumableModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_stock']) && $user['role_name']==='admin') {
                if ($_POST['consumable_id'] === 'new') {
                    $model->addConsumable(trim($_POST['name']), $_POST['unit'], (float)$_POST['stock_add'], (float)$_POST['threshold']);
                } else {
                    $cid = (int)$_POST['consumable_id'];
                    $model->restockConsumable($cid, (float)$_POST['stock_add']);
                }
                setFlash('success','Stock updated.'); 
                header('Location: consumables.php'); 
                exit;
            }

            if (isset($_POST['use_item'])) {
                $cid    = (int)$_POST['consumable_id'];
                $amount = (float)$_POST['amount_used'];
                $item   = $model->getConsumableById($cid);
                
                if ($item && $item['stock_level'] >= $amount) {
                    $newLevel = $item['stock_level'] - $amount;
                    $model->updateStockAndUse($cid, $newLevel, $user['id'], $amount);
                    auditLog('consumable_used','resources','consumable_usage_log',null,"{$item['name']}: -{$amount}{$item['unit']}");
                    
                    if ($newLevel <= $item['reorder_threshold'] && !$item['alert_sent']) {
                        $model->markAlertSent($cid);
                        auditLog('reorder_alert','resources','consumables',$cid,"REORDER ALERT: {$item['name']} at $newLevel {$item['unit']}");
                        setFlash('warning',"⚠️ Reorder alert: {$item['name']} is at $newLevel {$item['unit']} — below threshold of {$item['reorder_threshold']}. Admins notified.");
                    } else {
                        setFlash('success',"Used $amount {$item['unit']} of {$item['name']}.");
                    }
                } else { 
                    setFlash('danger','Insufficient stock.'); 
                }
                header('Location: consumables.php'); 
                exit;
            }
        }

        $consumables = $model->getAllConsumables();
        $usageLog = $model->getRecentUsageLog(20);

        $this->view('resources_consumables', [
            'user' => $user,
            'consumables' => $consumables,
            'usageLog' => $usageLog,
            'pageTitle' => 'Consumables Inventory'
        ]);
    }
}
