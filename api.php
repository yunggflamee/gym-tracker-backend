<?php
ob_start();           // NEW
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
session_start();
require_once "config.php";

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login': handleLogin(); break;
    case 'addLog': addWorkoutLog(); break;
    case 'getLogs': getWorkoutLogs(); break;
    case 'deleteLog': deleteWorkoutLog(); break;
    case 'getLeaderboard': getLeaderboard(); break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
}

function handleLogin() {
    global $conn;

    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];

    if (!in_array($username, ALLOWED_USERS)) {
        echo json_encode(["success" => false, "message" => "Incorrect name."]);
        return;
    }

    // CHECK IF USER EXISTS
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $row['id'];
        echo json_encode(["success" => true, "username" => $username]);
    } else {
        echo json_encode(["success" => false, "message" => "User not found in DB"]);
    }
}

function addWorkoutLog() {
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Not logged in"]);
        return;
    }

    $userId = $_SESSION['user_id'];
    $exercise = $_POST['exercise'];
    $weight = $_POST['weight'];
    $sets = $_POST['sets'];
    $reps = $_POST['reps'];

    $stmt = $conn->prepare(
        "INSERT INTO workout_logs (user_id, exercise_name, weight, sets, reps)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("isiii", $userId, $exercise, $weight, $sets, $reps);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add log"]);
    }
}

function getWorkoutLogs() {
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Not logged in"]);
        return;
    }

    $userId = $_SESSION['user_id'];
    
    $stmt = $conn->prepare(
        "SELECT id, exercise_name, weight, sets, reps, log_date
         FROM workout_logs
         WHERE user_id = ?
         ORDER BY log_date DESC"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    echo json_encode(["success" => true, "logs" => $logs]);
}

function deleteWorkoutLog() {
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Not logged in"]);
        return;
    }

    $logId = $_POST['logId'];
    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare(
        "DELETE FROM workout_logs WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param("ii", $logId, $userId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete log"]);
    }
}

function getLeaderboard() {
    global $conn;

    $exercise = $_GET['exercise'] ?? null;

    if ($exercise) {
        $stmt = $conn->prepare(
            "SELECT u.username, wl.exercise_name,
             SUM(wl.sets * wl.reps * wl.weight) AS total_volume
             FROM workout_logs wl
             JOIN users u ON wl.user_id = u.id
             WHERE wl.exercise_name = ?
             GROUP BY u.id, wl.exercise_name
             ORDER BY total_volume DESC"
        );
        $stmt->bind_param("s", $exercise);
    } else {
        $stmt = $conn->prepare(
            "SELECT u.username, wl.exercise_name,
             SUM(wl.sets * wl.reps * wl.weight) AS total_volume
             FROM workout_logs wl
             JOIN users u ON wl.user_id = u.id
             GROUP BY u.id, wl.exercise_name
             ORDER BY total_volume DESC"
        );
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $leaderboard = [];
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = $row;
    }

    echo json_encode(["success" => true, "leaderboard" => $leaderboard]);
}
?>
