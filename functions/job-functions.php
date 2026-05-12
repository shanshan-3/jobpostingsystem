<?php
function get_jobs(PDO $pdo, array $filters = []): array
{

    $sql = "
        SELECT 
            jp.job_id,
            jp.title,
            jp.description,
            jp.arrangement,
            jp.work_type,
            jp.required_skills,
            jp.accessibility_features,
            jp.created_at,
            ep.company_name
        FROM job_posting jp
        JOIN (
            SELECT e.user_id, e.company_name
            FROM employer_profiles e
            INNER JOIN (
                SELECT user_id, MAX(profile_id) AS latest_profile_id
                FROM employer_profiles
                GROUP BY user_id
            ) latest
                ON latest.user_id = e.user_id
                AND latest.latest_profile_id = e.profile_id
        ) ep ON jp.employer_id = ep.user_id
        WHERE jp.status = 'active'
    ";

    $params = [];

    if (!empty($filters['keyword'])) {
        $sql .= " AND (jp.title LIKE :keyword OR jp.description LIKE :keyword)";
        $params['keyword'] = '%' . $filters['keyword'] . '%';
    }

    if (!empty($filters['arrangement'])) {
        $sql .= " AND LOWER(jp.arrangement) = :arrangement";
        $params['arrangement'] = strtolower($filters['arrangement']);
    }

    if (!empty($filters['work_type'])) {
        $sql .= " AND LOWER(jp.work_type) = :work_type";
        $params['work_type'] = strtolower($filters['work_type']);
    }

    $sql .= " ORDER BY jp.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function has_applied(PDO $pdo, int $jobId, int $seekerId): bool
{
    $stmt = $pdo->prepare("
        SELECT 1 FROM applications
        WHERE job_id = :job_id AND seeker_id = :seeker_id
        LIMIT 1
    ");
    $stmt->execute([
        ':job_id' => $jobId,
        ':seeker_id' => $seekerId
    ]);
    return (bool)$stmt->fetchColumn();
}

function post_job(PDO $pdo, array $data): bool
{
    $sql = "
        INSERT INTO job_posting
            (employer_id, title, description, required_skills,
            work_type, arrangement, accessibility_features)
            VALUES
            (:employer_id, :title, :description, :required_skills,
            :work_type, :arrangement, :accessibility_features)
    ";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($data);
}
function update_job(PDO $pdo, int $job_id, array $data): bool
{
    $sql = "
        UPDATE job_posting
        SET title = :title,
            description = :description,
            required_skills = :required_skills,
            work_type = :work_type,
            arrangement = :arrangement,
            accessibility_features = :accessibility_features,
            status = :status
        WHERE job_id = :job_id
    ";
    $stmt = $pdo->prepare($sql);
    $data[':job_id'] = $job_id;
    return $stmt->execute($data);
}

function compute_match($seeker, $job)
{
    $total_score = 0;

    $seeker_skills = explode(',', strtolower($seeker['skills'] ?? ''));
    $job_skills = explode(',', strtolower($job['required_skills'] ?? ''));

    $skills_match = count(array_intersect($seeker_skills, $job_skills));
    $total_skills = count($job_skills);

    if ($total_skills > 0) {
        $total_score += ($skills_match / $total_skills) * 40;
    } else {
        $total_score += 40;
    }

    if ($seeker['work_preference'] === $job['arrangement']) {
        $total_score += 30;
    }

    $seeker_needs = explode(',', strtolower($seeker['accessibility_needs'] ?? ''));
    $job_access = explode(',', strtolower($job['accessibility_features'] ?? ''));

    $needs_match = count(array_intersect($seeker_needs, $job_access));
    $total_needs = count($seeker_needs);

    if ($total_needs > 0) {
        $total_score += ($needs_match / $total_needs) * 20;
    } else {
        $total_score += 20;
    }

    if ($seeker['work_preference'] === $job['work_type']) {
        $total_score += 10;
    }

    return (int) round($total_score);
}
