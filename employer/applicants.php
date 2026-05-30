<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../functions/user-functions.php';
require_once '../functions/application-functions.php';

require_role('employer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employer_id = $_SESSION['user_id'] ?? null;
    if (!$employer_id) {
        header('Location: ../auth/login.php');
        exit;
    }

    $result = update_application_status($pdo, $employer_id, $_POST);
    $separator = strpos($result['return_to'], '?') !== false ? '&' : '?';
    header('Location: ' . $result['return_to'] . $separator . 'status=' . ($result['success'] ? 'updated' : 'error'));
    exit;
}

$employer_id = $_SESSION['user_id'];
$job_filter = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$return_params = $_GET;
unset($return_params['status']);
$return_to = 'applicants.php';
if (!empty($return_params)) {
    $return_to .= '?' . http_build_query($return_params);
}

try {
    $sql = "
		SELECT
			j.job_id,
			j.title,
			j.status AS job_status,
			j.created_at,
			a.app_id,
			a.applied_at,
			a.status AS application_status,
			a.seeker_id,
			sp.full_name,
			sp.resume_path,
			u.email
		FROM job_posting j
		LEFT JOIN applications a ON a.job_id = j.job_id
		LEFT JOIN (
			SELECT sp1.user_id, sp1.full_name, sp1.resume_path
			FROM seeker_profiles sp1
			INNER JOIN (
				SELECT user_id, MAX(profile_id) AS latest_profile_id
				FROM seeker_profiles
				GROUP BY user_id
			) latest
				ON latest.user_id = sp1.user_id
				AND latest.latest_profile_id = sp1.profile_id
		) sp ON a.seeker_id = sp.user_id
		LEFT JOIN users u ON a.seeker_id = u.user_id
		WHERE j.employer_id = :employer_id
	";

    $params = [':employer_id' => $employer_id];
    if (!empty($job_filter)) {
        $sql .= " AND j.job_id = :job_id";
        $params[':job_id'] = $job_filter;
    }

    $sql .= " ORDER BY j.created_at DESC, a.applied_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rows = [];
}

$jobs = [];
foreach ($rows as $row) {
    $job_id = (int) $row['job_id'];
    if (!isset($jobs[$job_id])) {
        $jobs[$job_id] = [
            'job_id' => $job_id,
            'title' => $row['title'],
            'job_status' => $row['job_status'],
            'created_at' => $row['created_at'],
            'applicants' => []
        ];
    }

    if (!empty($row['app_id'])) {
        $jobs[$job_id]['applicants'][] = [
            'app_id' => (int) $row['app_id'],
            'applied_at' => $row['applied_at'],
            'status' => $row['application_status'],
            'seeker_id' => (int) $row['seeker_id'],
            'full_name' => $row['full_name'],
            'email' => $row['email'],
            'resume_path' => $row['resume_path']
        ];
    }
}

include '../includes/header.php';
?>

<?php include '../includes/navbar.php'; ?>

<div class="container py-4">
    <div class="mb-4">
        <h3 class="fw-bold mb-1"><i class="bi bi-people-fill me-2"></i>Applicants</h3>
        <p class="text-muted small mb-0">Review applicants for each of your job postings.</p>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'updated'): ?>
            <div class="alert alert-success">Application status updated.</div>
        <?php elseif ($_GET['status'] === 'error'): ?>
            <div class="alert alert-danger">Unable to update the application status.</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (count($jobs) > 0): ?>
        <?php foreach ($jobs as $job): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-white">
                    <div>
                        <h5 class="mb-1 fw-semibold"><?= htmlspecialchars($job['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?></h5>
                        <div class="text-muted small">
                            <i class="bi bi-calendar3 me-1"></i>
                            Posted <?= !empty($job['created_at']) ? date('M d, Y', strtotime($job['created_at'])) : 'Recently' ?>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($job['job_status'] === 'active'): ?>
                            <span class="badge bg-primary">Active</span>
                        <?php else: ?>
                            <span class="badge bg-dark">Closed</span>
                        <?php endif; ?>
                        <span class="badge bg-secondary">
                            <?= count($job['applicants']) ?> Applicants
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (count($job['applicants']) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>APPLICANT</th>
                                        <th>EMAIL</th>
                                        <th>APPLIED ON</th>
                                        <th>STATUS</th>
                                        <th>RESUME</th>
                                        <th>ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($job['applicants'] as $app): ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($app['full_name'] ?: 'Applicant', ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?= htmlspecialchars($app['email'] ?: '—', ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="text-muted small">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?= !empty($app['applied_at']) ? date('M d, Y', strtotime($app['applied_at'])) : '—' ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status = strtolower($app['status'] ?? 'pending');
                                                if ($status === 'shortlisted') {
                                                    $badge_class = 'bg-success';
                                                    $label = 'Shortlisted';
                                                } elseif ($status === 'rejected') {
                                                    $badge_class = 'bg-danger';
                                                    $label = 'Rejected';
                                                } else {
                                                    $badge_class = 'bg-warning text-dark';
                                                    $label = 'Pending';
                                                }
                                                ?>
                                                <span class="badge <?= $badge_class ?>"><?= $label ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($app['resume_path'])): ?>
                                                    <a class="btn btn-sm btn-outline-secondary" href="../<?= htmlspecialchars($app['resume_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                                        <i class="bi bi-file-earmark-text me-1"></i>View
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <form action="applicants.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="app_id" value="<?= (int) $app['app_id'] ?>">
                                                        <input type="hidden" name="status" value="shortlisted">
                                                        <input type="hidden" name="return_to" value="<?= htmlspecialchars($return_to, ENT_QUOTES, 'UTF-8') ?>">
                                                        <button type="submit" class="btn btn-sm btn-success" <?= $status === 'shortlisted' ? 'disabled' : '' ?>>
                                                            Shortlist
                                                        </button>
                                                    </form>
                                                    <form action="applicants.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="app_id" value="<?= (int) $app['app_id'] ?>">
                                                        <input type="hidden" name="status" value="rejected">
                                                        <input type="hidden" name="return_to" value="<?= htmlspecialchars($return_to, ENT_QUOTES, 'UTF-8') ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" <?= $status === 'rejected' ? 'disabled' : '' ?>>
                                                            Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="bi bi-person text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mt-3 mb-0">No applicants yet for this job.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-briefcase text-muted" style="font-size: 2.5rem;"></i>
                <p class="text-muted mt-3 mb-0">No job postings found.</p>
                <a href="post-job.php" class="btn btn-warning fw-bold text-dark mt-3">
                    <i class="bi bi-plus me-1"></i>Post a Job
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>