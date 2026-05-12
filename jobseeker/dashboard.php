<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../functions/job-functions.php';
require_once '../functions/user-functions.php';

require_role('seeker');

$stmt = $pdo->prepare(
    "
    SELECT full_name 
    FROM seeker_profiles 
    WHERE user_id = ?"
);
$stmt->execute([$_SESSION['user_id']]);

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
        JOIN employer_profiles ep ON jp.employer_id = ep.user_id
        WHERE a.user_id = ?
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

usort($recommended_jobs, function($a, $b) {
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
                    <div class="stat-icon bg-primmary bg-opacity-10">
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="bi bi-stars text-dark me-1"></i> Recommended for You
        </h5>
        <a href="jobs.php" class="btn btn-sm btn-secondary">View All</a>
    </div>

    <div class="row g-3 mb-5">
        <?php if (count($recommended_jobs) > 0): ?>
            <?php foreach ($recommended_jobs as $job): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card job-card h-100 shadow-sm border-2" style="position: relative; transition: transform 0.2s;">
                        <div class="card-body d-flex flex-column gap-3">
                            
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="bg-primary bg-opacity-10 p-2 rounded text-dark">
                                    <i class="bi bi-building-fill" style="font-size: 1.25rem;"></i>
                                </div>
                                <?php
                                    $score = $job['match_score'];
                                    if ($score >= 70) {
                                        $badge_class = 'bg-success';
                                    } elseif ($score >= 40) {
                                        $badge_class = 'bg-warning text-dark';
                                    } else {
                                        $badge_class = 'bg-secondary';
                                    }
                                ?>
                                <span class="badge <?= $badge_class ?> fs-6"><?= $score ?>% Match</span>
                            </div>

                            <div>
                                <h5 class="card-title fw-bold mb-1 text-dark"><?= htmlspecialchars($job['title'] ?? 'Untitled') ?></h5>
                                <div class="job-company text-muted small">
                                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($job['company_name'] ?? 'Company') ?>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-1">
                                <?php 
                                    $arr = strtolower($job['arrangement'] ?? '');
                                    $arr_class = ($arr == 'remote') ? 'bg-info text-dark' : (($arr == 'onsite') ? 'bg-light text-dark border' : 'bg-secondary text-white');
                                ?>
                                <span class="badge <?= $arr_class ?> fw-semibold">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= htmlspecialchars(ucfirst($arr)) ?>
                                </span>

                                <?php 
                                    $wt = strtolower($job['work_type'] ?? '');
                                    $wt_class = ($wt == 'fulltime') ? 'bg-primary' : (($wt == 'parttime') ? 'bg-secondary' : 'bg-dark text-white');
                                ?>
                                <span class="badge <?= $wt_class ?> fw-semibold text-dark">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= htmlspecialchars(ucfirst($wt)) ?>
                                </span>
                            </div>

                            <div class="job-posted text-muted small mt-auto pt-2 border-top">
                                <i class="bi bi-calendar3 me-1"></i>
                                Posted <?= isset($job['created_at']) ? date('M d, Y', strtotime($job['created_at'])) : 'Recently' ?>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card bg-light border-0 py-4 text-center">
                    <p class="text-muted mb-0">No specific recommendations yet. Try adding more skills to your profile!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- RECENT APPLICATIONS PAGE -->
    <div class="card-header d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Recent Applications</h5>
    </div>

    <div class="card mb-3 shadow-sm mt-3">
        <div class="card-body p-3">
            <div class="row g-3 p-0">
                <?php if (count($applications) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>JOB / COMPANY</th>
                                    <th>APPLIED ON</th>
                                    <th>ARRANGEMENT</th>
                                    <th>STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $application): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($application['title']) ?>
                                            <small class="text-muted d-block"><?= htmlspecialchars($application['company_name']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($application['applied_at']) ?></td>
                                        <td>
                                            <?php if ($application['arrangement'] === 'remote'): ?>
                                                <span class="badge bg-info text-dark">Remote</span>
                                            <?php elseif ($application['arrangement'] === 'hybrid'): ?>
                                                <span class="badge bg-primary">Hybrid</span>
                                            <?php elseif ($application['arrangement'] === 'onsite'): ?>
                                                <span class="badge bg-secondary">On-site</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($application['status'] === 'pending'): ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php elseif ($application['status'] === 'shortlisted'): ?>
                                                <span class="badge bg-success">Shortlisted</span>
                                            <?php elseif ($application['status'] === 'rejected'): ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center p-4">
                        <i class="bi bi-briefcase display-4 text-muted"></i>
                        <p class="mb-0">You haven't applied to any jobs yet. Start exploring and apply to your dream job!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>