<?php
require_once __DIR__ . '/../core/Controller.php';

class TaskController extends Controller {
    public function index($user) {
        $model = $this->model('TaskModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_task']) && $user['role_name']==='admin') {
                $pts = (int)$_POST['difficulty_score'] * 10;
                $model->addTask(trim($_POST['title']), trim($_POST['description']), (int)$_POST['difficulty_score'], $pts, $user['id']);
                setFlash('success','Task created.'); 
                header('Location: tasks.php'); 
                exit;
            }

            if (isset($_POST['claim'])) {
                $taskId = (int)$_POST['task_id'];
                $model->claimTask($taskId, $user['id']);
                setFlash('success','Task claimed! Complete it and log your hours.'); 
                header('Location: tasks.php'); 
                exit;
            }

            if (isset($_POST['complete_task'])) {
                $taskId   = (int)$_POST['task_id'];
                $compType = $_POST['completion_type'];
                
                $task = $model->getTask($taskId, $user['id']);
                if ($task) {
                    $pts  = $compType==='full' ? $task['points_reward'] : (int)($task['points_reward']*0.5);
                    $stat = $compType==='full' ? 'completed' : 'partial';
                    
                    $model->completeTask($taskId, $user['id'], $compType, $pts, $stat);
                    $model->addCommunityPoints($user['id'], $pts);
                    
                    $_SESSION['user']['points'] = ($_SESSION['user']['points'] ?? 0) + $pts;
                    auditLog('task_completed','volunteer','task_completions',null,"Task $taskId: $compType ($pts pts)");
                    setFlash('success',"Task marked $compType! You earned $pts community points.");
                }
                header('Location: tasks.php'); 
                exit;
            }

            if (isset($_POST['log_hours'])) {
                $hours = (float)$_POST['hours'];
                $desc  = trim($_POST['activity']);
                $month = date('Y-m');
                
                $model->logServiceHours($user['id'], $hours, $desc, $month);
                auditLog('hours_logged','volunteer','service_hours',null,"$hours hrs: $desc");
                setFlash('info',"$hours hour(s) logged for admin review.");
                header('Location: tasks.php'); 
                exit;
            }

            if (isset($_POST['review_hours']) && $user['role_name']==='admin') {
                $hId    = (int)$_POST['hour_id'];
                $action = $_POST['action'];
                $model->reviewServiceHours($hId, $action, $user['id']);
                
                setFlash('success',"Hours ".($action==='approved'?'approved':'rejected')."."); 
                header('Location: tasks.php'); 
                exit;
            }
        }

        $month = date('Y-m');
        $tasks = $model->getAllTasks();
        $myHoursApproved = $model->getApprovedHours($user['id'], $month);
        $pendingHours = $model->getPendingHours();
        $myHourLog = $model->getMyHourLog($user['id'], 10);
        $required = MONTHLY_SERVICE_HOURS;

        $this->view('volunteer_tasks', [
            'user' => $user,
            'tasks' => $tasks,
            'myHoursApproved' => $myHoursApproved,
            'pendingHours' => $pendingHours,
            'myHourLog' => $myHourLog,
            'required' => $required,
            'pageTitle' => 'Tasks & Volunteer Hours'
        ]);
    }
}
