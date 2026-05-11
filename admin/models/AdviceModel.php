<?php
require_once __DIR__ . '/../core/Model.php';

class AdviceModel extends Model {
    public function askQuestion($askerId, $question) {
        $this->db->prepare("INSERT INTO advice_questions (asker_id, question) VALUES (?,?)")
                 ->execute([$askerId, $question]);
    }

    public function getAskerId($questionId) {
        $chk = $this->db->prepare("SELECT asker_id FROM advice_questions WHERE id=?");
        $chk->execute([$questionId]);
        return $chk->fetchColumn();
    }

    public function postAnswer($questionId, $answererId, $answer) {
        $this->db->prepare("INSERT INTO advice_answers (question_id, answerer_id, answer) VALUES (?,?,?)")
                 ->execute([$questionId, $answererId, $answer]);
    }

    public function getAnswererId($answerId) {
        $aStmt = $this->db->prepare("SELECT answerer_id FROM advice_answers WHERE id=?");
        $aStmt->execute([$answerId]);
        return $aStmt->fetchColumn();
    }

    public function markBestAnswer($questionId, $answerId, $credits) {
        $this->db->prepare("UPDATE advice_answers SET credits_awarded=? WHERE id=?")->execute([$credits, $answerId]);
        $this->db->prepare("UPDATE advice_questions SET best_answer_id=?, status='answered' WHERE id=?")->execute([$answerId, $questionId]);
    }

    public function addSeedCredits($userId, $credits) {
        $this->db->prepare("UPDATE users SET seed_bank_credits=seed_bank_credits+? WHERE id=?")->execute([$credits, $userId]);
    }

    public function closeQuestion($questionId, $askerId) {
        $this->db->prepare("UPDATE advice_questions SET status='closed' WHERE id=? AND asker_id=?")->execute([$questionId, $askerId]);
    }

    public function getAllQuestions() {
        return $this->db->query("
            SELECT q.*, u.full_name AS asker_name,
                   COUNT(a.id) AS answer_count
            FROM advice_questions q
            JOIN users u ON q.asker_id = u.id
            LEFT JOIN advice_answers a ON a.question_id = q.id
            GROUP BY q.id
            ORDER BY q.status='open' DESC, q.asked_at DESC
        ")->fetchAll();
    }

    public function getAnswersForQuestion($questionId) {
        $answers = $this->db->prepare("
            SELECT a.*, u.full_name AS answerer_name
            FROM advice_answers a JOIN users u ON a.answerer_id=u.id
            WHERE a.question_id=? ORDER BY a.credits_awarded DESC, a.answered_at ASC
        ");
        $answers->execute([$questionId]);
        return $answers->fetchAll();
    }
}
