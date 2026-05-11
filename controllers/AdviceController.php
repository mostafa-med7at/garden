<?php
require_once __DIR__ . '/../core/Controller.php';

class AdviceController extends Controller {
    public function index($user) {
        $model = $this->model('AdviceModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['ask_question'])) {
                $q = trim($_POST['question']);
                if ($q) {
                    $model->askQuestion($user['id'], $q);
                    setFlash('success', 'Question posted! Other members can now answer.');
                }
                header('Location: advice.php'); 
                exit;
            }

            if (isset($_POST['post_answer'])) {
                $qId    = (int)$_POST['question_id'];
                $answer = trim($_POST['answer']);
                if ($answer) {
                    $asker = $model->getAskerId($qId);
                    if ($asker == $user['id']) {
                        setFlash('warning', "You can't answer your own question.");
                    } else {
                        $model->postAnswer($qId, $user['id'], $answer);
                        setFlash('success', 'Answer posted!');
                    }
                }
                header('Location: advice.php#q'.$qId); 
                exit;
            }

            if (isset($_POST['select_best'])) {
                $answerId  = (int)$_POST['answer_id'];
                $qId       = (int)$_POST['question_id'];

                $askerId = $model->getAskerId($qId);
                if ($askerId != $user['id']) { 
                    setFlash('danger', 'Only the asker can select the best answer.'); 
                    header('Location: advice.php'); 
                    exit; 
                }

                $answererId = $model->getAnswererId($answerId);

                if ($answererId) {
                    $credits = 5; 
                    $model->markBestAnswer($qId, $answerId, $credits);
                    $model->addSeedCredits($answererId, $credits);
                    
                    auditLog('best_answer_selected', 'marketplace', 'advice_answers', $answerId, "$credits credits awarded");
                    setFlash('success', "Best answer selected! The member earned $credits seed bank credits 🌱");
                }
                header('Location: advice.php#q'.$qId); 
                exit;
            }

            if (isset($_POST['close_question'])) {
                $qId = (int)$_POST['question_id'];
                $model->closeQuestion($qId, $user['id']);
                setFlash('info', 'Question closed.'); 
                header('Location: advice.php'); 
                exit;
            }
        }

        $questionsRaw = $model->getAllQuestions();
        $questions = [];
        
        foreach ($questionsRaw as $q) {
            $q['answers'] = $model->getAnswersForQuestion($q['id']);
            $questions[] = $q;
        }

        $this->view('marketplace_advice', [
            'user' => $user,
            'questions' => $questions,
            'pageTitle' => 'Advice Board'
        ]);
    }
}
