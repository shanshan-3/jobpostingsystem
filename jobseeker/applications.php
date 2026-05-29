<?php 
    require_once '../config/database.php';
    require_once '../includes/session.php';
    require_once '../includes/header.php';
    require_once '../includes/navbar.php';
    require_once '../functions/user-functions.php';
    require_once '../functions/job-functions.php';
    require_once '../functions/application-functions.php';

    require_role('seeker');
    
?>