<?php
// user.php

header('Content-Type: application/json');

require_once 'db.php';

function getUserData($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $userId = intval($_GET['id']);
        $userData = getUserData($userId);
        if ($userData) {
            echo json_encode($userData);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'User ID is required']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
}
?>