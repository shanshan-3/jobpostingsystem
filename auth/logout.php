<?php
    session_start();

    session_unset();
    session_destroy();

    header("Location: /jobsystem/index.php");
    exit;
?>