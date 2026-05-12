<?php
include '../config/database.php';
include '../includes/session.php';
include '../functions/application-functions.php';

require_role('seeker');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = $_POST['job_id'] ?? null;
    $cover_message = $_POST['cover_message'] ?? '';

    if (!$job_id) {
        header("Location: jobs.php?application=error");
        exit();
    }

    $seeker_id = $_SESSION['user_id'];

    $sql = "SELECT COUNT(*) FROM applications WHERE job_id = :job_id AND seeker_id = :seeker_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':job_id' => $job_id,
        ':seeker_id' => $seeker_id
    ]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        header("Location: jobs.php?application=duplicate");
        exit();
    }

    if (apply_to_job($pdo, $job_id, $seeker_id, $cover_message)) {
        header("Location: jobs.php?application=success");
        exit();
    }

    header("Location: jobs.php?application=error");
    exit();
}
?>