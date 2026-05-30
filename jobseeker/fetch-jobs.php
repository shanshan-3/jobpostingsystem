<?php
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../functions/job-functions.php';
require_once '../functions/user-functions.php';

if (!isset($_SESSION['user_id'])) {
    exit('<p class="text-danger">Unauthorized. Please log in.</p>');
}
$seeker_id = $_SESSION['user_id'];
$seeker_profile = get_seeker_profile($pdo, $seeker_id) ?: [];

$filters = [
    'keyword' => $_GET['keyword'] ?? '',
    'arrangement' => $_GET['arrangement'] ?? '',
    'work_type' => $_GET['work_type'] ?? ''
];

$jobs = get_jobs($pdo, $filters);

if (empty($jobs)) {
    echo '<div class="card col-12 py-5 text-center">
            <div class="p-5 border-0">
                <i class="bi bi-briefcase text-muted mb-3" style="font-size: 2.5rem; display: block;"></i>
                <h4 class="text-muted">No jobs found</h4>
                <p class="text-muted mb-0">Try adjusting your filters.</p>
            </div>
        </div>';
    exit;
}

foreach ($jobs as $job):
    $current_job_id = $job['id'] ?? $job['job_id'] ?? 0;

    $alreadyApplied = false;
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM applications WHERE job_id = ? AND seeker_id = ?");
        $stmt->execute([$current_job_id, $seeker_id]);
        $alreadyApplied = $stmt->fetch() ? true : false;
    } catch (PDOException $e) {
        $alreadyApplied = false;
    }
?>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card job-card h-100 shadow-sm">
            <div class="card-body d-flex flex-column gap-3">

                <div class="d-flex justify-content-between align-items-start">
                    <div class="bg-primary bg-opacity-10 p-2 rounded text-dark">
                        <i class="bi bi-building-fill" style="font-size: 1.25rem;"></i>
                    </div>
                    <?php
                    $match_score = compute_match($seeker_profile, $job);
                    if ($match_score >= 70) {
                        $badge_class = 'bg-success';
                    } elseif ($match_score >= 40) {
                        $badge_class = 'bg-warning text-dark';
                    } else {
                        $badge_class = 'bg-secondary';
                    }
                    ?>
                    <span class="badge <?= $badge_class ?> fs-6"><?= $match_score ?>% Match</span>
                </div>

                <div>
                    <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($job['title'] ?? 'Untitled') ?></h5>
                    <div class="job-company text-muted small">
                        <i class="bi bi-building me-1"></i><?= htmlspecialchars($job['company_name'] ?? 'Company') ?>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-1">
                    <?php
                    $arrangement_raw = strtolower(trim($job['arrangement'] ?? ''));
                    if ($arrangement_raw === 'remote') {
                        $arrangement_class = 'bg-info text-dark';
                        $arrangement_label = 'Remote';
                    } elseif ($arrangement_raw === 'hybrid') {
                        $arrangement_class = 'bg-warning text-dark';
                        $arrangement_label = 'Hybrid';
                    } elseif (in_array($arrangement_raw, ['onsite', 'on-site', 'on site'], true)) {
                        $arrangement_class = 'bg-light text-dark border';
                        $arrangement_label = 'On-site';
                    } else {
                        $arrangement_class = '';
                        $arrangement_label = '';
                    }
                    ?>
                    <?php if ($arrangement_label !== ''): ?>
                        <span class="badge <?= $arrangement_class ?> fw-semibold">
                            <i class="bi bi-geo-alt me-1"></i>
                            <?= htmlspecialchars($arrangement_label) ?>
                        </span>
                    <?php endif; ?>

                    <span class="badge <?= ($job['work_type'] ?? '') == 'fulltime' ? 'bg-primary' : (($job['work_type'] ?? '') == 'parttime' ? 'bg-secondary' : 'bg-dark'); ?> fw-semibold">
                        <i class="bi bi-clock me-1"></i>
                        <?= htmlspecialchars(ucfirst($job['work_type'] ?? '')) ?>
                    </span>
                </div>


                <div class="job-posted text-muted small mt-1">
                    <i class="bi bi-calendar3 me-1"></i>
                    Posted <?= isset($job['created_at']) ? date('M d, Y', strtotime($job['created_at'])) : 'Unknown' ?>
                </div>

                <div class="mt-auto">
                    <button class="btn btn-sm w-100 view-job-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#jobModal"
                        data-job-id="<?= $current_job_id ?>"
                        data-job-title="<?= htmlspecialchars($job['title'] ?? '') ?>"
                        data-job-company="<?= htmlspecialchars($job['company_name'] ?? 'Company') ?>"
                        data-job-arrangement="<?= htmlspecialchars($job['arrangement'] ?? '') ?>"
                        data-job-worktype="<?= htmlspecialchars($job['work_type'] ?? '') ?>"
                        data-job-description="<?= htmlspecialchars($job['description'] ?? '') ?>"
                        data-job-skills="<?= htmlspecialchars($job['required_skills'] ?? '') ?>"
                        data-job-accessibility="<?= htmlspecialchars($job['accessibility_features'] ?? '') ?>"
                        data-job-created="<?= isset($job['created_at']) ? date('M d, Y', strtotime($job['created_at'])) : '' ?>"
                        data-job-applied="<?= $alreadyApplied ? '1' : '0' ?>">
                        View Details
                    </button>
                </div>

            </div>
        </div>
    </div>
<?php endforeach; ?>