<?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    function requiredLogin() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /auth/login.php");
            exit();
        }
    }
    function require_role($role){
        requiredLogin();
        if ($_SESSION['role'] !== $role) {
            header("Location: /index.php");
            exit();
        }
    }
?>