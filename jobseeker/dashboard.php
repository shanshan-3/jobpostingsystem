<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../functions/job-functions.php';
require_once '../functions/user-functions.php';

require_role('seeker');

$profile = get_seeker_profile($pdo, $_SESSION['user_id']);
if (!$profile) {
    header('Location: profile.php?setup=1');
    exit;
}

include '../includes/header.php';

try {
    $stmt = $pdo->prepare("
        SELECT 
            a.job_id, a.status, a.applied_at,
            jp.title, jp.arrangement,
            ep.company_name
        FROM applications a
        JOIN job_posting jp ON a.job_id = jp.job_id
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
        WHERE a.seeker_id = ?
        ORDER BY a.applied_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $applications = [];
}


$total = count($applications);
$pending = count(array_filter($applications, fn($a) => $a['status'] === 'pending'));
$shortlisted = count(array_filter($applications, fn($a) => $a['status'] === 'shortlisted'));

// Recommendation logic
$all_jobs = get_jobs($pdo);
$recommended_jobs = [];

foreach ($all_jobs as $job) {
    $score = compute_match($profile, $job);
    if ($score >= 20) {
        $job['match_score'] = $score;
        $recommended_jobs[] = $job;
    }
}

usort($recommended_jobs, function ($a, $b) {
    return $b['match_score'] <=> $a['match_score'];
});
$recommended_jobs = array_slice($recommended_jobs, 0, 5);

?>
<?php include '../includes/navbar.php'; ?>

<div class="container py-4">
    <div class="welcome-banner p-4 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="mb-1">WELCOME, <span><?= htmlspecialchars($profile['full_name']) ?></span> !</h2>
            <p class="mb-0">This is your dashboard. Manage your profile, view applications, and explore new opportunities.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="jobs.php" class="btn btn-warning fw-bold text-dark"><i class="bi bi-search me-1"></i>Browse Jobs</a>
        </div>
    </div>

    <!-- STATS CARD -->
    <div class="row g-3 mb-4">

        <div class="col-12 col-md-4">
            <div class="card stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="bi bi-send text-primary"></i>
                    </div>
                    <div>
                        <div class="stat-value text-primary fw-bold"><?= $total ?></div>
                        <div class="small fw-medium">Total Applications</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10">
                        <i class="bi bi-hourglass-split text-warning"></i>
                    </div>
                    <div>
                        <div class="stat-value text-warning fw-bold"><?= $pending ?></div>
                        <div class="small fw-medium">Pending Applications</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="bi bi-check2-circle text-success"></i>
                    </div>
                    <div>
                        <div class="stat-value text-success fw-bold"><?= $shortlisted ?></div>
                        <div class="small fw-medium">Shortlisted</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RECOMMENDED FOR YOU -->
    <div class="card-header d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="bi bi-stars text-dark me-1"></i> Recommended for You
        </h5>
        <a href="jobs.php" class="btn btn-sm btn-secondary">View All</a>
    </div>

    <div class="card mb-5 shadow-sm">
        <div class="card-body p-0">
            <?php if (count($recommended_jobs) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>JOB / COMPANY</th>
                                <th>MATCH</th>
                                <th>ARRANGEMENT</th>
                                <th>WORK TYPE</th>
                                <th>POSTED</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recommended_jobs as $job): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($job['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-building me-1"></i>
                                            <?= htmlspecialchars($job['company_name'] ?? 'Company', ENT_QUOTES, 'UTF-8') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $score = (int) ($job['match_score'] ?? 0);
                                        if ($score >= 70) {
                                            $badge_class = 'bg-success';
                                        } elseif ($score >= 40) {
                                            $badge_class = 'bg-warning text-dark';
                                        } else {
                                            $badge_class = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= $score ?>% Match</span>
                                    </td>
                                    <td>
                                        <?php
                                        $arr = strtolower($job['arrangement'] ?? '');
                                        if ($arr === 'remote') {
                                            echo '<span class="badge bg-info text-dark">Remote</span>';
                                        } elseif ($arr === 'hybrid') {
                                            echo '<span class="badge bg-primary">Hybrid</span>';
                                        } elseif ($arr === 'onsite' || $arr === 'on-site') {
                                            echo '<span class="badge bg-secondary">On-site</span>';
                                        } else {
                                            echo '<span class="badge bg-light text-dark border">' . htmlspecialchars(ucfirst($arr)) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $wt = strtolower($job['work_type'] ?? '');
                                        if ($wt === 'fulltime') {
                                            echo '<span class="badge bg-success">Full-time</span>';
                                        } elseif ($wt === 'parttime') {
                                            echo '<span class="badge bg-secondary">Part-time</span>';
                                        } elseif ($wt === 'freelance') {
                                            echo '<span class="badge bg-dark">Freelance</span>';
                                        } else {
                                            echo '<span class="badge bg-light text-dark border">' . htmlspecialchars(ucfirst($wt)) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-muted small">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?= isset($job['created_at']) ? date('M d, Y', strtotime($job['created_at'])) : 'Recently' ?>
                                    </td>
                                    <td>
                                        <a href="jobs.php" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center p-4">
                    <i class="bi bi-stars display-4 text-muted"></i>
                    <p class="text-muted mt-3 mb-0">No specific recommendations yet. Try adding more skills to your profile!</p>
                    <a href="profile.php" class="btn btn-warning fw-bold text-dark mt-3">
                        <i class="bi bi-person me-1"></i>Update Profile
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RECENT APPLICATIONS -->
    <div class="card-header d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Recent Applications</h5>
    </div>

    <div class="card mb-3 shadow-sm">
        <div class="card-body p-0">
            <?php if ($total > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>JOB / COMPANY</th>
                                <th>ARRANGEMENT</th>
                                <th>WORK TYPE</th>
                                <th>APPLIED ON</th>
                                <th>STATUS</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($app['title'], ENT_QUOTES, 'UTF-8') ?>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-building me-1"></i>
                                            <?= htmlspecialchars($app['company_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $arr = strtolower($app['arrangement'] ?? '');
                                        if ($arr === 'remote') {
                                            echo '<span class="badge bg-info text-dark">Remote</span>';
                                        } elseif ($arr === 'hybrid') {
                                            echo '<span class="badge bg-primary">Hybrid</span>';
                                        } elseif ($arr === 'onsite') {
                                            echo '<span class="badge bg-secondary">On-site</span>';
                                        } else {
                                            echo '<span class="badge bg-light text-dark border">' . htmlspecialchars(ucfirst($arr)) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $wt = strtolower($app['work_type'] ?? '');
                                        if ($wt === 'fulltime') {
                                            echo '<span class="badge bg-success">Full-time</span>';
                                        } elseif ($wt === 'parttime') {
                                            echo '<span class="badge bg-secondary">Part-time</span>';
                                        } elseif ($wt === 'freelance') {
                                            echo '<span class="badge bg-dark">Freelance</span>';
                                        } else {
                                            echo '<span class="badge bg-light text-dark border">' . htmlspecialchars(ucfirst($wt)) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-muted small">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                    </td>
                                    <td>
                                        <?php if ($app['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($app['status'] === 'shortlisted'): ?>
                                            <span class="badge bg-success">Shortlisted</span>
                                        <?php elseif ($app['status'] === 'rejected'): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($app['cover_message'])): ?>
                                            <button
                                                class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#coverModal"
                                                data-cover="<?= htmlspecialchars($app['cover_message'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-title="<?= htmlspecialchars($app['title'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-company="<?= htmlspecialchars($app['company_name'], ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="bi bi-envelope me-1"></i>Cover
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center p-4">
                    <i class="bi bi-send display-4 text-muted"></i>
                    <p class="text-muted mt-3 mb-0">You haven't applied to any jobs yet.</p>
                    <a href="jobs.php" class="btn btn-warning fw-bold text-dark mt-3">
                        <i class="bi bi-search me-1"></i>Browse Jobs
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>