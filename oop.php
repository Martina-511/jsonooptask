<?php

class Task {
    private $id;
    private $title;
    private $description;
    private $priority;
    private $dueDate;
    private $user;
    
    public function __construct($id, $title, $description, $priority, $dueDate, $user) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->priority = $priority;
        $this->dueDate = $dueDate;
        $this->user = $user;
    }
    
    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getPriority() { return $this->priority; }
    public function getDueDate() { return $this->dueDate; }
    public function getUser() { return $this->user; }
    
    public function toArray() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'dueDate' => $this->dueDate,
            'user' => $this->user
        ];
    }
}

class TaskManager {
    private $tasks = [];
    private $file = 'tasks.json';

    public function __construct() {
        $this->loadTasks();
    }

    private function loadTasks() {
        if (file_exists($this->file)) {
            $json = file_get_contents($this->file);
            $data = json_decode($json, true);
            if (is_array($data)) {
                foreach ($data as $taskData) {
                    $this->tasks[] = new Task(
                        $taskData['id'], $taskData['title'], $taskData['description'],
                        $taskData['priority'], $taskData['dueDate'], $taskData['user']
                    );
                }
            }
        }
    }

    private function saveTasks() {
        $data = array_map(fn($task) => $task->toArray(), $this->tasks);
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function addTask($title, $description, $priority, $dueDate, $user) {
        $id = uniqid();
        $task = new Task($id, $title, $description, $priority, $dueDate, $user);
        $this->tasks[] = $task;
        $this->saveTasks();
    }

    public function deleteTask($id) {
        $this->tasks = array_filter($this->tasks, fn($task) => $task->getId() !== $id);
        $this->saveTasks();
    }

    public function listTasks() {
        return $this->tasks;
    }
}

$taskManager = new TaskManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $taskManager->addTask($_POST['title'], $_POST['description'], $_POST['priority'], $_POST['dueDate'], $_POST['user']);
    } elseif (isset($_POST['delete'])) {
        $taskManager->deleteTask($_POST['id']);
    }
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

$tasks = $taskManager->listTasks();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Task Management</title>
</head>
<body>
    <h1>Task Management System</h1>
    <form method="post">
        Title: <input type="text" name="title" required><br>
        Description: <input type="text" name="description" required><br>
        Priority: <select name="priority">
            <option value="High">High</option>
            <option value="Medium">Medium</option>
            <option value="Low">Low</option>
        </select><br>
        Due Date: <input type="date" name="dueDate" required><br>
        User: <input type="text" name="user" required><br>
        <button type="submit" name="add">Add Task</button>
    </form>

    <h2>Tasks</h2>
    <ul>
        <?php foreach ($tasks as $task): ?>
            <li>
                <?= htmlspecialchars($task->getTitle()) ?> (<?= $task->getPriority() ?>) -
                <?= htmlspecialchars($task->getUser()) ?> - Due: <?= $task->getDueDate() ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $task->getId() ?>">
                    <button type="submit" name="delete">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
