<?php
require_once __DIR__ . '/../core/Controller.php';

class VotingController extends Controller {
    public function index($user) {
        $model = $this->model('VotingModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['create_proposal']) && $user['role_name']==='admin') {
                $ends = $_POST['voting_ends_at'] ?: date('Y-m-d H:i:s', strtotime('+7 days'));
                $model->createProposal(trim($_POST['title']), trim($_POST['description']), $user['id'], $ends);
                setFlash('success','Proposal created. Members can now vote.'); 
                header('Location: voting.php'); 
                exit;
            }

            if (isset($_POST['cast_vote'])) {
                $propId = (int)$_POST['proposal_id'];
                $prop = $model->getOpenProposal($propId);
                
                if (!$prop) { 
                    setFlash('danger','Voting is closed for this proposal.'); 
                    header('Location: voting.php'); 
                    exit; 
                }
                
                try {
                    $model->castVote($propId, $user['id']);
                    setFlash('success','Your vote has been recorded!');
                } catch (Exception $e) {
                    setFlash('warning','You have already voted on this proposal.');
                }
                header('Location: voting.php'); 
                exit;
            }

            if (isset($_POST['close_voting']) && $user['role_name']==='admin') {
                $propId = (int)$_POST['proposal_id'];
                $count = $model->getVoteCount($propId);
                
                $model->closeVoting($propId, $propId);
                auditLog('voting_closed','volunteer','proposals',$propId,"$count votes");
                setFlash('success',"Voting closed. Total votes: $count."); 
                header('Location: voting.php'); 
                exit;
            }
        }

        $proposals = $model->getAllProposals($user['id']);
        $totalVoters = $model->getTotalActiveVoters();

        $this->view('volunteer_voting', [
            'user' => $user,
            'proposals' => $proposals,
            'totalVoters' => $totalVoters,
            'pageTitle' => 'Community Voting'
        ]);
    }
}
