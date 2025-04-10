<?php
session_start();

function checkSession() {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Check if session has expired
    if (isset($_SESSION['expires']) && time() > $_SESSION['expires']) {
        session_destroy();
        return false;
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['valid' => checkSession()]);
}