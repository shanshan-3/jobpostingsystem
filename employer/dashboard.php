<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../functions/user-functions.php';
require_once '../functions/job-functions.php';

require_role('employer');

$stmt = $pdo->prepare(
    "
    SELECT * 
    FROM employer_profiles 
    WHERE user_id = ?"
);
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    header("Location: profile.php?setup=1");
    exit();
}
include '../includes/header.php';

try {
    $stmt = $pdo->prepare("
        SELECT j.job_id, j.title, j.created_at, j.work_type, j.arrangement, j.status, 
        (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.job_id) AS applicant_count
        FROM job_posting j
        WHERE j.employer_id = ?
        ORDER BY j.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $jobs = [];
}
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM applications a
        JOIN job_posting j ON a.job_id = j.job_id
        WHERE j.employer_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $application_count = $stmt->fetchColumn();
} catch (PDOException $e) {
    $application_count = 0;
}
try {
    $stmt = $pdo->prepare("
        SELECT sp.full_name, j.title
        FROM applications a
        JOIN job_posting j ON a.job_id = j.job_id
        LEFT JOIN (
            SELECT sp1.user_id, sp1.full_name
            FROM seeker_profiles sp1
            INNER JOIN (
                SELECT user_id, MAX(profile_id) AS latest_profile_id
                FROM seeker_profiles
                GROUP BY user_id
            ) latest
                ON latest.user_id = sp1.user_id
                AND latest.latest_profile_id = sp1.profile_id
        ) sp ON a.seeker_id = sp.user_id
        WHERE j.employer_id = ?
        ORDER BY a.applied_at DESC
        LIMIT 4
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_applicants = [];
}

$active_jobs = count(array_filter($jobs, fn($j) => $j['status'] === 'active'));
$closed_jobs = count(array_filter($jobs, fn($j) => $j['status'] === 'closed'));

?>
<?php include '../includes/navbar.php'; ?>

<div class="container py-4">
    <div class="welcome-banner p-4 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="mb-1">WELCOME, <span><?= htmlspecialchars($profile['company_name']) ?></span> !</h2>
            <p class="mb-0">This is your dashboard. Manage your profile, view job postings, and explore new opportunities.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="post-job.php" class="btn btn-warning fw-bold text-dark"><i class="bi bi-plus me-1"></i>Post a Job</a>
        </div>
    </div>
    <!-- STATS CARD -->
    <div class="row g-3 mb-4">

        <div class="col-12 col-md-4">
            <div class="card stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="bi bi-briefcase text-primary"></i>
                    </div>
                    <div>
                        <div class="stat-value text-primary fw-bold"><?= count($jobs) ?></div>
                        <div class="small fw-medium">Active Job Posts</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10">
                        <i class="bi bi-people-fill text-warning"></i>
                    </div>
                    <div>
                        <div class="stat-value text-warning fw-bold"><?= $application_count ?></div>
                        <div class="small fw-medium">Total Applications</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-dark bg-opacity-10">
                        <i class="bi bi-archive-fill text-dark"></i>
                    </div>
                    <div>
                        <div class="stat-value text-dark fw-bold"><?= $closed_jobs ?></div>
                        <div class="small fw-medium">Closed Jobs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- JOB POSTING PAGE -->
    <div class="card-header d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Recent Job Postings</h5>
        <div class="card-jobs">
            <a href="post-job.php" class="btn btn-warning fw-bold text-dark"><i class="bi bi-plus me-1"></i>Post a Job</a>
        </div>
    </div>
    <div class="card mb-3 shadow-sm">
        <div class="card-body p-0">
            <?php if (count($jobs) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>JOB TITLE</th>
                                <th>WORK TYPE</th>
                                <th>ARRANGEMENT</th>
                                <th>APPLICANTS</th>
                                <th>STATUS</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td><?= htmlspecialchars($job['title']) ?></td>
                                    <td><?= htmlspecialchars($job['work_type']) ?></td>
                                    <td>
                                        <?php if ($job['arrangement'] === 'remote'): ?>
                                            <span class="badge bg-info">remote</span>
                                        <?php elseif ($job['arrangement'] === 'hybrid'): ?>
                                            <span class="badge bg-warning">hybrid</span>
                                        <?php else: ?>
                                            <span class="badge bg-light">on-site</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $job['applicant_count'] ?></td>
                                    <td>
                                        <?php if ($job['status'] === 'active'): ?>
                                            <span class="badge bg-primary">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-dark">Closed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit-job.php?id=<?= $job['job_id'] ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center p-4">
                    <i class="bi bi-briefcase text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3 mb-0">No job postings found. Start by posting your first job!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RECENT APPLICANTS -->
    <div class="card-header d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Recent Applicants</h5>
    </div>
    <div class="card mb-4 shadow-sm">
        <div class="card-body p-0">
            <?php if (count($recent_applicants) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>APPLICANT</th>
                                <th>APPLIED FOR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_applicants as $applicant): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; flex-shrink: 0;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <span class="fw-medium"><?= htmlspecialchars($applicant['full_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="align-middle"><?= htmlspecialchars($applicant['title']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center p-4">
                    <i class="bi bi-person text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3 mb-0">No recent applicants found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>