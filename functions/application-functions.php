<?php
function apply_to_job(PDO $pdo, int $job_id, int $seeker_id, string $cover_message): bool
{
    $sql = "
        INSERT INTO applications (job_id, seeker_id, cover_message)
        VALUES (:job_id, :seeker_id, :cover_message)
    ";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':job_id' => $job_id,
        ':seeker_id' => $seeker_id,
        ':cover_message' => $cover_message
    ]);
}

function update_application_status(PDO $pdo, int $employer_id, array $post, string $default_return = 'applicants.php'): array
{
    $app_id = filter_var($post['app_id'] ?? null, FILTER_VALIDATE_INT);
    $status = $post['status'] ?? '';
    $return_to = $post['return_to'] ?? $default_return;

    $parsed = parse_url($return_to);
    if (!empty($parsed['scheme']) || !empty($parsed['host']) || strpos($return_to, $default_return) !== 0) {
        $return_to = $default_return;
    }

    $success = false;
    if ($app_id && in_array($status, ['shortlisted', 'rejected'], true)) {
        $check = $pdo->prepare("
            SELECT a.app_id
            FROM applications a
            JOIN job_posting j ON a.job_id = j.job_id
            WHERE a.app_id = :app_id
              AND j.employer_id = :employer_id
            LIMIT 1
        ");
        $check->execute([
            ':app_id' => $app_id,
            ':employer_id' => $employer_id
        ]);

        if ($check->fetchColumn()) {
            $stmt = $pdo->prepare("
                UPDATE applications
                SET status = :new_status
                WHERE app_id = :application_id
            ");
            $success = $stmt->execute([
                ':new_status' => $status,
                ':application_id' => $app_id
            ]);
        }
    }

    return [
        'success' => $success,
        'return_to' => $return_to
    ];
}
