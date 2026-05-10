<?php
function apply_to_job(PDO $pdo,int $job_id, int $seeker_id, string $cover_message): bool
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
?>