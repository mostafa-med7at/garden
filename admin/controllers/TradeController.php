<?php
require_once __DIR__ . '/../core/Controller.php';

class TradeController extends Controller {
    public function index($user) {
        $model = $this->model('TradeModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['create_trade'])) {
                $title    = trim($_POST['title']);
                $desc     = trim($_POST['description']);
                $qty      = trim($_POST['quantity']);
                $cat      = trim($_POST['allergen_category'] ?? '');
                $hours    = max(1, (int)($_POST['expiry_hours'] ?? 2));
                $expires  = date('Y-m-d H:i:s', strtotime("+{$hours} hours"));
                $allergen = isAllergen($cat) ? 1 : 0;

                $newId = $model->createTrade($user['id'], $title, $desc, $qty, $allergen, $cat, $expires);
                auditLog('trade_created','marketplace','flash_trades', $newId, $title);
                setFlash('success','Trade posted! It expires in '.$hours.' hour(s).');
                header('Location: trades.php'); exit;
            }

            if (isset($_POST['claim_trade'])) {
                $tradeId = (int)$_POST['trade_id'];
                $trade = $model->getTradeToClaim($tradeId, $user['id']);
                if ($trade) {
                    $model->claimTrade($tradeId, $user['id']);
                    auditLog('trade_claimed','marketplace','flash_trades',$tradeId,"Claimed by user {$user['id']}");
                    setFlash('success','Trade claimed! Arrange pickup with the seller.');
                } else {
                    setFlash('danger','This trade is no longer available.');
                }
                header('Location: trades.php'); exit;
            }

            if (isset($_POST['cancel_trade'])) {
                $tradeId = (int)$_POST['trade_id'];
                $model->cancelTrade($tradeId, $user['id']);
                setFlash('info','Trade cancelled.'); 
                header('Location: trades.php'); exit;
            }

            if (isset($_POST['donate'])) {
                $produce  = trim($_POST['produce_name']);
                $qty      = trim($_POST['donate_qty']);
                $spoiled  = isset($_POST['is_spoiled']) ? 1 : 0;
                
                if ($spoiled) {
                    $model->donateProduce($user['id'], $produce, $qty, 0, 1, 'Spoiled or unusable');
                    setFlash('danger','Donation rejected — spoiled produce cannot be added. No karma points awarded.');
                } else {
                    $karma = calculateKarmaPoints($qty);
                    $model->donateProduce($user['id'], $produce, $qty, $karma, 0);
                    $model->addKarma($user['id'], $karma);
                    
                    $_SESSION['user']['karma'] = ($_SESSION['user']['karma'] ?? 0) + $karma;
                    auditLog('donation_made','marketplace','donations',null,"$produce: +$karma karma");
                    setFlash('success',"Thanks for donating $produce! You earned $karma karma points ⭐");
                }
                header('Location: trades.php'); exit;
            }

            if (isset($_POST['rate_trade'])) {
                $tradeId = (int)$_POST['trade_id'];
                $rating  = max(1, min(5, (int)$_POST['rating']));
                $notes   = trim($_POST['rating_notes'] ?? '');
                
                if ($model->checkTradeClaim($tradeId, $user['id'])) {
                    try {
                        $model->rateTrade($tradeId, $user['id'], $rating, $notes);
                        setFlash('success','Rating submitted. Thank you!');
                    } catch (Exception $e) { 
                        setFlash('warning','You have already rated this trade.'); 
                    }
                }
                header('Location: trades.php'); exit;
            }
        }

        $model->expireOldTrades();

        $activeTrades = $model->getActiveTrades();
        $myTrades = $model->getMyTrades($user['id'], 10);
        $claimedByMe = $model->getTradesClaimedByMe($user['id'], 5);
        $donations = $model->getMyDonations($user['id'], 10);
        $donationStats = $model->getMyDonationStats($user['id']);

        $this->view('marketplace_trades', [
            'user' => $user,
            'activeTrades' => $activeTrades,
            'myTrades' => $myTrades,
            'claimedByMe' => $claimedByMe,
            'donations' => $donations,
            'donationStats' => $donationStats,
            'pageTitle' => 'Harvest Marketplace'
        ]);
    }
}
