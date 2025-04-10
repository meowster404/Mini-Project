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

    // Protection against session fixation
    if (!isset($_SESSION['last_activity'])) {
        session_destroy();
        return false;
    }

    // Session timeout after 30 minutes of inactivity
    $inactivity_limit = 30 * 60; // 30 minutes
    if (time() - $_SESSION['last_activity'] > $inactivity_limit) {
        session_destroy();
        return false;
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $session_valid = checkSession();
    
    // Regenerate session ID periodically to prevent session fixation
    if ($session_valid && !isset($_SESSION['last_regenerated']) || 
        (time() - $_SESSION['last_regenerated'] > 300)) {
        session_regenerate_id(true);
        $_SESSION['last_regenerated'] = time();
    }
    
    echo json_encode(['valid' => $session_valid]);
}