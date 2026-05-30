<?php
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../functions/job-functions.php';
require_once '../functions/user-functions.php';
require_once '../functions/application-functions.php';
require_role('seeker');
$seeker_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("
        SELECT 
            a.app_id,
            a.status,
            a.cover_message,
            a.applied_at,
            jp.title,
            jp.arrangement,
            jp.work_type,
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
    $stmt->execute([$seeker_id]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $applications = [];
}
$total       = count($applications);
$pending     = count(array_filter($applications, fn($a) => $a['status'] === 'pending'));
$shortlisted = count(array_filter($applications, fn($a) => $a['status'] === 'shortlisted'));
$rejected    = count(array_filter($applications, fn($a) => $a['status'] === 'rejected'));
include '../includes/header.php';
?>
<?php include '../includes/navbar.php'; ?>
<div class="container py-4">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">
            <i class="bi bi-send me-2"></i>My Applications
        </h3>
        <p class="text-muted small mb-0">Track the status of all your job applications.</p>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="bi bi-send text-primary"></i>
                    </div>
                    <div>
                        <div class="stat-value text-primary fw-bold"><?= $total ?></div>
                        <div class="small fw-medium">Total</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div>
                        <div class="stat-value text-warning fw-bold"><?= $pending ?></div>
                        <div class="small fw-medium">Pending</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
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
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger bg-opacity-10">
                        <i class="bi bi-x-circle text-danger"></i>
                    </div>
                    <div>
                        <div class="stat-value text-danger fw-bold"><?= $rejected ?></div>
                        <div class="small fw-medium">Rejected</div>
                    </div>
                </div>
            </div>
        </div>
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
<div class="modal fade" id="coverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="coverModalTitle"></h5>
                    <small class="text-muted" id="coverModalCompany"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="coverModalBody" class="text-muted small mb-0" style="line-height:1.7;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('[data-bs-target="#coverModal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('coverModalTitle').textContent = this.dataset.title || '';
            document.getElementById('coverModalCompany').textContent = this.dataset.company || '';
            document.getElementById('coverModalBody').textContent = this.dataset.cover || '';
        });
    });
</script>